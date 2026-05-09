<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerCode;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index(Request $request)
    {
        $query = Customer::query();
        
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('mobile', 'LIKE', "%{$search}%")
                  ->orWhere('area', 'LIKE', "%{$search}%");
            });
        }
        
        $customers = $query->latest()->paginate(20);
        
        return view('admin.customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        return view('admin.customers.create');
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20|unique:customers,mobile',
            'area' => 'nullable|string',
            'codes' => 'nullable|array',
            'codes.*' => 'string|max:50'
        ]);
        
        DB::beginTransaction();
        try {
            $customer = Customer::create([
                'name' => $request->name,
                'mobile' => $request->mobile,
                'area' => $request->area,
                'created_by' => auth()->id()
            ]);
            
            // Add customer codes
            if ($request->codes && count($request->codes) > 0) {
                foreach ($request->codes as $code) {
                    if (!empty($code)) {
                        CustomerCode::create([
                            'customer_id' => $customer->id,
                            'code' => $code,
                            'created_by' => auth()->id()
                        ]);
                    }
                }
            }
            
            DB::commit();
            
            return redirect()->route('admin.customers.index')
                ->with('success', 'Customer created successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error creating customer: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified customer for API/Modal
     */
    public function show($id)
    {
        try {
            $customer = Customer::with(['codes', 'bookings.items'])->find($id);
            
            if (!$customer) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Customer not found'
                    ], 404);
                }
                abort(404);
            }
            
            // Calculate statistics
            $stats = [
                'total_bookings' => $customer->bookings->count(),
                'total_amount' => $customer->bookings->sum('grand_total'),
                'total_paid' => $customer->bookings->sum('paid_amount'),
                'outstanding' => $customer->bookings->sum('grand_total') - $customer->bookings->sum('paid_amount')
            ];
            
            // Format bookings for JSON response
            $bookings = [];
            foreach ($customer->bookings as $booking) {
                $totalItems = $booking->items->sum('quantity');
                $deliveredItems = $booking->items->sum('delivered_quantity');
                
                // Calculate due amount
                $dueAmount = $booking->grand_total - $booking->paid_amount;
                
                $bookings[] = [
                    'id' => $booking->id,
                    'invoice_no' => $booking->invoice_no,
                    'booking_date' => $booking->booking_date->format('Y-m-d'),
                    'grand_total' => (float)$booking->grand_total,
                    'paid_amount' => (float)$booking->paid_amount,
                    'due_amount' => (float)$dueAmount,
                    'status' => $booking->status,
                    'status_display' => ucfirst(str_replace('_', ' ', $booking->status)),
                    'payment_status' => $booking->payment_status,
                    'payment_status_display' => ucfirst(str_replace('_', ' ', $booking->payment_status)),
                    'total_items' => $totalItems,
                    'delivered_items' => $deliveredItems,
                    'remaining_items' => $totalItems - $deliveredItems
                ];
            }
            
            // Format codes for JSON response
            $codes = [];
            foreach ($customer->codes as $code) {
                $codes[] = [
                    'id' => $code->id,
                    'code' => $code->code
                ];
            }
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'customer' => [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'mobile' => $customer->mobile,
                        'area' => $customer->area,
                        'notes' => $customer->notes,
                        'codes' => $codes
                    ],
                    'stats' => $stats,
                    'bookings' => $bookings
                ]);
            }
            
            // For non-AJAX requests (full page view)
            return view('admin.customers.show', compact('customer', 'stats'));
            
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            throw $e;
        }
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit($id)
    {
        $customer = Customer::with('codes')->findOrFail($id);
        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20|unique:customers,mobile,' . $id,
            'area' => 'nullable|string'
        ]);
        
        try {
            $customer->update([
                'name' => $request->name,
                'mobile' => $request->mobile,
                'area' => $request->area ?? null
            ]);
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Customer updated successfully!'
                ]);
            }
            
            return redirect()->route('admin.customers.show', $customer->id)
                ->with('success', 'Customer updated successfully!');
            
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating customer: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Error updating customer: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        
        try {
            // Check if customer has bookings
            if ($customer->bookings()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete customer with existing bookings'
                ], 400);
            }
            
            // Delete customer codes first
            $customer->codes()->delete();
            
            // Delete customer
            $customer->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Customer deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting customer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search customers for AJAX requests.
     */
    public function search(Request $request)
    {
        $search = $request->get('term');
        
        $customers = Customer::where('name', 'LIKE', "%{$search}%")
            ->orWhere('mobile', 'LIKE', "%{$search}%")
            ->orWhere('area', 'LIKE', "%{$search}%")
            ->orWhereHas('codes', function($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%");
            })
            ->limit(20)
            ->get();
        
        $results = [];
        foreach ($customers as $customer) {
            // Calculate totals from bookings
            $totalAmount = $customer->bookings->sum('grand_total');
            $totalPaid = $customer->bookings->sum('paid_amount');
            
            $results[] = [
                'id' => $customer->id,
                'text' => $customer->name . ' (' . $customer->mobile . ')',
                'name' => $customer->name,
                'mobile' => $customer->mobile,
                'area' => $customer->area,
                'code_array' => $customer->codes->pluck('code')->toArray(),
                'bookings_count' => $customer->bookings->count(),
                'total_amount' => $totalAmount,
                'total_paid' => $totalPaid
            ];
        }
        
        return response()->json([
            'results' => $results
        ]);
    }

    /**
     * Add customer code
     */
    public function addCode(Request $request, $id)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:customer_codes,code'
        ]);
        
        $customer = Customer::findOrFail($id);
        
        $code = CustomerCode::create([
            'customer_id' => $customer->id,
            'code' => $request->code,
            'created_by' => auth()->id()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Code added successfully',
            'code' => $code
        ]);
    }

    /**
     * Remove customer code
     */
    public function removeCode($id, $codeId)
    {
        $customer = Customer::findOrFail($id);
        $code = $customer->codes()->findOrFail($codeId);
        
        try {
            $code->delete();
            return response()->json([
                'success' => true,
                'message' => 'Code removed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customer codes for API
     */
    public function getCustomerCodes($id)
    {
        try {
            $customer = Customer::with('codes')->find($id);
            
            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found',
                    'codes' => []
                ]);
            }
            
            return response()->json([
                'success' => true,
                'codes' => $customer->codes
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'codes' => []
            ]);
        }
    }

    /**
     * Get customer details for API
     */
    public function getCustomerDetails($id)
    {
        try {
            $customer = Customer::with('codes')->find($id);
            
            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found'
                ], 404);
            }
            
            $stats = [
                'total_bookings' => $customer->bookings->count(),
                'total_amount' => $customer->bookings->sum('grand_total'),
                'total_paid' => $customer->bookings->sum('paid_amount'),
                'outstanding' => $customer->bookings->sum('grand_total') - $customer->bookings->sum('paid_amount')
            ];
            
            $bookings = [];
            foreach ($customer->bookings as $booking) {
                $totalItems = $booking->items->sum('quantity');
                $deliveredItems = $booking->items->sum('delivered_quantity');
                
                $bookings[] = [
                    'id' => $booking->id,
                    'invoice_no' => $booking->invoice_no,
                    'booking_date' => $booking->booking_date,
                    'grand_total' => $booking->grand_total,
                    'paid_amount' => $booking->paid_amount,
                    'status' => $booking->status,
                    'status_display' => ucfirst(str_replace('_', ' ', $booking->status)),
                    'payment_status' => $booking->payment_status,
                    'payment_status_display' => ucfirst(str_replace('_', ' ', $booking->payment_status)),
                    'total_items' => $totalItems,
                    'delivered_items' => $deliveredItems,
                    'remaining_items' => $totalItems - $deliveredItems
                ];
            }
            
            return response()->json([
                'success' => true,
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'mobile' => $customer->mobile,
                    'area' => $customer->area,
                    'notes' => $customer->notes,
                    'codes' => $customer->codes
                ],
                'stats' => $stats,
                'bookings' => $bookings
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}