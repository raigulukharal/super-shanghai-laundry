@extends('layouts.admin')

@section('title', 'Booking Report')
@section('subtitle', 'Booking details report')

@section('content')
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
        <h3 class="text-lg font-semibold">📋 Booking Report</h3>
        <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">🖨️ Print / Save PDF</button>
    </div>
    
    <div class="p-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
            <div><strong>Start Date:</strong> {{ $request->start_date ?? 'All' }}</div>
            <div><strong>End Date:</strong> {{ $request->end_date ?? 'All' }}</div>
            <div><strong>Status:</strong> {{ ucfirst($request->status ?? 'All') }}</div>
            <div><strong>Total Bookings:</strong> {{ $bookings->count() }}</div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full border">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">Invoice #</th>
                        <th class="px-4 py-2 text-left">Customer</th>
                        <th class="px-4 py-2 text-right">Amount</th>
                        <th class="px-4 py-2 text-right">Paid</th>
                        <th class="px-4 py-2 text-right">Due</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-left">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                    <tr class="border-t">
                        <td class="px-4 py-2 font-mono">{{ $booking->invoice_no }}</td>
                        <td class="px-4 py-2">{{ $booking->customer->name ?? 'N/A' }}</td>
                        <td class="px-4 py-2 text-right">Rs. {{ number_format($booking->grand_total, 2) }}</td>
                        <td class="px-4 py-2 text-right">Rs. {{ number_format($booking->paid_amount, 2) }}</td>
                        <td class="px-4 py-2 text-right @if(($booking->grand_total - $booking->paid_amount) > 0) text-red-600 @endif">Rs. {{ number_format($booking->grand_total - $booking->paid_amount, 2) }}</td>
                        <td class="px-4 py-2">{{ ucfirst($booking->status) }}</td>
                        <td class="px-4 py-2">{{ $booking->booking_date->format('d-m-Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">No bookings found</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 font-bold">
                    <tr>
                        <td colspan="2" class="px-4 py-2 text-right">Total:</td>
                        <td class="px-4 py-2 text-right">Rs. {{ number_format($totalAmount, 2) }}</td>
                        <td class="px-4 py-2 text-right">Rs. {{ number_format($totalPaid, 2) }}</td>
                        <td class="px-4 py-2 text-right">Rs. {{ number_format($totalDue, 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection