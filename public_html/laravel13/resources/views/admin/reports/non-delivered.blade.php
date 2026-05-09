@extends('layouts.admin')

@section('title', 'Non-Delivered Items Report')
@section('subtitle', 'Items pending delivery')

@section('content')
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
        <h3 class="text-lg font-semibold">⏳ Non-Delivered Items Report</h3>
        <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition">
            <i class="ri-printer-line"></i> Print Report
        </button>
    </div>
    
    <div class="p-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="bg-yellow-50 rounded-lg p-4 border-l-4 border-yellow-500">
                <p class="text-sm text-gray-600">Total Pending Items (Rows)</p>
                <p class="text-2xl font-bold text-yellow-700">{{ $items->count() }}</p>
                <p class="text-xs text-gray-500">Number of items with pending delivery</p>
            </div>
            <div class="bg-red-50 rounded-lg p-4 border-l-4 border-red-500">
                <p class="text-sm text-gray-600">Total Quantity Pending</p>
                <p class="text-2xl font-bold text-red-700">{{ $totalPending }}</p>
                <p class="text-xs text-gray-500">Total pieces yet to be delivered</p>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full border border-gray-200 rounded-lg">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 border-b">S.No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 border-b">Invoice #</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 border-b">Customer Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 border-b">Cloth Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 border-b">Color</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 border-b">Ordered</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 border-b">Delivered</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 border-b">Pending</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $index => $item)
                    @php
                        $delivered = $item->delivered_quantity ?? 0;
                        $pending = $item->quantity - $delivered;
                        $rowClass = $index % 2 == 0 ? 'bg-white' : 'bg-gray-50';
                    @endphp
                    <tr class="{{ $rowClass }} border-b hover:bg-blue-50 transition">
                        <td class="px-4 py-2 text-sm">{{ $index + 1 }}</td>
                        <td class="px-4 py-2 text-sm font-mono font-bold">{{ $item->booking->invoice_no ?? 'N/A' }}</td>
                        <td class="px-4 py-2 text-sm">{{ $item->booking->customer->name ?? 'N/A' }}</td>
                        <td class="px-4 py-2 text-sm">{{ $item->clothType->name ?? 'N/A' }}</td>
                        <td class="px-4 py-2 text-sm">
                            @if($item->color)
                                <span class="inline-flex items-center gap-1">
                                    <span class="w-3 h-3 rounded-full" style="background-color: {{ $item->color->code ?? '#ccc' }}"></span>
                                    {{ $item->color->name ?? 'N/A' }}
                                </span>
                            @else
                                <span class="text-gray-400">Not Selected</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-sm text-center font-semibold">{{ $item->quantity }}</td>
                        <td class="px-4 py-2 text-sm text-center text-green-600">{{ $delivered }}</td>
                        <td class="px-4 py-2 text-sm text-center text-red-600 font-bold">{{ $pending }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                            <i class="ri-checkbox-circle-line text-4xl mb-2 block text-green-500"></i>
                            🎉 All items delivered! No pending deliveries.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-100">
                    <tr>
                        <td colspan="5" class="px-4 py-3 text-right font-bold">Total:</td>
                        <td class="px-4 py-3 text-center font-bold">{{ $items->sum('quantity') }}</td>
                        <td class="px-4 py-3 text-center font-bold text-green-600">{{ $items->sum('delivered_quantity') }}</td>
                        <td class="px-4 py-3 text-center font-bold text-red-600">{{ $totalPending }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <!-- Export Options -->
        <div class="mt-6 flex justify-end gap-3">
            <button onclick="exportToCSV()" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition">
                <i class="ri-file-excel-line"></i> Export to CSV
            </button>
            <button onclick="window.print()" class="bg-purple-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-purple-700 transition">
                <i class="ri-printer-line"></i> Print
            </button>
        </div>
    </div>
</div>

<script>
function exportToCSV() {
    let csv = [];
    let rows = [];
    
    // Table headers
    rows.push(['S.No', 'Invoice #', 'Customer Name', 'Cloth Type', 'Color', 'Ordered', 'Delivered', 'Pending']);
    
    // Table data
    @foreach($items as $index => $item)
    rows.push([
        '{{ $index + 1 }}',
        '{{ $item->booking->invoice_no ?? "N/A" }}',
        '{{ $item->booking->customer->name ?? "N/A" }}',
        '{{ $item->clothType->name ?? "N/A" }}',
        '{{ $item->color->name ?? "Not Selected" }}',
        '{{ $item->quantity }}',
        '{{ $item->delivered_quantity ?? 0 }}',
        '{{ $item->quantity - ($item->delivered_quantity ?? 0) }}'
    ]);
    @endforeach
    
    // Add totals row
    rows.push(['', '', '', '', 'TOTAL:', '{{ $items->sum("quantity") }}', '{{ $items->sum("delivered_quantity") }}', '{{ $totalPending }}']);
    
    rows.forEach(function(row) {
        csv.push(row.join(','));
    });
    
    // Download CSV
    let blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    let link = document.createElement('a');
    let url = URL.createObjectURL(blob);
    link.href = url;
    link.setAttribute('download', 'non_delivered_report_{{ date('Y-m-d') }}.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}
</script>

<style>
@media print {
    button, .btn-print, .flex.justify-end {
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
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 8px;
    }
}
</style>
@endsection