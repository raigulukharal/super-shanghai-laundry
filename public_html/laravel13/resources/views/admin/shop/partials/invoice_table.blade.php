@if($invoices->count() > 0)
<div class="overflow-x-auto mt-4">
    <table class="w-full text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-3 py-2 text-left">Invoice #</th>
                <th class="px-3 py-2 text-left">Range</th>
                <th class="px-3 py-2 text-left">Status</th>
                <th class="px-3 py-2 text-left">Type</th>
                <th class="px-3 py-2 text-left">Date</th>
                <th class="px-3 py-2 text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoices as $inv)
            <tr class="border-b">
                <td class="px-3 py-2 font-mono">
                    {{ $inv->invoice_no }}
                    @if($inv->is_missing)
                    <span class="status-badge missing ml-1">Missing</span>
                    @endif
                </td>
                <td class="px-3 py-2">{{ $inv->invoiceRange->range_name ?? 'N/A' }}</td>
                <td class="px-3 py-2">
                    <span class="status-badge 
                        @if($inv->status == 'in_shop') status-in-shop
                        @elseif($inv->status == 'delivered') status-delivered
                        @else status-pending @endif">
                        {{ ucfirst(str_replace('_', ' ', $inv->status)) }}
                    </span>
                </td>
                <td class="px-3 py-2">
                    <span class="status-badge @if($inv->type == 'regular') type-regular @else type-extra @endif">
                        {{ ucfirst($inv->type) }}
                    </span>
                </td>
                <td class="px-3 py-2">{{ $inv->invoiceRange->range_date ?? 'N/A' }}</td>
                <td class="px-3 py-2 text-center">
                    <button onclick="viewInvoice('{{ $inv->invoice_no }}')" class="text-blue-600 hover:text-blue-800 mr-2" title="View">
                        <i class="ri-eye-line"></i>
                    </button>
                    <button onclick="removeInvoice({{ $inv->id }})" class="text-red-600 hover:text-red-800" title="Remove">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">
    {{ $invoices->links() }}
</div>
@else
<div class="text-center py-8 text-gray-500">
    <i class="ri-inbox-line text-4xl mb-2 block"></i>
    No invoices found
</div>
@endif

<script>
function viewInvoice(invoiceNo) {
    window.open('/admin/bookings?invoice_no=' + invoiceNo, '_blank');
}
</script>