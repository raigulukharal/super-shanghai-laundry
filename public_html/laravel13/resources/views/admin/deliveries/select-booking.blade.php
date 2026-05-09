@extends('layouts.admin')

@section('title', 'Select Booking for Delivery')
@section('subtitle', 'Choose a booking to process delivery')

@section('content')
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <h3 class="text-lg font-semibold">Select Booking</h3>
        <p class="text-sm text-gray-500">Choose a booking to start delivery process</p>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold">Invoice #</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold">Customer</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold">Mobile</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold">Total Amount</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingBookings as $booking)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono">{{ $booking->invoice_no }}</td>
                    <td class="px-4 py-3">{{ $booking->customer->name ?? 'N/A' }}</td>
                    <td class="px-4 py-3">{{ $booking->customer->mobile ?? 'N/A' }}</td>
                    <td class="px-4 py-3">Rs. {{ number_format($booking->grand_total, 2) }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full 
                            @if($booking->status == 'pending') bg-yellow-100 text-yellow-800
                            @else bg-orange-100 text-orange-800 @endif">
                            {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('admin.deliveries.create', $booking->id) }}" 
                           class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                            Start Delivery
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                        <i class="ri-checkbox-circle-line text-4xl mb-2 block text-green-300"></i>
                        No pending deliveries found
                        <p class="text-sm mt-1">All bookings have been delivered</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection