@extends('layouts.admin')

@section('title', 'Deliveries')
@section('subtitle', 'All delivery records - Search by invoice, customer or receiver')

@section('content')
<div>
    <!-- Search Bar -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <div class="flex gap-3">
            <div class="flex-1 relative">
                <i class="ri-search-line absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="searchInput" placeholder="🔍 Search by invoice number, customer name, mobile, customer code or receiver name..." 
                       class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <div id="searchLoader" class="absolute right-3 top-1/2 transform -translate-y-1/2 hidden">
                    <i class="ri-loader-4-line animate-spin text-blue-500"></i>
                </div>
            </div>
            <button id="resetSearchBtn" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition">
                Reset
            </button>
            <a href="{{ route('admin.deliveries.create') }}" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                + New Delivery
            </a>
        </div>
        <p class="text-xs text-gray-500 mt-2">
            <i class="ri-information-line"></i> Search by invoice number, customer name, mobile number, customer code or receiver name
        </p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Deliveries</p>
                    <p class="text-2xl font-bold">{{ $stats['total'] ?? 0 }}</p>
                </div>
                <i class="ri-truck-line text-3xl text-blue-500"></i>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Today's Deliveries</p>
                    <p class="text-2xl font-bold">{{ $stats['today'] ?? 0 }}</p>
                </div>
                <i class="ri-calendar-todo-line text-3xl text-green-500"></i>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Pending Items</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending_items'] ?? 0 }}</p>
                </div>
                <i class="ri-time-line text-3xl text-yellow-500"></i>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Partial Items</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $stats['partial_items'] ?? 0 }}</p>
                </div>
                <i class="ri-play-list-line text-3xl text-orange-500"></i>
            </div>
        </div>
    </div>

    <!-- Deliveries Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
            <h3 class="text-lg font-semibold">All Deliveries</h3>
            <a href="{{ route('admin.deliveries.create') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm">+ New Delivery</a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Invoice</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Customer Code</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Receiver</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Items</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Delivery Date</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody id="deliveries-table-body">
                    @include('admin.deliveries.partials.table', ['deliveries' => $deliveries])
                </tbody>
            </table>
        </div>
        
        <div class="px-4 py-3 border-t" id="pagination-links">
            {{ $deliveries->links() }}
        </div>
    </div>
</div>

<style>
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .animate-spin {
        animation: spin 1s linear infinite;
    }
</style>

@push('scripts')
<script>
let searchTimeout;

$(document).ready(function() {
    $('#searchInput').on('keyup', function() {
        let searchTerm = $(this).val().trim();
        clearTimeout(searchTimeout);
        
        if (searchTerm.length >= 1) {
            $('#searchLoader').removeClass('hidden');
            searchTimeout = setTimeout(function() { performSearch(searchTerm); }, 500);
        } else if (searchTerm.length === 0) {
            resetToOriginal();
        }
    });
    
    $('#resetSearchBtn').click(function() {
        $('#searchInput').val('');
        resetToOriginal();
    });
});

function performSearch(term) {
    $.ajax({
        url: '{{ route("admin.deliveries.search") }}',
        method: 'GET',
        data: { term: term },
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function(response) {
            $('#searchLoader').addClass('hidden');
            
            if (response.results && response.results.length > 0) {
                let html = '';
                response.results.forEach(function(delivery) {
                    html += `
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">#${delivery.id}</td>
                            <td class="px-4 py-3 font-mono font-bold">${escapeHtml(delivery.invoice_no)}</td>
                            <td class="px-4 py-3">${escapeHtml(delivery.customer_name)}</td>
                            <td class="px-4 py-3"><span class="bg-gray-100 px-2 py-1 rounded text-xs">${escapeHtml(delivery.customer_code)}</span></td>
                            <td class="px-4 py-3">${escapeHtml(delivery.receiver_name)}</td>
                            <td class="px-4 py-3 text-center"><span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">${delivery.items_count} items</span></td>
                            <td class="px-4 py-3">${delivery.delivery_date}</td>
                            <td class="px-4 py-3 text-center">
                                <a href="/admin/deliveries/${delivery.id}" class="text-blue-600 hover:text-blue-800">
                                    <i class="ri-eye-line text-xl"></i>
                                </a>
                            </td>
                         </tr>
                    `;
                });
                $('#deliveries-table-body').html(html);
                $('#pagination-links').hide();
            } else {
                $('#deliveries-table-body').html(`
                    <tr class="border-b">
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                            <i class="ri-search-line text-4xl mb-2 block"></i>
                            No deliveries found matching "<strong>${escapeHtml(term)}</strong>"
                        </td>
                    </tr>
                `);
                $('#pagination-links').hide();
            }
        },
        error: function(xhr) {
            $('#searchLoader').addClass('hidden');
            console.error('Search error:', xhr);
            $('#deliveries-table-body').html(`
                <tr class="border-b">
                    <td colspan="8" class="px-4 py-8 text-center text-red-500">
                        <i class="ri-error-warning-line text-4xl mb-2 block"></i>
                        Error searching deliveries. Please try again.
                    </td>
                </tr>
            `);
        }
    });
}

function resetToOriginal() {
    location.reload();
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}
</script>
@endpush
@endsection