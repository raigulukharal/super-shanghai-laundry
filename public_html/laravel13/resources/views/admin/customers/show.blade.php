@extends('layouts.admin')

@section('title', 'Customer Details')
@section('subtitle', 'View customer information and history')

@section('content')
<style>
    .stat-card {
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-2px);
    }
</style>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Customer Details</h2>
            <p class="text-gray-500 text-sm mt-1">View customer information and booking history</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.customers.edit', $customer->id) }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                <i class="ri-edit-line"></i> Edit Customer
            </a>
            <a href="{{ route('admin.customers.index') }}" class="border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition flex items-center gap-2">
                <i class="ri-arrow-left-line"></i> Back
            </a>
        </div>
    </div>

    <!-- Customer Info Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
            <p class="text-gray-500 text-xs uppercase tracking-wide">Customer Name</p>
            <p class="text-xl font-bold text-gray-800">{{ $customer->name }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500">
            <p class="text-gray-500 text-xs uppercase tracking-wide">Mobile Number</p>
            <p class="text-xl font-bold text-gray-800">{{ $customer->mobile }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-purple-500">
            <p class="text-gray-500 text-xs uppercase tracking-wide">Area / Address</p>
            <p class="text-xl font-bold text-gray-800">{{ $customer->area ?? 'N/A' }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-orange-500">
            <p class="text-gray-500 text-xs uppercase tracking-wide">Customer Codes</p>
            <p class="text-xl font-bold text-gray-800">
                @foreach($customer->codes as $code)
                <span class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded-full mr-1">{{ $code->code }}</span>
                @endforeach
                @if($customer->codes->count() == 0)
                <span class="text-gray-400 text-sm">No codes</span>
                @endif
            </p>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="stat-card bg-blue-50 rounded-xl p-4 text-center">
            <p class="text-xs text-gray-500">Total Bookings</p>
            <p class="text-2xl font-bold text-blue-600">{{ $stats['total_bookings'] }}</p>
        </div>
        <div class="stat-card bg-green-50 rounded-xl p-4 text-center">
            <p class="text-xs text-gray-500">Total Spent</p>
            <p class="text-2xl font-bold text-green-600">Rs. {{ number_format($stats['total_amount'], 2) }}</p>
        </div>
        <div class="stat-card bg-yellow-50 rounded-xl p-4 text-center">
            <p class="text-xs text-gray-500">Total Paid</p>
            <p class="text-2xl font-bold">Rs. {{ number_format($stats['total_paid'], 2) }}</p>
        </div>
        <div class="stat-card bg-red-50 rounded-xl p-4 text-center">
            <p class="text-xs text-gray-500">Outstanding</p>
            <p class="text-2xl font-bold text-red-600">Rs. {{ number_format($stats['outstanding'], 2) }}</p>
        </div>
    </div>

    <!-- Booking History -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-gray-700 to-gray-800">
            <h3 class="text-white font-semibold flex items-center gap-2">
                <i class="ri-history-line"></i> Booking History
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Paid</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Due</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Delivery</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($customer->bookings as $booking)
                    @php
                        $due = $booking->grand_total - $booking->paid_amount;
                        $totalItems = $booking->items->sum('quantity');
                        $deliveredItems = $booking->items->sum('delivered_quantity');
                        $remainingItems = $totalItems - $deliveredItems;
                        
                        $statusClass = '';
                        if($booking->status == 'delivered') $statusClass = 'bg-green-100 text-green-800';
                        elseif($booking->status == 'cancelled') $statusClass = 'bg-red-100 text-red-800';
                        elseif($booking->status == 'partial_delivered') $statusClass = 'bg-yellow-100 text-yellow-800';
                        else $statusClass = 'bg-blue-100 text-blue-800';
                        
                        $paymentClass = '';
                        if($booking->payment_status == 'full_pay') $paymentClass = 'bg-green-100 text-green-800';
                        elseif($booking->payment_status == 'partial_pay') $paymentClass = 'bg-yellow-100 text-yellow-800';
                        else $paymentClass = 'bg-red-100 text-red-800';
                        
                        $deliveryText = '';
                        if($remainingItems == 0) $deliveryText = '✅ All Delivered';
                        elseif($deliveredItems > 0) $deliveryText = "🔄 {$deliveredItems}/{$totalItems} items";
                        else $deliveryText = "⏳ Pending";
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 font-mono font-bold text-blue-600">{{ $booking->invoice_no }}</td>
                        <td class="px-6 py-4">{{ $booking->booking_date->format('d-m-Y') }}</td>
                        <td class="px-6 py-4 text-right">Rs. {{ number_format($booking->grand_total, 2) }}</td>
                        <td class="px-6 py-4 text-right text-green-600">Rs. {{ number_format($booking->paid_amount, 2) }}</td>
                        <td class="px-6 py-4 text-right {{ $due > 0 ? 'text-red-600 font-bold' : 'text-green-600' }}">
                            Rs. {{ number_format($due, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full {{ $statusClass }}">
                                {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full {{ $paymentClass }}">
                                {{ ucfirst(str_replace('_', ' ', $booking->payment_status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">{{ $deliveryText }}</td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex gap-2 justify-center">
                                <a href="{{ route('admin.bookings.download-invoice', $booking->id) }}" target="_blank" class="text-purple-600 hover:text-purple-800" title="Download Invoice">
                                    <i class="ri-download-line text-xl"></i>
                                </a>
                                <a href="{{ route('admin.bookings.show', $booking->id) }}" class="text-blue-600 hover:text-blue-800" title="View Booking">
                                    <i class="ri-eye-line text-xl"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                            <i class="ri-inbox-line text-4xl"></i>
                            <p class="mt-2">No bookings found for this customer</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection