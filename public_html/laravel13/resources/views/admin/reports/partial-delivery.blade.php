@extends('layouts.admin')

@section('title', 'Partial Delivery Report')
@section('subtitle', 'Bookings with partial deliveries')

@section('content')
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
        <h3 class="text-lg font-semibold">🔄 Partial Delivery Report</h3>
        <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">🖨️ Print</button>
    </div>
    
    <div class="p-6">
        <div class="mb-4"><strong>Total Partial Deliveries:</strong> {{ $bookings->count() }}</div>
        
        <div class="overflow-x-auto">
            <table class="w-full border">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2">Invoice #</th>
                        <th class="px-4 py-2">Customer</th>
                        <th class="px-4 py-2">Total Items</th>
                        <th class="px-4 py-2">Delivered</th>
                        <th class="px-4 py-2">Pending</th>
                        <th class="px-4 py-2">Booking Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                    @php
                        $totalItems = $booking->items->count();
                        $deliveredItems = $booking->items->where('status', 'delivered')->count();
                        $pendingItems = $totalItems - $deliveredItems;
                    @endphp
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $booking->invoice_no }}</td>
                        <td class="px-4 py-2">{{ $booking->customer->name ?? 'N/A' }}</td>
                        <td class="px-4 py-2 text-center">{{ $totalItems }}</td>
                        <td class="px-4 py-2 text-center text-green-600">{{ $deliveredItems }}</td>
                        <td class="px-4 py-2 text-center text-red-600">{{ $pendingItems }}</td>
                        <td class="px-4 py-2">{{ $booking->booking_date->format('d-m-Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center">No partial deliveries found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection