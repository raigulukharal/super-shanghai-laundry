@foreach($customers as $customer)
@php
    $dueAmount = $customer->bookings_sum_grand_total - $customer->bookings_sum_paid_amount;
@endphp
<tr class="border-b hover:bg-gray-50 transition">
    <td class="px-4 py-3">{{ $customer->id }}</td>
    <td class="px-4 py-3 font-medium">{{ $customer->name }}</td>
    <td class="px-4 py-3">{{ $customer->mobile }}</td>
    <td class="px-4 py-3">
        @foreach($customer->codes as $code)
        <span class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded-full mr-1">{{ $code->code }}</span>
        @endforeach
    </td>
    <td class="px-4 py-3">{{ $customer->area ?? '-' }}</td>
    <td class="px-4 py-3 text-center">
        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">{{ $customer->bookings_count }}</span>
    </td>
    <td class="px-4 py-3 text-right">Rs. {{ number_format($customer->bookings_sum_grand_total ?? 0, 2) }}</td>
    <td class="px-4 py-3 text-right @if($dueAmount > 0) text-red-600 font-semibold @else text-green-600 @endif">
        Rs. {{ number_format($dueAmount, 2) }}
    </td>
    <td class="px-4 py-3 text-center">
        <button onclick="viewCustomer({{ $customer->id }})" class="text-blue-600 hover:text-blue-800 mx-1" title="View Details">
            <i class="ri-eye-line text-xl"></i>
        </button>
        <a href="{{ route('admin.customers.edit', $customer->id) }}" class="text-green-600 hover:text-green-800 mx-1" title="Edit">
            <i class="ri-edit-line text-xl"></i>
        </a>
    </td>
</tr>
@endforeach

@if($customers->isEmpty())
<tr>
    <td colspan="9" class="px-4 py-8 text-center text-gray-500">No customers found</td>
</tr>
@endif