@extends('layouts.admin')

@section('title', 'Delivery Details')
@section('subtitle', 'Delivery #' . $delivery->id)

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
    .status-delivered { background: #d1fae5; color: #065f46; }
    .status-partial { background: #fed7aa; color: #9a3412; }
    .status-pending { background: #fef3c7; color: #92400e; }
</style>

<div class="space-y-6">
    <!-- Header Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="info-card bg-white rounded-xl p-4 border-l-4 border-green-500">
            <p class="text-gray-500 text-xs uppercase tracking-wide">Delivery ID</p>
            <p class="text-2xl font-bold">#{{ $delivery->id }}</p>
        </div>
        <div class="info-card bg-white rounded-xl p-4 border-l-4 border-blue-500">
            <p class="text-gray-500 text-xs uppercase tracking-wide">Invoice Number</p>
            <p class="text-xl font-bold text-blue-600">{{ $delivery->booking->invoice_no ?? 'N/A' }}</p>
        </div>
        <div class="info-card bg-white rounded-xl p-4 border-l-4 border-purple-500">
            <p class="text-gray-500 text-xs uppercase tracking-wide">Delivery Date</p>
            <p class="text-xl font-bold">{{ $delivery->delivery_date->format('d-m-Y') }}</p>
            <p class="text-xs text-gray-500">{{ $delivery->delivery_date->format('h:i A') }}</p>
        </div>
        <div class="info-card bg-white rounded-xl p-4 border-l-4 border-orange-500">
            <p class="text-gray-500 text-xs uppercase tracking-wide">Processed By</p>
            <p class="text-xl font-bold">{{ $delivery->creator->name ?? 'Admin' }}</p>
        </div>
    </div>

    <!-- Customer & Delivery Info -->
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
                    <span class="font-semibold">{{ $delivery->booking->customer->name ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between items-center pb-2 border-b">
                    <span class="text-gray-500">Customer Code</span>
                    <span class="font-mono bg-gray-100 px-2 py-1 rounded">{{ $delivery->booking->customer_code_used ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between items-center pb-2 border-b">
                    <span class="text-gray-500">Mobile Number</span>
                    <span class="font-semibold">{{ $delivery->booking->customer->mobile ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-500">Booking Status</span>
                    <span class="status-badge status-{{ $delivery->booking->status }}">
                        {{ ucfirst($delivery->booking->status) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Delivery Information -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-green-600 to-green-700">
                <h3 class="text-white font-semibold flex items-center gap-2">
                    <i class="ri-truck-line"></i> Delivery Information
                </h3>
            </div>
            <div class="p-6 space-y-3">
                <div class="flex justify-between items-center pb-2 border-b">
                    <span class="text-gray-500">Receiver Name</span>
                    <span class="font-semibold">{{ $delivery->receiver_name ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between items-center pb-2 border-b">
                    <span class="text-gray-500">Receiver Mobile</span>
                    <span class="font-semibold">{{ $delivery->receiver_mobile ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between items-center pb-2 border-b">
                    <span class="text-gray-500">Delivery Date & Time</span>
                    <span class="font-mono">{{ $delivery->delivery_date->format('d-m-Y h:i A') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-500">Delivery By</span>
                    <span class="font-semibold">{{ $delivery->creator->name ?? 'System' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Notes Section -->
    @if($delivery->notes)
    <div class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl shadow-sm overflow-hidden border border-yellow-200">
        <div class="px-6 py-4 bg-yellow-100 border-b border-yellow-200">
            <h3 class="font-semibold text-yellow-800 flex items-center gap-2">
                <i class="ri-sticky-note-line"></i> Delivery Notes
            </h3>
        </div>
        <div class="p-6">
            <p class="text-gray-700">{{ $delivery->notes }}</p>
        </div>
    </div>
    @endif

    <!-- Items Delivered -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-gray-700 to-gray-800">
            <h3 class="text-white font-semibold flex items-center gap-2">
                <i class="ri-shopping-bag-line"></i> Items Delivered
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cloth Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Color</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($delivery->items as $index => $item)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-gray-500">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 font-medium">{{ $item->bookingItem->clothType->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1">
                                <span class="w-3 h-3 rounded-full" style="background: {{ $item->bookingItem->color->color_code ?? '#gray' }}"></span>
                                {{ $item->bookingItem->color->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-10 h-6 bg-blue-100 text-blue-700 rounded font-semibold text-sm">
                                {{ $item->quantity_delivered }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">₹ {{ number_format($item->unit_price_at_delivery, 2) }}</td>
                        <td class="px-6 py-4 text-right font-semibold text-green-600">₹ {{ number_format($item->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-right font-bold text-lg">Total Amount:</td>
                        <td class="px-6 py-4 text-right font-bold text-xl text-green-600">
                            ₹ {{ number_format($delivery->items->sum('total'), 2) }}
                        </td>
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
                    <p class="text-2xl font-bold text-gray-800">₹ {{ number_format($delivery->booking->grand_total ?? 0, 2) }}</p>
                </div>
                <div class="text-center p-4 bg-green-50 rounded-lg">
                    <p class="text-gray-500 text-sm">Total Paid</p>
                    <p class="text-2xl font-bold text-green-600">₹ {{ number_format($delivery->booking->paid_amount ?? 0, 2) }}</p>
                </div>
                <div class="text-center p-4 bg-orange-50 rounded-lg">
                    <p class="text-gray-500 text-sm">Remaining Due</p>
                    <p class="text-2xl font-bold text-orange-600">₹ {{ number_format(($delivery->booking->grand_total ?? 0) - ($delivery->booking->paid_amount ?? 0), 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex justify-between items-center">
        <a href="{{ route('admin.deliveries.index') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
            <i class="ri-arrow-left-line"></i> Back to Deliveries
        </a>
        <div class="flex gap-3">
            <a href="{{ route('admin.bookings.show', $delivery->booking_id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="ri-file-list-line"></i> View Full Booking
            </a>
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                <i class="ri-printer-line"></i> Print
            </button>
        </div>
    </div>
</div>

<style>
@media print {
    .action-buttons, .admin-sidebar, .admin-header, .back-button {
        display: none !important;
    }
}
</style>
@endsection