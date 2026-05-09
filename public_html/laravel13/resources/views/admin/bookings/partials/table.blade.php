@foreach($bookings as $booking)
@php
    $customerCode = $booking->customer_code_used ?? ($booking->customer->codes->first()->code ?? 'N/A');
    $dueAmount = $booking->grand_total - $booking->paid_amount;
@endphp
<tr class="border-b hover:bg-gray-50 transition">
    <td class="px-4 py-3 font-mono font-medium">{{ $booking->invoice_no }}</td>
    <td class="px-4 py-3">{{ $booking->customer->name ?? 'N/A' }}</td>
    <td class="px-4 py-3">
        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700">
            {{ $customerCode }}
        </span>
    </td>
    <td class="px-4 py-3">{{ $booking->customer->mobile ?? 'N/A' }}</td>
    <td class="px-4 py-3 text-right">Rs. {{ number_format($booking->grand_total, 2) }}</td>
    <td class="px-4 py-3 text-right">Rs. {{ number_format($booking->paid_amount, 2) }}</td>
    <td class="px-4 py-3 text-right @if($dueAmount > 0) text-red-600 font-semibold @endif">
        Rs. {{ number_format($dueAmount, 2) }}
    </td>
    <td class="px-4 py-3">
        <span class="px-2 py-1 text-xs rounded-full 
            @if($booking->status == 'delivered') bg-green-100 text-green-800
            @elseif($booking->status == 'cancelled') bg-red-100 text-red-800
            @elseif($booking->status == 'partial_delivered') bg-yellow-100 text-yellow-800
            @else bg-blue-100 text-blue-800 @endif">
            {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
        </span>
    </td>
    <td class="px-4 py-3">
        <span class="px-2 py-1 text-xs rounded-full 
            @if($booking->payment_status == 'full_pay') bg-green-100 text-green-800
            @elseif($booking->payment_status == 'partial_pay') bg-yellow-100 text-yellow-800
            @else bg-red-100 text-red-800 @endif">
            {{ ucfirst(str_replace('_', ' ', $booking->payment_status)) }}
        </span>
    </td>
    <td class="px-4 py-3">{{ $booking->booking_date->format('d-m-Y') }}</td>
    <td class="px-4 py-3 text-center">
        <div class="flex gap-2 justify-center">
            <button onclick="viewBooking({{ $booking->id }})" class="text-blue-600 hover:text-blue-800" title="View Details">
                <i class="ri-eye-line text-xl"></i>
            </button>
            @if($booking->status == 'pending')
            <a href="{{ route('admin.bookings.edit', $booking->id) }}" class="text-green-600 hover:text-green-800" title="Edit">
                <i class="ri-edit-line text-xl"></i>
            </a>
            <button onclick="cancelBooking({{ $booking->id }})" class="text-red-600 hover:text-red-800" title="Cancel">
                <i class="ri-close-circle-line text-xl"></i>
            </button>
            @endif
            <a href="/admin/bookings/{{ $booking->id }}/download-invoice" target="_blank" class="text-purple-600 hover:text-purple-800" title="Download Invoice">
                <i class="ri-download-line text-xl"></i>
            </a>
        </div>
    </td>
</tr>
@endforeach

@if($bookings->isEmpty())
<tr>
    <td colspan="11" class="px-4 py-8 text-center text-gray-500">No bookings found</td>
</tr>
@endif

@if(method_exists($bookings, 'links'))
<div class="px-4 py-3">
    {{ $bookings->links() }}
</div>
@endif