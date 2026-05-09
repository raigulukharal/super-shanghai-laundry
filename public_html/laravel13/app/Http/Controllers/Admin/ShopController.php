<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvoiceRange;
use App\Models\ShopInvoice;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    /**
     * Display shop management page
     */
    public function index()
    {
        $ranges = InvoiceRange::with('shopInvoices')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $bookings = Booking::orderBy('invoice_no')->get(['id', 'invoice_no']);
        
        $stats = [
            'total_ranges' => InvoiceRange::count(),
            'total_invoices' => ShopInvoice::count(),
            'in_shop' => ShopInvoice::where('status', 'in_shop')->count(),
            'delivered' => ShopInvoice::where('status', 'delivered')->count(),
            'pending' => ShopInvoice::where('status', 'pending')->count(),
            'missing' => ShopInvoice::where('is_missing', true)->count(),
            'extra' => ShopInvoice::where('type', 'extra')->count()
        ];
        
        return view('admin.shop.index', compact('ranges', 'stats', 'bookings'));
    }

    /**
     * Generate invoice range
     */
    public function generateRange(Request $request)
    {
        $request->validate([
            'start_invoice' => 'required|string',
            'end_invoice' => 'required|string',
            'range_date' => 'required|date'
        ]);

        DB::beginTransaction();
        
        try {
            $start = $request->start_invoice;
            $end = $request->end_invoice;
            
            preg_match('/(\d+)-(\d+)/', $start, $startMatch);
            preg_match('/(\d+)-(\d+)/', $end, $endMatch);
            
            if (!$startMatch || !$endMatch) {
                throw new \Exception('Invalid invoice number format. Use format: 1-00001');
            }
            
            $prefix = $startMatch[1];
            $startNum = (int)$startMatch[2];
            $endNum = (int)$endMatch[2];
            
            if ($startNum > $endNum) {
                throw new \Exception('Start invoice number must be less than end invoice number');
            }
            
            $rangeName = "{$prefix}-{$startMatch[2]} to {$prefix}-{$endMatch[2]}";
            
            $range = InvoiceRange::create([
                'range_name' => $rangeName,
                'start_invoice' => $start,
                'end_invoice' => $end,
                'range_date' => $request->range_date,
                'description' => $request->description
            ]);
            
            for ($i = $startNum; $i <= $endNum; $i++) {
                $invoiceNo = $prefix . '-' . str_pad($i, 5, '0', STR_PAD_LEFT);
                
                $bookingExists = Booking::where('invoice_no', $invoiceNo)->exists();
                
                ShopInvoice::create([
                    'invoice_range_id' => $range->id,
                    'invoice_no' => $invoiceNo,
                    'status' => $bookingExists ? 'delivered' : 'in_shop',
                    'type' => 'regular',
                    'is_missing' => false
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Invoice range generated successfully',
                'range' => $range->load('shopInvoices')
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add extra invoice
     */
    public function addExtraInvoice(Request $request)
    {
        $request->validate([
            'range_id' => 'required|exists:invoice_ranges,id',
            'invoice_no' => 'required|string|unique:shop_invoices,invoice_no'
        ]);
        
        $bookingExists = Booking::where('invoice_no', $request->invoice_no)->exists();
        
        $shopInvoice = ShopInvoice::create([
            'invoice_range_id' => $request->range_id,
            'invoice_no' => $request->invoice_no,
            'status' => $bookingExists ? 'delivered' : 'in_shop',
            'type' => 'extra',
            'is_missing' => false,
            'notes' => $request->notes
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Extra invoice added successfully',
            'invoice' => $shopInvoice
        ]);
    }

    /**
     * Remove invoice
     */
    public function removeInvoice($id)
    {
        $shopInvoice = ShopInvoice::findOrFail($id);
        $shopInvoice->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Invoice removed successfully'
        ]);
    }

    /**
     * Mark invoice as missing
     */
    public function markMissing($id)
    {
        $shopInvoice = ShopInvoice::findOrFail($id);
        $shopInvoice->is_missing = true;
        $shopInvoice->status = 'pending';
        $shopInvoice->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Invoice marked as missing'
        ]);
    }

    /**
     * Delete entire invoice range
     */
    public function deleteRange($id)
    {
        $range = InvoiceRange::findOrFail($id);
        
        DB::beginTransaction();
        try {
            ShopInvoice::where('invoice_range_id', $id)->delete();
            $range->delete();
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Invoice range deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get range details
     */
    public function getRangeDetails($id)
    {
        $range = InvoiceRange::with('shopInvoices')->findOrFail($id);
        
        $stats = [
            'total' => $range->shopInvoices->count(),
            'regular' => $range->shopInvoices->where('type', 'regular')->count(),
            'extra' => $range->shopInvoices->where('type', 'extra')->count(),
            'missing' => $range->shopInvoices->where('is_missing', true)->count(),
            'in_shop' => $range->shopInvoices->where('status', 'in_shop')->count(),
            'delivered' => $range->shopInvoices->where('status', 'delivered')->count(),
            'pending' => $range->shopInvoices->where('status', 'pending')->count()
        ];
        
        return response()->json([
            'success' => true,
            'range' => $range,
            'stats' => $stats
        ]);
    }

    /**
     * Update invoice status
     */
    public function updateInvoiceStatus(Request $request, $id)
    {
        $shopInvoice = ShopInvoice::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:in_shop,delivered,pending'
        ]);
        
        $shopInvoice->status = $request->status;
        $shopInvoice->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully'
        ]);
    }

    /**
     * Get booking details for modal
     */
    public function getBookingDetails($invoiceNo)
    {
        $booking = Booking::with(['customer', 'items.clothType', 'items.color'])
            ->where('invoice_no', $invoiceNo)
            ->first();
        
        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ]);
        }
        
        $totalItems = $booking->items->sum('quantity');
        $deliveredItems = $booking->items->sum('delivered_quantity');
        $due = $booking->grand_total - $booking->paid_amount;
        
        $nonDeliveredItems = [];
        foreach ($booking->items as $item) {
            $remaining = $item->quantity - ($item->delivered_quantity ?? 0);
            if ($remaining > 0) {
                $nonDeliveredItems[] = [
                    'id' => $item->id,
                    'cloth_type' => $item->clothType->name ?? 'N/A',
                    'color' => $item->color->name ?? 'Not Selected',
                    'remaining_quantity' => $remaining,
                    'total_quantity' => $item->quantity,
                    'unit_price' => $item->unit_price
                ];
            }
        }
        
        return response()->json([
            'success' => true,
            'booking' => [
                'id' => $booking->id,
                'invoice_no' => $booking->invoice_no,
                'customer_name' => $booking->customer->name ?? 'N/A',
                'customer_mobile' => $booking->customer->mobile ?? 'N/A',
                'customer_code' => $booking->customer_code_used ?? 'N/A',
                'booking_date' => $booking->booking_date->format('d-m-Y'),
                'grand_total' => $booking->grand_total,
                'paid_amount' => $booking->paid_amount,
                'due' => $due,
                'status' => $booking->status,
                'total_items' => $totalItems,
                'delivered_items' => $deliveredItems,
                'remaining_items' => $totalItems - $deliveredItems,
                'non_delivered_items' => $nonDeliveredItems
            ]
        ]);
    }

