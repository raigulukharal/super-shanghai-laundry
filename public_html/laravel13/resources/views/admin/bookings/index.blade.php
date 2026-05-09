@extends('layouts.admin')

@section('title', 'Bookings')
@section('subtitle', 'Manage all customer bookings')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Bookings Management</h2>
            <p class="text-gray-500 text-sm mt-1">View and manage all customer bookings</p>
        </div>
        <a href="{{ route('admin.bookings.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
            <i class="ri-add-line"></i>
            New Booking
        </a>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" action="{{ route('admin.bookings.index') }}" class="flex flex-wrap gap-3">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" placeholder="Search by Invoice #, Customer Name, Mobile..." 
                       value="{{ request('search') }}" 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <select name="status" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="partial_delivered" {{ request('status') == 'partial_delivered' ? 'selected' : '' }}>Partial Delivered</option>
                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="ri-search-line"></i> Search
                </button>
            </div>
            <div>
                <a href="{{ route('admin.bookings.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">
                    <i class="ri-refresh-line"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Bookings Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Invoice #</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Mobile</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Items</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Amount</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Paid</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Due</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($bookings as $booking)
                    @php
                        $totalItems = $booking->items->sum('quantity');
                        $due = $booking->grand_total - $booking->paid_amount;
                        $statusClass = '';
                        if($booking->status == 'pending') $statusClass = 'bg-yellow-100 text-yellow-800';
                        elseif($booking->status == 'delivered') $statusClass = 'bg-green-100 text-green-800';
                        elseif($booking->status == 'partial_delivered') $statusClass = 'bg-blue-100 text-blue-800';
                        elseif($booking->status == 'cancelled') $statusClass = 'bg-red-100 text-red-800';
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-mono font-bold text-blue-600">{{ $booking->invoice_no }}</td>
                        <td class="px-4 py-3">{{ $booking->customer->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3">{{ $booking->customer->mobile ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-center">{{ $totalItems }}</td>
                        <td class="px-4 py-3 text-right">Rs. {{ number_format($booking->grand_total, 2) }}</td>
                        <td class="px-4 py-3 text-right text-green-600">Rs. {{ number_format($booking->paid_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right {{ $due > 0 ? 'text-red-600 font-bold' : 'text-green-600' }}">
                            Rs. {{ number_format($due, 2) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 text-xs rounded-full {{ $statusClass }}">
                                {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $booking->booking_date->format('d-m-Y') }}</td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex gap-2 justify-center">
                                <a href="{{ route('admin.bookings.show', $booking->id) }}" class="text-blue-600 hover:text-blue-800" title="View">
                                    <i class="ri-eye-line text-lg"></i>
                                </a>
                                <a href="{{ route('admin.bookings.edit', $booking->id) }}" class="text-green-600 hover:text-green-800" title="Edit">
                                    <i class="ri-edit-line text-lg"></i>
                                </a>
                                <button onclick="deleteBooking({{ $booking->id }}, '{{ $booking->invoice_no }}')" class="text-red-600 hover:text-red-800" title="Delete">
                                    <i class="ri-delete-bin-line text-lg"></i>
                                </button>
                                @if($booking->status != 'delivered')
                                <a href="{{ route('admin.deliveries.create', $booking->id) }}" class="text-orange-600 hover:text-orange-800" title="Deliver">
                                    <i class="ri-truck-line text-lg"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-4 py-8 text-center text-gray-500">
                            <i class="ri-inbox-line text-4xl"></i>
                            <p class="mt-2">No bookings found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t">
            {{ $bookings->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<script>
function deleteBooking(id, invoiceNo) {
    if(confirm(`Delete booking ${invoiceNo}?\n\n⚠️ This will delete:\n- All items\n- All payments\n- All delivery records\n\nThis action cannot be undone!`)) {
        $.ajax({
            url: `/admin/bookings/${id}`,
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(res) {
                if(res.success) {
                    alert('✅ Booking deleted successfully');
                    location.reload();
                } else {
                    alert('❌ Error: ' + res.message);
                }
            },
            error: function(xhr) {
                alert('❌ Error deleting booking');
            }
        });
    }
}
</script>
@endsection