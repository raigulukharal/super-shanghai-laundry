<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Customer;
use App\Models\CustomerCode;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\Payment;
use App\Models\ClothType;
use App\Models\Color;
use App\Models\Category;
use App\Models\InvoiceSequence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings.
     */
    public function index(Request $request)
    {
        $query = Booking::with(['customer', 'items']);
        
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_no', 'LIKE', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('mobile', 'LIKE', "%{$search}%");
                  });
            });
        }
        
        if ($request->status && $request->status != 'all') {
            $query->where('status', $request->status);
        }
        
        $bookings = $query->orderBy('id', 'desc')->paginate(20);
        
        return view('admin.bookings.index', compact('bookings'));
    }

    /**
     * Show the form for creating a new booking.
     */
    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $clothTypes = ClothType::with('category')->orderBy('name')->get();
        $colors = Color::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        
        // Sirf display ke liye - INCREMENT NAHI KARNA
        $sequence = InvoiceSequence::firstOrCreate(
            ['prefix' => '1'],
            ['last_number' => 50400]
        );
        
        // Next number dikhana hai (display only)
        $nextNumber = $sequence->last_number + 1;
        $nextInvoiceNo = '1-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        
        return view('admin.bookings.create', compact('customers', 'clothTypes', 'colors', 'categories', 'nextInvoiceNo'));
    }

    /**
     * Store a newly created booking in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string',
            'customer_mobile' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.cloth_type_id' => 'required|exists:cloth_types,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.delivery_type' => 'required|in:normal,urgent',
        ]);
        
        DB::beginTransaction();
        try {
            // Create or get customer
            $customer = Customer::firstOrCreate(
                ['mobile' => $request->customer_mobile],
                [
                    'name' => $request->customer_name,
                    'area' => $request->customer_area ?? null
                ]
            );
            
            // If customer already exists, update details
            if ($customer->wasRecentlyCreated == false) {
                if ($customer->name != $request->customer_name) {
                    $customer->name = $request->customer_name;
                }
                if ($request->customer_area && $customer->area != $request->customer_area) {
                    $customer->area = $request->customer_area;
                }
                $customer->save();
            }
            
            // Add customer code if provided
            if ($request->customer_code && $request->customer_code != '') {
                $existingCode = CustomerCode::where('customer_id', $customer->id)
                    ->where('code', $request->customer_code)
                    ->first();
                
                if (!$existingCode) {
                    CustomerCode::create([
                        'customer_id' => $customer->id,
                        'code' => $request->customer_code
                    ]);
                }
            }
            
            // Calculate totals
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['quantity'] * $item['unit_price'];
            }
            
            $grandTotal = $totalAmount + ($request->other_charges ?? 0) - ($request->discount ?? 0);
            
            // ✅ GENERATE INVOICE NUMBER - YAHAN INCREMENT KARO (Sirf successful booking par)
            $sequence = InvoiceSequence::firstOrCreate(
                ['prefix' => '1'],
                ['last_number' => 50400]
            );
            $sequence->increment('last_number');
            $invoiceNo = '1-' . str_pad($sequence->last_number, 5, '0', STR_PAD_LEFT);
            
            // Create booking
            $booking = Booking::create([
                'invoice_no' => $invoiceNo,
                'customer_id' => $customer->id,
                'customer_code_used' => $request->customer_code ?? null,
                'booking_date' => $request->booking_date ?? now(),
                'expected_delivery_date' => $request->expected_delivery_date,
                'total_amount' => $totalAmount,
                'discount' => $request->discount ?? 0,
                'other_charges' => $request->other_charges ?? 0,
                'grand_total' => $grandTotal,
                'paid_amount' => $request->paid_amount ?? 0,
                'customer_notes' => $request->customer_notes,
                'status' => 'pending',
                'created_by' => auth()->id()
            ]);
            
            // Add items
            foreach ($request->items as $itemData) {
                $quantity = $itemData['quantity'];
                $unitPrice = $itemData['unit_price'];
                $totalPrice = $quantity * $unitPrice;
                
                // Handle color data
                $colorId = null;
                $colorDataJson = null;
                
                if (isset($itemData['color_data']) && !empty($itemData['color_data'])) {
                    $colorDataJson = $itemData['color_data'];
                    $colorArray = json_decode($itemData['color_data'], true);
                    if ($colorArray && is_array($colorArray) && count($colorArray) > 0) {
                        $firstColor = reset($colorArray);
                        $colorId = $firstColor['id'] ?? null;
                    }
                }
                
                BookingItem::create([
                    'booking_id' => $booking->id,
                    'cloth_type_id' => $itemData['cloth_type_id'],
                    'color_id' => $colorId,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => $totalPrice,
                    'delivery_type' => $itemData['delivery_type'],
                    'status' => 'pending',
                    'delivered_quantity' => 0,
                    'expected_delivery_date' => $request->expected_delivery_date,
                    'color_data' => $colorDataJson
                ]);
            }
            
            // Add payment if paid
            if ($request->paid_amount > 0) {
                Payment::create([
                    'booking_id' => $booking->id,
                    'amount' => $request->paid_amount,
                    'payment_method' => $request->payment_method ?? 'cash',
                    'payment_date' => now(),
                    'notes' => 'Initial payment',
                    'created_by' => auth()->id()
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully!',
                'invoice_no' => $invoiceNo,
                'booking_id' => $booking->id
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
     * Display the specified booking.
     */
    public function show($id)
    {
        $booking = Booking::with(['customer', 'items.clothType', 'items.color', 'payments', 'deliveries.items'])
            ->findOrFail($id);
        
        return view('admin.bookings.show', compact('booking'));
    }

    /**
     * Show the form for editing the specified booking.
     */
    public function edit($id)
    {
        $booking = Booking::with(['customer', 'items.clothType', 'items.color', 'payments'])
            ->findOrFail($id);
        
        $customers = Customer::orderBy('name')->get();
        $clothTypes = ClothType::with('category')->orderBy('name')->get();
        $colors = Color::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        
        return view('admin.bookings.edit', compact('booking', 'customers', 'clothTypes', 'colors', 'categories'));
    }

    /**
     * Update the specified booking in storage.
     */
    public function update(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        
        $request->validate([
            'customer_name' => 'required|string',
            'customer_mobile' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.cloth_type_id' => 'required|exists:cloth_types,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.delivery_type' => 'required|in:normal,urgent',
        ]);
        
        DB::beginTransaction();
        try {
            // Update or create customer
            $customer = Customer::firstOrCreate(
                ['mobile' => $request->customer_mobile],
                [
                    'name' => $request->customer_name,
                    'area' => $request->customer_area ?? null
                ]
            );
            
            // If customer already exists, update details
            if ($customer->wasRecentlyCreated == false) {
                if ($customer->name != $request->customer_name) {
                    $customer->name = $request->customer_name;
                }
                if ($request->customer_area && $customer->area != $request->customer_area) {
                    $customer->area = $request->customer_area;
                }
                $customer->save();
            }
            
            // Calculate totals
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['quantity'] * $item['unit_price'];
            }
            
            $grandTotal = $totalAmount + ($request->other_charges ?? 0) - ($request->discount ?? 0);
            
            // Update booking
            $booking->customer_id = $customer->id;
            $booking->customer_code_used = $request->customer_code_used ?? $request->customer_code ?? null;
            $booking->booking_date = $request->booking_date;
            $booking->expected_delivery_date = $request->expected_delivery_date;
            $booking->total_amount = $totalAmount;
            $booking->discount = $request->discount ?? 0;
            $booking->other_charges = $request->other_charges ?? 0;
            $booking->grand_total = $grandTotal;
            $booking->customer_notes = $request->customer_notes;
            $booking->save();
            
            // Get all booking_item ids
            $bookingItemIds = BookingItem::where('booking_id', $booking->id)->pluck('id')->toArray();
            
            // Delete delivery_items first (foreign key constraint)
            if (!empty($bookingItemIds)) {
                DeliveryItem::whereIn('booking_item_id', $bookingItemIds)->delete();
            }
            
            // Delete old booking_items
            BookingItem::where('booking_id', $booking->id)->delete();
            
            // Add new items
            foreach ($request->items as $itemData) {
                $quantity = $itemData['quantity'];
                $unitPrice = $itemData['unit_price'];
                $totalPrice = $quantity * $unitPrice;
                
                // Handle color data
                $colorId = null;
                $colorDataJson = null;
                
                if (isset($itemData['color_data']) && !empty($itemData['color_data'])) {
                    $colorDataJson = $itemData['color_data'];
                    $colorArray = json_decode($itemData['color_data'], true);
                    if ($colorArray && is_array($colorArray) && count($colorArray) > 0) {
                        $firstColor = reset($colorArray);
                        $colorId = $firstColor['id'] ?? null;
                    }
                }
                
                BookingItem::create([
                    'booking_id' => $booking->id,
                    'cloth_type_id' => $itemData['cloth_type_id'],
                    'color_id' => $colorId,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => $totalPrice,
                    'delivery_type' => $itemData['delivery_type'],
                    'status' => 'pending',
                    'delivered_quantity' => 0,
                    'expected_delivery_date' => $request->expected_delivery_date,
                    'color_data' => $colorDataJson
                ]);
            }
            
            // Add payment if new payment added
            if ($request->paid_amount > $booking->paid_amount) {
                $newPayment = $request->paid_amount - $booking->paid_amount;
                Payment::create([
                    'booking_id' => $booking->id,
                    'amount' => $newPayment,
                    'payment_method' => $request->payment_method ?? 'cash',
                    'payment_date' => now(),
                    'notes' => 'Additional payment on edit',
                    'created_by' => auth()->id()
                ]);
                
                $booking->paid_amount = $request->paid_amount;
                $booking->save();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Booking updated successfully!'
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
     * Remove the specified booking from storage.
     */
    public function destroy($id)
    {
        $booking = Booking::findOrFail($id);
        
        DB::beginTransaction();
        try {
            // Get all booking_item ids
            $bookingItemIds = BookingItem::where('booking_id', $booking->id)->pluck('id')->toArray();
            
            // Delete delivery_items first (foreign key constraint)
            if (!empty($bookingItemIds)) {
                DeliveryItem::whereIn('booking_item_id', $bookingItemIds)->delete();
            }
            
            // Delete deliveries
            Delivery::where('booking_id', $booking->id)->delete();
            
            // Delete payments
            Payment::where('booking_id', $booking->id)->delete();
            
            // Delete booking items
            BookingItem::where('booking_id', $booking->id)->delete();
            
            // Delete the booking
            $booking->delete();
            
            DB::commit();
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Booking deleted successfully!'
                ]);
            }
            
            return redirect()->route('admin.bookings.index')
                ->with('success', 'Booking deleted successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting booking: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Error deleting booking: ' . $e->getMessage());
        }
    }

    /**
     * Get customer codes for API
     */
    public function getCustomerCodes($id)
    {
        $customer = Customer::with('codes')->findOrFail($id);
        return response()->json([
            'codes' => $customer->codes
        ]);
    }

    /**
     * Cancel booking
     */
    public function cancel($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->status = 'cancelled';
        $booking->cancelled_at = now();
        $booking->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully'
        ]);
    }

    /**
     * Add payment to booking
     */
    public function addPayment(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string'
        ]);
        
        $booking = Booking::findOrFail($id);
        
        DB::beginTransaction();
        try {
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_date' => now(),
                'notes' => $request->notes,
                'created_by' => auth()->id()
            ]);
            
            $totalPaid = $booking->payments()->sum('amount');
            $booking->paid_amount = $totalPaid;
            
            if ($totalPaid >= $booking->grand_total) {
                $booking->payment_status = 'full_pay';
            } else if ($totalPaid > 0) {
                $booking->payment_status = 'partial_pay';
            } else {
                $booking->payment_status = 'full_due';
            }
            
            $booking->save();
            
            DB::commit();
            
            return redirect()->back()->with('success', 'Payment added successfully!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error adding payment: ' . $e->getMessage());
        }
    }

    /**
     * Update booking status
     */
    public function updateStatus(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $booking->status = $request->status;
        $booking->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully'
        ]);
    }

    /**
     * Generate next invoice number
     */
    public function generateInvoiceNo()
    {
        $sequence = InvoiceSequence::firstOrCreate(
            ['prefix' => '1'],
            ['last_number' => 50400]
        );
        $sequence->increment('last_number');
        return '1-' . str_pad($sequence->last_number, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Download invoice PDF
     */
    public function downloadInvoice($id)
    {
        $booking = Booking::with(['customer', 'items.clothType', 'items.color', 'payments'])
            ->findOrFail($id);
        
        $pdf = PDF::loadView('admin.bookings.invoice-pdf', compact('booking'));
        return $pdf->download('invoice_' . $booking->invoice_no . '.pdf');
    }

    /**
     * Get API data for booking
     */
    public function getApiData($id)
    {
        $booking = Booking::with(['customer', 'items.clothType', 'items.color'])
            ->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'booking' => $booking
        ]);
    }

    /**
     * Get cloth types by category
     */
    public function getClothTypesByCategory(Request $request)
    {
        $clothTypes = ClothType::where('category_id', $request->category_id)
            ->orderBy('name')
            ->get();
        
        return response()->json($clothTypes);
    }

    /**
     * Search bookings
     */
    public function search(Request $request)
    {
        $search = $request->get('term');
        $bookings = Booking::with('customer')
            ->where('invoice_no', 'LIKE', "%{$search}%")
            ->orWhereHas('customer', function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('mobile', 'LIKE', "%{$search}%");
            })
            ->limit(10)
            ->get();
        
        return response()->json([
            'results' => $bookings->map(function($booking) {
                return [
                    'id' => $booking->id,
                    'text' => $booking->invoice_no . ' - ' . ($booking->customer->name ?? 'N/A'),
                    'invoice_no' => $booking->invoice_no,
                    'customer_name' => $booking->customer->name ?? 'N/A'
                ];
            })
        ]);
    }

    /**
     * Process partial delivery
     */
    public function processPartialDelivery(Request $request, $id)
    {
        return redirect()->route('admin.deliveries.partial', $id);
    }

    /**
     * Test partial delivery
     */
    public function testPartialDelivery($id)
    {
        return response()->json([
            'message' => 'Test route working',
            'booking_id' => $id
        ]);
    }

    /**
     * Saved invoices list
     */
    public function savedInvoices()
    {
        return view('admin.bookings.saved-invoices');
    }

    /**
     * Delete invoice file
     */
    public function deleteInvoiceFile($fileName)
    {
        return response()->json(['success' => true]);
    }
}