/**
 * UNDELIVER - Reverse delivery with payment reversal
 */
public function undeliverInvoice($bookingId)
{
    $booking = Booking::findOrFail($bookingId);
    
    DB::beginTransaction();
    try {
        // 1. Find and delete the latest delivery record
        $delivery = Delivery::where('booking_id', $bookingId)->latest()->first();
        
        if ($delivery) {
            // Delete delivery items
            DeliveryItem::where('delivery_id', $delivery->id)->delete();
            $delivery->delete();
        }
        
        // 2. Reset all items delivered_quantity to 0
        foreach ($booking->items as $item) {
            $item->delivered_quantity = 0;
            $item->status = 'pending';
            $item->save();
        }
        
        // 3. ✅ CRITICAL: Delete the payment records related to this delivery
        // Find payments created after the last delivery (or all payments if needed)
        $paymentsToDelete = Payment::where('booking_id', $bookingId)
            ->where('notes', 'LIKE', '%delivery%')
            ->orWhere('notes', 'LIKE', '%Full payment on delivery%')
            ->orWhere('notes', 'LIKE', '%Auto payment from full delivery%')
            ->get();
        
        foreach ($paymentsToDelete as $payment) {
            $payment->delete();
        }
        
        // 4. ✅ Also delete any payment that was created after the delivery date
        if ($delivery) {
            $paymentsAfterDelivery = Payment::where('booking_id', $bookingId)
                ->where('created_at', '>=', $delivery->created_at)
                ->get();
            
            foreach ($paymentsAfterDelivery as $payment) {
                $payment->delete();
            }
        }
        
        // 5. Reset booking paid_amount from remaining payments
        $totalPaid = $booking->payments()->sum('amount');
        $booking->paid_amount = $totalPaid;
        
        // 6. Reset booking status
        $booking->status = 'pending';
        $booking->save();
        
        // 7. Update ShopInvoice status
        $shopInvoice = ShopInvoice::where('invoice_no', $booking->invoice_no)->first();
        if ($shopInvoice) {
            // Check if booking exists in system
            $bookingExists = Booking::where('invoice_no', $shopInvoice->invoice_no)->exists();
            $shopInvoice->status = $bookingExists ? 'pending' : 'in_shop';
            $shopInvoice->save();
        }
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'Delivery undone successfully! Payments reversed. Booking is now pending.'
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
    /**
 * SEARCH INVOICES - FIXED - Proper due calculation based on payments
 */
public function searchInvoices(Request $request)
{
    $query = ShopInvoice::with('invoiceRange');
    
    if ($request->invoice_no) {
        $query->where('invoice_no', 'LIKE', "%{$request->invoice_no}%");
    }
    
    if ($request->range_id && $request->range_id != '') {
        $query->where('invoice_range_id', $request->range_id);
    }
    
    if ($request->date) {
        $query->whereHas('invoiceRange', function($q) use ($request) {
            $q->whereDate('range_date', $request->date);
        });
    }
    
    if ($request->status && $request->status != 'all') {
        $query->where('status', $request->status);
    }
    
    $shopInvoices = $query->orderBy('invoice_no')->get();
    
    $invoices = [];
    foreach ($shopInvoices as $shopInvoice) {
        $booking = Booking::with(['customer', 'items', 'payments'])
            ->where('invoice_no', $shopInvoice->invoice_no)
            ->first();
        
        if ($booking) {
            $totalItems = $booking->items->sum('quantity');
            $deliveredItems = $booking->items->sum('delivered_quantity');
            $remainingItems = $totalItems - $deliveredItems;
            
            // Calculate total paid from payments table
            $totalPaid = $booking->payments->sum('amount');
            
            // Calculate due properly
            $due = max(0, $booking->grand_total - $totalPaid);
            
            $invoices[] = [
                'id' => $shopInvoice->id,
                'booking_id' => $booking->id,
                'invoice_no' => $shopInvoice->invoice_no,
                'customer_name' => $booking->customer->name ?? 'N/A',
                'customer_mobile' => $booking->customer->mobile ?? 'N/A',
                'customer_code' => $booking->customer_code_used ?? $booking->customer->codes->first()->code ?? 'N/A',
                'booking_date' => $booking->booking_date->format('d-m-Y'),
                'grand_total' => (float)$booking->grand_total,
                'paid_amount' => (float)$totalPaid,
                'due_amount' => (float)$due,
                'status' => $booking->status,
                'total_items' => $totalItems,
                'delivered_items' => $deliveredItems,
                'remaining_items' => $remainingItems,
                'shop_status' => $shopInvoice->status,
                'type' => $shopInvoice->type,
                'is_missing' => $shopInvoice->is_missing
            ];
        } else {
            $invoices[] = [
                'id' => $shopInvoice->id,
                'booking_id' => null,
                'invoice_no' => $shopInvoice->invoice_no,
                'customer_name' => '⚠️ No Booking Found',
                'customer_mobile' => '-',
                'customer_code' => '-',
                'booking_date' => '-',
                'grand_total' => 0,
                'paid_amount' => 0,
                'due_amount' => 0,
                'status' => 'no_booking',
                'total_items' => 0,
                'delivered_items' => 0,
                'remaining_items' => 0,
                'shop_status' => $shopInvoice->status,
                'type' => $shopInvoice->type,
                'is_missing' => $shopInvoice->is_missing
            ];
        }
    }
    
    if ($request->ajax()) {
        return response()->json([
            'success' => true,
            'invoices' => $invoices,
            'total' => count($invoices)
        ]);
    }
    
    return view('admin.shop.index', compact('invoices'));
}
}