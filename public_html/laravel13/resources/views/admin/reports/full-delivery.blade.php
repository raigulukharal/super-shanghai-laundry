@extends('layouts.admin')

@section('title', 'Full Delivery Report')
@section('subtitle', 'All completed deliveries')

@section('content')
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
        <h3 class="text-lg font-semibold">✅ Full Delivery Report</h3>
        <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">🖨️ Print</button>
    </div>
    
    <div class="p-6">
        <div class="mb-4"><strong>Total Deliveries:</strong> {{ $deliveries->count() }} | <strong>Total Items:</strong> {{ $totalItems }}</div>
        
        <div class="overflow-x-auto">
            <table class="w-full border">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2">Delivery ID</th>
                        <th class="px-4 py-2">Invoice #</th>
                        <th class="px-4 py-2">Customer</th>
                        <th class="px-4 py-2">Receiver</th>
                        <th class="px-4 py-2">Items</th>
                        <th class="px-4 py-2">Delivery Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deliveries as $delivery)
                    <tr class="border-t">
                        <td class="px-4 py-2">#{{ $delivery->id }}</td>
                        <td class="px-4 py-2">{{ $delivery->booking->invoice_no ?? 'N/A' }}</td>
                        <td class="px-4 py-2">{{ $delivery->booking->customer->name ?? 'N/A' }}</td>
                        <td class="px-4 py-2">{{ $delivery->receiver_name }}</td>
                        <td class="px-4 py-2">{{ $delivery->items->count() }}</td>
                        <td class="px-4 py-2">{{ \Carbon\Carbon::parse($delivery->delivery_date)->format('d-m-Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center">No full deliveries found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection