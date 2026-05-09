@extends('layouts.admin')

@section('title', 'Urgent Orders Report')
@section('subtitle', 'All urgent delivery orders')

@section('content')
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
        <h3 class="text-lg font-semibold">⚠️ Urgent Orders Report</h3>
        <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">🖨️ Print</button>
    </div>
    
    <div class="p-6">
        <div class="mb-4"><strong>Total Urgent Orders:</strong> {{ $items->count() }}</div>
        
        <div class="overflow-x-auto">
            <table class="w-full border">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2">Invoice #</th>
                        <th class="px-4 py-2">Customer</th>
                        <th class="px-4 py-2">Item</th>
                        <th class="px-4 py-2">Color</th>
                        <th class="px-4 py-2">Quantity</th>
                        <th class="px-4 py-2">Expected Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $item->booking->invoice_no ?? 'N/A' }}</td>
                        <td class="px-4 py-2">{{ $item->booking->customer->name ?? 'N/A' }}</td>
                        <td class="px-4 py-2">{{ $item->clothType->name ?? 'N/A' }}</td>
                        <td class="px-4 py-2">{{ $item->color->name ?? 'N/A' }}</td>
                        <td class="px-4 py-2 text-center">{{ $item->quantity - ($item->delivered_quantity ?? 0) }} / {{ $item->quantity }}</td>
                        <td class="px-4 py-2">{{ \Carbon\Carbon::parse($item->expected_delivery_date)->format('d-m-Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center">No urgent orders found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection