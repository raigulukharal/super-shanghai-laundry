@extends('layouts.admin')

@section('title', 'Booking Details')
@section('subtitle', 'Booking #' . $booking->invoice_no)

@section('content')
<style>
    .info-card {
        transition: all 0.3s ease;
        border: 1px solid #e5e7eb;
    }
    .info-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-delivered { background: #d1fae5; color: #065f46; }
    .status-partial_delivered { background: #fed7aa; color: #9a3412; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }
    .payment-full_pay { background: #d1fae5; color: #065f46; }
    .payment-partial_pay { background: #fed7aa; color: #9a3412; }
    .payment-full_due { background: #fee2e2; color: #dc2626; }
</style>

<div class="space-y-6">
    <!-- Header Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="info-card bg-white rounded-xl p-4 border-l-4 border-blue-500">
            <p class="text-gray-500 text-xs uppercase tracking-wide">Invoice Number</p>
            <p class="text-2xl font-bold text-blue-600">{{ $booking->invoice_no }}</p>
        </div>
        <div class="info-card bg-white rounded-xl p-4 border-l-4 border-green-500">
            <p class="text-gray-500 text-xs uppercase tracking-wide">Booking Date</p>
            <p class="text-xl font-bold">{{ $booking->booking_date instanceof \Carbon\Carbon ? $booking->booking_date->format('d-m-Y') : date('d-m-Y', strtotime($booking->booking_date)) }}</p>
        </div>
        <div class="info-card bg-white rounded-xl p-4 border-l-4 border-purple-500">
            <p class="text-gray-500 text-xs uppercase tracking-wide">Total Amount</p>
            <p class="text-xl font-bold text-purple-600">Rs. {{ number_format($booking->grand_total, 2) }}</p>
        </div>
        <div class="info-card bg-white rounded-xl p-4 border-l-4 border-orange-500">
            <p class="text-gray-500 text-xs uppercase tracking-wide">Status</p>
            <p class="text-xl">
                <span class="status-badge status-{{ $booking->status }}">
                    {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                </span>
            </p>
        </div>
    </div>

    <!-- Customer & Booking Info -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Customer Information -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-700">
                <h3 class="text-white font-semibold flex items-center gap-2">
                    <i class="ri-user-line"></i> Customer Information
                </h3>
            </div>
            <div class="p-6 space-y-3">
                <div class="flex justify-between items-center pb-2 border-b">
                    <span class="text-gray-500">Customer Name</span>
                    <span class="font-semibold">{{ $booking->customer->name ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between items-center pb-2 border-b">
                    <span class="text-gray-500">Customer Code</span>
                    <span class="font-mono bg-gray-100 px-2 py-1 rounded">{{ $booking->customer_code_used ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between items-center pb-2 border-b">
                    <span class="text-gray-500">Mobile Number</span>
                    <span class="font-semibold">{{ $booking->customer->mobile ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between items-center">
    <span class="text-gray-500">Address / Area</span>
    <span class="font-semibold text-right">
        @if($booking->customer)
            {{ $booking->customer->area ?? 'N/A' }}
        @else
            N/A
        @endif
    </span>
</div>
            </div>
        </div>

        <!-- Booking Information -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-green-600 to-green-700">
                <h3 class="text-white font-semibold flex items-center gap-2">
                    <i class="ri-calendar-line"></i> Booking Information
                </h3>
            </div>
            <div class="p-6 space-y-3">
                <div class="flex justify-between items-center pb-2 border-b">
                    <span class="text-gray-500">Booking Date</span>
                    <span class="font-semibold">{{ $booking->booking_date instanceof \Carbon\Carbon ? $booking->booking_date->format('d-m-Y') : date('d-m-Y', strtotime($booking->booking_date)) }}</span>
                </div>
                <div class="flex justify-between items-center pb-2 border-b">
                    <span class="text-gray-500">Expected Delivery Date</span>
                    <span class="font-semibold">{{ $booking->expected_delivery_date ? (\Carbon\Carbon::parse($booking->expected_delivery_date)->format('d-m-Y')) : 'Not specified' }}</span>
                </div>
                <div class="flex justify-between items-center pb-2 border-b">
                    <span class="text-gray-500">Payment Status</span>
                    <span class="status-badge payment-{{ $booking->payment_status }}">
                        {{ ucfirst(str_replace('_', ' ', $booking->payment_status)) }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-500">Created By</span>
                    <span class="font-semibold">{{ $booking->creator->name ?? 'Admin' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Notes Section -->
    @if($booking->customer_notes)
    <div class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl shadow-sm overflow-hidden border border-yellow-200">
        <div class="px-6 py-4 bg-yellow-100 border-b border-yellow-200">
            <h3 class="font-semibold text-yellow-800 flex items-center gap-2">
                <i class="ri-sticky-note-line"></i> Customer Notes
            </h3>
        </div>
        <div class="p-6">
            <p class="text-gray-700">{{ $booking->customer_notes }}</p>
        </div>
    </div>
    @endif

    <!-- Items Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-gray-700 to-gray-800">
            <h3 class="text-white font-semibold flex items-center gap-2">
                <i class="ri-shopping-bag-line"></i> Booking Items
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cloth Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Color</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Delivery Type</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($booking->items as $index => $item)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-gray-500">{{ $index + 1 }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full bg-indigo-100 text-indigo-700">
                                {{ $item->clothType->category->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 font-medium">{{ $item->clothType->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1">
                                <span class="w-3 h-3 rounded-full" style="background: {{ $item->color->color_code ?? '#gray' }}"></span>
                                {{ $item->color->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-10 h-6 bg-blue-100 text-blue-700 rounded font-semibold text-sm">
                                {{ $item->quantity }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">Rs. {{ number_format($item->unit_price, 2) }}</td>
                        <td class="px-6 py-4 text-right font-semibold text-green-600">Rs. {{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2 py-1 text-xs rounded-full {{ $item->delivery_type == 'urgent' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ ucfirst($item->delivery_type) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-right font-bold text-lg">Sub Total:</td>
                        <td class="px-6 py-4 text-right font-bold">Rs. {{ number_format($booking->total_amount, 2) }}</td>
                        </tr>
                    </tr>
                    @if($booking->discount > 0)
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-right text-red-600">Discount:</td>
                        <td class="px-6 py-4 text-right text-red-600">- Rs. {{ number_format($booking->discount, 2) }}</td>
                        <td></td>
                    </tr>
                    @endif
                    @if($booking->other_charges > 0)
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-right text-orange-600">Other Charges:</td>
                        <td class="px-6 py-4 text-right text-orange-600">+ Rs. {{ number_format($booking->other_charges, 2) }}</td>
                        <td></td>
                    </tr>
                    @endif
                    <tr class="border-t-2 border-gray-300">
                        <td colspan="6" class="px-6 py-4 text-right font-bold text-lg">Grand Total:</td>
                        <td class="px-6 py-4 text-right font-bold text-xl text-green-600">Rs. {{ number_format($booking->grand_total, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Payment Summary -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-emerald-600 to-teal-600">
            <h3 class="text-white font-semibold flex items-center gap-2">
                <i class="ri-money-rupee-circle-line"></i> Payment Summary
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-500 text-sm">Grand Total</p>
                    <p class="text-2xl font-bold text-gray-800">Rs. {{ number_format($booking->grand_total, 2) }}</p>
                </div>
                <div class="text-center p-4 bg-green-50 rounded-lg">
                    <p class="text-gray-500 text-sm">Total Paid</p>
                    <p class="text-2xl font-bold text-green-600">Rs. {{ number_format($booking->paid_amount, 2) }}</p>
                </div>
                <div class="text-center p-4 bg-orange-50 rounded-lg">
                    <p class="text-gray-500 text-sm">Remaining Due</p>
                    <p class="text-2xl font-bold text-orange-600">Rs. {{ number_format($booking->grand_total - $booking->paid_amount, 2) }}</p>
                </div>
            </div>
            
            @if($booking->payments->count() > 0)
            <div class="mt-6">
                <h4 class="font-semibold mb-3">Payment History</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left">Date</th>
                                <th class="px-4 py-2 text-right">Amount</th>
                                <th class="px-4 py-2 text-left">Method</th>
                                <th class="px-4 py-2 text-left">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($booking->payments as $payment)
                            <tr class="border-b">
                                <td class="px-4 py-2">{{ $payment->payment_date instanceof \Carbon\Carbon ? $payment->payment_date->format('d-m-Y') : date('d-m-Y', strtotime($payment->payment_date)) }}</td>
                                <td class="px-4 py-2 text-right font-semibold text-green-600">Rs. {{ number_format($payment->amount, 2) }}</td>
                                <td class="px-4 py-2">{{ ucfirst($payment->payment_method) }}</td>
                                <td class="px-4 py-2">{{ $payment->notes ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex justify-between items-center">
        <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
            <i class="ri-arrow-left-line"></i> Back to Bookings
        </a>
        <div class="flex gap-3">
            <a href="{{ route('admin.bookings.edit', $booking->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <i class="ri-edit-line"></i> Edit Booking
            </a>
            <!--@if($booking->status != 'delivered' && $booking->status != 'cancelled')-->
            <!--<a href="{{ route('admin.deliveries.create', $booking->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">-->
            <!--    <i class="ri-truck-line"></i> Create Delivery-->
            <!--</a>-->
            <!--@endif-->
            <!--<a href="{{ route('admin.bookings.download-invoice', $booking->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">-->
            <!--    <i class="ri-download-line"></i> Download Invoice-->
            <!--</a>-->
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                <i class="ri-printer-line"></i> Print
            </button>
        </div>
    </div>
</div>

<style>
@media print {
    .admin-sidebar, .admin-header, .action-buttons {
        display: none !important;
    }
}
</style>
@endsection