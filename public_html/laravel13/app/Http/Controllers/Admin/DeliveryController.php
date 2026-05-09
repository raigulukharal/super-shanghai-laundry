<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\Payment;
use App\Models\ShopInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $query = Delivery::with(['booking.customer', 'booking.customer.codes', 'items']);
        
        if ($request->search) {
            $search = $request->search;
            $query->whereHas('booking', function($q) use ($search) {
                $q->where('invoice_no', 'LIKE', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('mobile', 'LIKE', "%{$search}%");
                  });
            })->orWhere('receiver_name', 'LIKE', "%{$search}%");
        }
        
        $deliveries = $query->latest()->paginate(20);
        
        $stats = [
            'total' => Delivery::count(),
            'today' => Delivery::whereDate('delivery_date', today())->count(),
            'pending_items' => BookingItem::where('status', 'pending')->count(),
            'partial_items' => BookingItem::where('status', 'partial')->count(),
            'delivered_items' => BookingItem::where('status', 'delivered')->count()
        ];
        
        return view('admin.deliveries.index', compact('deliveries', 'stats'));
    }

    public function search(Request $request)
    {
        $search = $request->get('term');
        if (empty($search)) return response()->json(['results' => []]);
        
        $deliveries = Delivery::with(['booking.customer', 'booking.customer.codes', 'items'])
            ->where(function($q) use ($search) {
                $q->whereHas('booking', function($bq) use ($search) {
                    $bq->where('invoice_no', 'LIKE', "%{$search}%")
                      ->orWhereHas('customer', function($cq) use ($search) {
                          $cq->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('mobile', 'LIKE', "%{$search}%")
                            ->orWhereHas('codes', function($codeq) use ($search) {
                                $codeq->where('code', 'LIKE', "%{$search}%");
                            });
                      });
                })->orWhere('receiver_name', 'LIKE', "%{$search}%");
            })
            ->limit(50)
            ->get();
        
        $results = [];
        foreach ($deliveries as $delivery) {
            $results[] = [
                'id' => $delivery->id,
                'booking_id' => $delivery->booking->id,
                'invoice_no' => $delivery->booking->invoice_no,
                'customer_name' => $delivery->booking->customer->name,
                'customer_mobile' => $delivery->booking->customer->mobile,
                'customer_code' => $delivery->booking->customer_code_used ?? 'N/A',
                'receiver_name' => $delivery->receiver_name,
                'delivery_date' => $delivery->delivery_date->format('d-m-Y'),
                'items_count' => $delivery->items->count()
            ];
        }
        
        return response()->json(['results' => $results]);
    }

    public function create($bookingId = null)
    {
        if ($bookingId) {
            $booking = Booking::with(['customer', 'customer.codes', 'items' => function($q) {
                $q->where('status', '!=', 'delivered');
            }, 'items.clothType', 'items.color'])->findOrFail($bookingId);
            return view('admin.deliveries.create', compact('booking'));
        }
        $pendingBookings = Booking::whereIn('status', ['pending', 'partial_delivered'])->with('customer')->get();
        return view('admin.deliveries.select-booking', compact('pendingBookings'));
    }

    /**
     * PARTIAL DELIVERY - Receiver name and mobile optional
     */
    public function partialDelivery(Request $request, $bookingId)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.booking_item_id' => 'required|exists:booking_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'receiver_name' => 'nullable|string|max:255',
            'receiver_mobile' => 'nullable|string|max:20',
            'notes' => 'nullable|string'
        ]);

        $booking = Booking::findOrFail($bookingId);
        DB::beginTransaction();
        try {
            $delivery = Delivery::create([
                'booking_id' => $booking->id,
                'receiver_name' => $request->receiver_name ?? ($booking->customer->name ?? null),
                'receiver_mobile' => $request->receiver_mobile ?? ($booking->customer->mobile ?? null),
                'notes' => $request->notes,
                'delivery_date' => now(),
                'created_by' => auth()->id()
            ]);
            
            $totalDeliveredAmount = 0;
            foreach ($request->items as $itemData) {
                $bookingItem = BookingItem::findOrFail($itemData['booking_item_id']);
                $deliveredQty = $itemData['quantity'];
                $total = $deliveredQty * $bookingItem->unit_price;
                $totalDeliveredAmount += $total;
                
                DeliveryItem::create([
                    'delivery_id' => $delivery->id,
                    'booking_item_id' => $bookingItem->id,
                    'quantity_delivered' => $deliveredQty,
                    'unit_price_at_delivery' => $bookingItem->unit_price,
                    'total' => $total
                ]);
                
                $bookingItem->delivered_quantity += $deliveredQty;
                $bookingItem->status = ($bookingItem->delivered_quantity >= $bookingItem->quantity) ? 'delivered' : 'partial';
                $bookingItem->save();
            }
            
            // Handle payment if collected
            if ($request->collect_payment && $request->payment_amount > 0) {
                Payment::create([
                    'booking_id' => $booking->id,
                    'amount' => $request->payment_amount,
                    'payment_date' => now(),
                    'payment_method' => $request->payment_method ?? 'cash',
                    'notes' => 'Payment collected during partial delivery',
                    'created_by' => auth()->id()
                ]);
                
                $totalPaid = $booking->payments->sum('amount');
                $booking->paid_amount = $totalPaid;
            }
            
            // Update booking status
            $booking->updateDeliveryStatus();
            $booking->save();
            
            // Update ShopInvoice status
            $shopInvoice = ShopInvoice::where('invoice_no', $booking->invoice_no)->first();
            if ($shopInvoice) {
                if ($booking->status === 'delivered') {
                    $shopInvoice->status = 'delivered';
                } elseif ($booking->status === 'partial_delivered') {
                    $shopInvoice->status = 'partial_delivered';
                } else {
                    $shopInvoice->status = 'pending';
                }
                $shopInvoice->save();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Partial delivery processed successfully',
                'redirect' => route('admin.deliveries.show', $delivery->id)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * FULL DELIVERY - Receiver name and mobile optional
     */
    public function fullDelivery(Request $request, $bookingId)
    {
        $booking = Booking::with(['items' => function($q) {
            $q->where('status', '!=', 'delivered');
        }, 'payments'])->findOrFail($bookingId);
        
        DB::beginTransaction();
        try {
            // Create delivery record (receiver name and mobile optional)
            $delivery = Delivery::create([
                'booking_id' => $booking->id,
                'receiver_name' => $request->receiver_name ?? ($booking->customer->name ?? null),
                'receiver_mobile' => $request->receiver_mobile ?? ($booking->customer->mobile ?? null),
                'notes' => $request->notes ?? 'Full delivery',
                'delivery_date' => now(),
                'created_by' => auth()->id()
            ]);
            
            // Deliver all remaining items
            foreach ($booking->items as $bookingItem) {
                $remainingQty = $bookingItem->quantity - ($bookingItem->delivered_quantity ?? 0);
                if ($remainingQty > 0) {
                    $total = $remainingQty * $bookingItem->unit_price;
                    
                    DeliveryItem::create([
                        'delivery_id' => $delivery->id,
                        'booking_item_id' => $bookingItem->id,
                        'quantity_delivered' => $remainingQty,
                        'unit_price_at_delivery' => $bookingItem->unit_price,
                        'total' => $total
                    ]);
                    
                    $bookingItem->delivered_quantity = $bookingItem->quantity;
                    $bookingItem->status = 'delivered';
                    $bookingItem->save();
                }
            }
            
            // Calculate and create payment if needed
            $totalPaidFromPayments = $booking->payments->sum('amount');
            $remainingDue = $booking->grand_total - $totalPaidFromPayments;
            
            if ($remainingDue > 0) {
                Payment::create([
                    'booking_id' => $booking->id,
                    'amount' => $remainingDue,
                    'payment_date' => now(),
                    'payment_method' => 'cash',
                    'notes' => 'Full payment on delivery',
                    'created_by' => auth()->id()
                ]);
                
                $booking->paid_amount = $totalPaidFromPayments + $remainingDue;
            } else {
                $booking->paid_amount = $totalPaidFromPayments;
            }
            
            $booking->status = 'delivered';
            $booking->save();
            
            // Update ShopInvoice status
            $shopInvoice = ShopInvoice::where('invoice_no', $booking->invoice_no)->first();
            if ($shopInvoice) {
                $shopInvoice->status = 'delivered';
                $shopInvoice->save();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Full delivery completed successfully!',
                'delivery_id' => $delivery->id
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $delivery = Delivery::with(['booking.customer', 'items.bookingItem.clothType', 'items.bookingItem.color', 'creator'])
            ->findOrFail($id);
        
        return view('admin.deliveries.show', compact('delivery'));
    }

    public function getNonDeliveredItems($bookingId)
    {
        $booking = Booking::with(['items' => function($q) {
            $q->where('status', '!=', 'delivered');
        }, 'items.clothType', 'items.color'])->findOrFail($bookingId);
        
        $items = [];
        foreach ($booking->items as $item) {
            $remainingQty = $item->quantity - ($item->delivered_quantity ?? 0);
            if ($remainingQty > 0) {
                $items[] = [
                    'id' => $item->id,
                    'cloth_type' => $item->clothType->name,
                    'color' => $item->color->name,
                    'remaining_quantity' => $remainingQty,
                    'total_quantity' => $item->quantity,
                    'unit_price' => $item->unit_price
                ];
            }
        }
        
        return response()->json([
            'booking_id' => $booking->id,
            'invoice_no' => $booking->invoice_no,
            'customer_name' => $booking->customer->name,
            'pending_amount' => $booking->grand_total - $booking->paid_amount,
            'items' => $items
        ]);
    }

    public function nonDeliveredItems()
    {
        $pendingItems = BookingItem::with(['booking.customer', 'clothType', 'color'])
            ->where('status', '!=', 'delivered')
            ->whereRaw('delivered_quantity < quantity')
            ->get();
        
        $report = [];
        foreach ($pendingItems as $item) {
            $remaining = $item->quantity - ($item->delivered_quantity ?? 0);
            $report[] = [
                'booking_id' => $item->booking_id,
                'invoice_no' => $item->booking->invoice_no,
                'customer_name' => $item->booking->customer->name,
                'cloth_type' => $item->clothType->name,
                'color' => $item->color->name,
                'total_quantity' => $item->quantity,
                'delivered' => $item->delivered_quantity ?? 0,
                'remaining' => $remaining,
                'expected_date' => $item->expected_delivery_date?->format('d-m-Y')
            ];
        }
        
        return view('admin.deliveries.non-delivered', compact('report'));
    }
}