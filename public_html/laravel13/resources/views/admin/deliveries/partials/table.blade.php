@forelse($deliveries as $delivery)
<tr class="border-b hover:bg-gray-50">
    <td class="px-4 py-3">#{{ $delivery->id }}</td>
    <td class="px-4 py-3 font-mono font-bold">{{ $delivery->booking->invoice_no ?? 'N/A' }}</td>
    <td class="px-4 py-3">{{ $delivery->booking->customer->name ?? 'N/A' }}</td>
    <td class="px-4 py-3">
        <span class="bg-gray-100 px-2 py-1 rounded text-xs">
            {{ $delivery->booking->customer_code_used ?? ($delivery->booking->customer->codes->first()->code ?? 'N/A') }}
        </span>
    </td>
    <td class="px-4 py-3">{{ $delivery->receiver_name }}</td>
    <td class="px-4 py-3 text-center">
        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
            {{ $delivery->items->count() }} items
        </span>
    </td>
    <td class="px-4 py-3">{{ $delivery->delivery_date->format('d-m-Y') }}</td>
    <td class="px-4 py-3 text-center">
        <a href="{{ route('admin.deliveries.show', $delivery->id) }}" class="text-blue-600 hover:text-blue-800" title="View Details">
            <i class="ri-eye-line text-xl"></i>
        </a>
    </td>
</tr>
@empty
<tr class="border-b">
    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
        <i class="ri-inbox-line text-4xl mb-2 block"></i>
        No deliveries found
    </td>
</tr>
@endforelse