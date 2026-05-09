@extends('layouts.admin')

@section('title', 'Due Amounts Report')
@section('subtitle', 'All customers with pending payments')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Due Amounts Report</h2>
            <p class="text-gray-500 text-sm mt-1">Customers with pending payments</p>
        </div>
        <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
            <i class="ri-printer-line"></i> Print Report
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs uppercase tracking-wide">Total Bookings</p>
                    <p class="text-2xl font-bold">{{ $totalBookings }}</p>
                </div>
                <i class="ri-shopping-cart-line text-2xl text-blue-500"></i>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs uppercase tracking-wide">Total Amount</p>
                    <p class="text-2xl font-bold text-green-600">Rs. {{ number_format($totalAmount, 2) }}</p>
                </div>
                <i class="ri-money-rupee-circle-line text-2xl text-green-500"></i>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs uppercase tracking-wide">Total Paid</p>
                    <p class="text-2xl font-bold text-purple-600">Rs. {{ number_format($totalPaid, 2) }}</p>
                </div>
                <i class="ri-wallet-line text-2xl text-purple-500"></i>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs uppercase tracking-wide">Total Due</p>
                    <p class="text-2xl font-bold text-red-600">Rs. {{ number_format($totalDue, 2) }}</p>
                    <p class="text-xs text-gray-500">{{ number_format($duePercentage, 1) }}% of total</p>
                </div>
                <i class="ri-alert-line text-2xl text-red-500"></i>
            </div>
        </div>
    </div>

    <!-- Due Bookings Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h3 class="text-lg font-semibold">Pending Dues by Customer</h3>
        </div>
        
        <div class="overflow-x-auto">
             <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Invoice #</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Customer Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Mobile</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">Total Amount</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">Paid Amount</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">Due Amount</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Date</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dueBookings as $booking)
                    @php
                        $dueAmount = $booking->grand_total - $booking->paid_amount;
                    @endphp
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono font-bold">{{ $booking->invoice_no }}</td>
                        <td class="px-4 py-3">{{ $booking->customer->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3">{{ $booking->customer->mobile ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-right">Rs. {{ number_format($booking->grand_total, 2) }}</td>
                        <td class="px-4 py-3 text-right text-green-600">Rs. {{ number_format($booking->paid_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right text-red-600 font-bold">Rs. {{ number_format($dueAmount, 2) }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($booking->status == 'pending') bg-yellow-100 text-yellow-800
                                @elseif($booking->status == 'partial_delivered') bg-orange-100 text-orange-800
                                @elseif($booking->status == 'delivered') bg-green-100 text-green-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $booking->booking_date->format('d-m-Y') }}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('admin.bookings.show', $booking->id) }}" class="text-blue-600 hover:text-blue-800" title="View Booking">
                                <i class="ri-eye-line text-lg"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                            <i class="ri-checkbox-circle-line text-4xl mb-2 block text-green-500"></i>
                            No pending dues! All customers have cleared their payments.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
             </table>
        </div>
        
        <div class="px-4 py-3 border-t bg-gray-50">
            <div class="flex justify-between items-center">
                <p class="text-sm text-gray-600">
                    <i class="ri-information-line"></i> Showing {{ $dueBookings->count() }} bookings with pending payments
                </p>
                <p class="text-sm font-bold text-red-600">
                    Total Due: Rs. {{ number_format($totalDue, 2) }}
                </p>
            </div>
        </div>
    </div>

    <!-- Information Box -->
    <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-lg p-4">
        <div class="flex items-start gap-3">
            <i class="ri-alert-line text-yellow-600 text-xl"></i>
            <div>
                <p class="text-sm text-yellow-800 font-semibold">Payment Reminder</p>
                <p class="text-sm text-yellow-700 mt-1">
                    • Total outstanding amount: <strong>Rs. {{ number_format($totalDue, 2) }}</strong><br>
                    • Follow up with customers who have pending payments.<br>
                    • Click on the eye icon to view full booking details and record payment.
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        header, footer, .btn-print, .bg-yellow-50, .bg-gray-50 .border-t {
            display: none !important;
        }
        body {
            background: white;
            padding: 0;
            margin: 0;
        }
        .shadow-sm {
            box-shadow: none;
        }
        table {
            border: 1px solid #ddd;
        }
        th, td {
            border: 1px solid #ddd;
        }
    }
</style>
@endsection