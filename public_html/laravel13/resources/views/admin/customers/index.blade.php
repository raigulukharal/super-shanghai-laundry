@extends('layouts.admin')

@section('title', 'Customers')
@section('subtitle', 'Manage all customers - Live search')

@section('content')
<div>
    <!-- Search Bar - Live Search -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <div class="flex gap-3">
            <div class="flex-1 relative">
                <i class="ri-search-line absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="liveSearchInput" placeholder="🔍 Search by name, mobile number or customer code..." 
                       class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <div id="searchLoader" class="absolute right-3 top-1/2 transform -translate-y-1/2 hidden">
                    <i class="ri-loader-4-line animate-spin text-blue-500"></i>
                </div>
            </div>
            <button id="resetSearchBtn" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition">
                Reset
            </button>
            <a href="{{ route('admin.customers.create') }}" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                + New Customer
            </a>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Mobile</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Customer Codes</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Area</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Bookings</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">Total Spent</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">Due</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody id="customers-table-body">
                    @include('admin.customers.partials.table', ['customers' => $customers])
                </tbody>
            </table>
        </div>
        
        <div class="px-4 py-3 border-t" id="pagination-links">
            {{ $customers->links() }}
        </div>
    </div>
</div>

<!-- Customer Details Modal -->
<div id="customerModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4" style="z-index: 9999;">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-5xl max-h-[90vh] overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex justify-between items-center sticky top-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="ri-user-line text-white text-xl"></i>
                </div>
                <div>
                    <h3 class="text-white text-xl font-bold">Customer Details</h3>
                    <p class="text-blue-100 text-sm" id="modalCustomerName">Loading...</p>
                </div>
            </div>
            <button onclick="closeCustomerModal()" class="text-white hover:text-gray-200 transition">
                <i class="ri-close-line text-2xl"></i>
            </button>
        </div>
        
        <div id="customerModalContent" class="overflow-y-auto p-6" style="max-height: calc(90vh - 70px);">
            <div class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="mt-2 text-gray-500">Loading customer details...</p>
            </div>
        </div>
    </div>
</div>

<script>
let searchTimeout;
let currentCustomerId = null;
let csrfToken = '{{ csrf_token() }}';

$(document).ready(function() {
    $('#liveSearchInput').on('keyup', function() {
        let searchTerm = $(this).val().trim();
        clearTimeout(searchTimeout);
        if (searchTerm.length >= 1) {
            $('#searchLoader').removeClass('hidden');
            searchTimeout = setTimeout(function() { performLiveSearch(searchTerm); }, 500);
        } else if (searchTerm.length === 0) {
            resetToOriginal();
        }
    });
    
    function performLiveSearch(term) {
        $.ajax({
            url: '{{ route("admin.customers.search") }}',
            method: 'GET',
            data: { term: term },
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(response) {
                $('#searchLoader').addClass('hidden');
                if (response.results && response.results.length > 0) {
                    let html = '';
                    response.results.forEach(function(customer) {
                        let dueAmount = (customer.total_amount - customer.total_paid).toFixed(2);
                        let codesHtml = '';
                        if (customer.code_array && customer.code_array.length > 0) {
                            customer.code_array.forEach(function(code) {
                                codesHtml += `<span class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded-full mr-1">${escapeHtml(code)}</span>`;
                            });
                        } else {
                            codesHtml = '-';
                        }
                        html += `
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="px-4 py-3">${customer.id}</td>
                                <td class="px-4 py-3 font-medium">${escapeHtml(customer.name)}</td>
                                <td class="px-4 py-3">${escapeHtml(customer.mobile)}</td>
                                <td class="px-4 py-3">${codesHtml}</td>
                                <td class="px-4 py-3">${escapeHtml(customer.area || '-')}</td>
                                <td class="px-4 py-3 text-center"><span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">${customer.bookings_count || 0}</span></td>
                                <td class="px-4 py-3 text-right">Rs. ${parseFloat(customer.total_amount || 0).toFixed(2)}</td>
                                <td class="px-4 py-3 text-right ${dueAmount > 0 ? 'text-red-600 font-semibold' : 'text-green-600'}">Rs. ${dueAmount}</td>
                                <td class="px-4 py-3 text-center">
                                    <button onclick="viewCustomer(${customer.id})" class="text-blue-600 hover:text-blue-800 mx-1" title="View Details">
                                        <i class="ri-eye-line text-xl"></i>
                                    </button>
                                    <a href="/admin/customers/${customer.id}/edit" class="text-green-600 hover:text-green-800 mx-1" title="Edit">
                                        <i class="ri-edit-line text-xl"></i>
                                    </a>
                                 </td>
                              </tr>
                        `;
                    });
                    $('#customers-table-body').html(html);
                    $('#pagination-links').hide();
                } else {
                    $('#customers-table-body').html(`<tr class="border-b"><td colspan="9" class="px-4 py-8 text-center text-gray-500"><i class="ri-search-line text-4xl mb-2 block"></i>No customers found matching "<strong>${escapeHtml(term)}</strong>"</td></tr>`);
                    $('#pagination-links').hide();
                }
            },
            error: function() { $('#searchLoader').addClass('hidden'); }
        });
    }
    
    function resetToOriginal() { location.reload(); }
    $('#resetSearchBtn').click(function() { $('#liveSearchInput').val(''); resetToOriginal(); });
});

function viewCustomer(id) {
    currentCustomerId = id;
    const modal = document.getElementById('customerModal');
    const modalContent = document.getElementById('customerModalContent');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    modalContent.innerHTML = '<div class="text-center py-8"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div><p class="mt-2 text-gray-500">Loading customer details...</p></div>';
    
    fetch('/admin/customers/' + id, {
        method: 'GET',
        headers: { 
            'Accept': 'application/json', 
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.customer) {
            displayCustomerDetails(data);
        } else {
            modalContent.innerHTML = '<div class="text-center py-8 text-red-500">Failed to load customer details: ' + (data.message || 'Unknown error') + '</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        modalContent.innerHTML = '<div class="text-center py-8 text-red-500">Error loading customer details: ' + error.message + '</div>';
    });
}

function displayCustomerDetails(data) {
    const customer = data.customer;
    const stats = data.stats;
    const bookings = data.bookings;
    
    document.getElementById('modalCustomerName').innerHTML = customer.name;
    
    let codesHtml = '';
    if (customer.codes && customer.codes.length > 0) {
        customer.codes.forEach(function(code) {
            codesHtml += `<span class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded-full mr-1">${escapeHtml(code.code)}</span>`;
        });
    } else {
        codesHtml = 'N/A';
    }
    
    let bookingsHtml = '';
    if (bookings && bookings.length > 0) {
        bookings.forEach(function(booking) {
            let dueAmount = booking.grand_total - booking.paid_amount;
            let bookingDate = new Date(booking.booking_date).toLocaleDateString();
            
            let statusClass = '';
            if (booking.status === 'delivered') statusClass = 'bg-green-100 text-green-800';
            else if (booking.status === 'cancelled') statusClass = 'bg-red-100 text-red-800';
            else if (booking.status === 'partial_delivered') statusClass = 'bg-yellow-100 text-yellow-800';
            else statusClass = 'bg-blue-100 text-blue-800';
            
            let paymentClass = '';
            if (booking.payment_status === 'full_pay') paymentClass = 'bg-green-100 text-green-800';
            else if (booking.payment_status === 'partial_pay') paymentClass = 'bg-yellow-100 text-yellow-800';
            else paymentClass = 'bg-red-100 text-red-800';
            
            bookingsHtml += `
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono font-bold">${escapeHtml(booking.invoice_no)}</td>
                    <td class="px-4 py-3">${bookingDate}</td>
                    <td class="px-4 py-3 text-right">Rs. ${booking.grand_total.toFixed(2)}</td>
                    <td class="px-4 py-3 text-right">Rs. ${booking.paid_amount.toFixed(2)}</td>
                    <td class="px-4 py-3 text-right ${dueAmount > 0 ? 'text-red-600 font-bold' : 'text-green-600'}">Rs. ${dueAmount.toFixed(2)}</td>
                    <td class="px-4 py-3"><span class="px-2 py-1 text-xs rounded-full ${statusClass}">${booking.status_display}</span></td>
                    <td class="px-4 py-3"><span class="px-2 py-1 text-xs rounded-full ${paymentClass}">${booking.payment_status_display}</span></td>
                    <td class="px-4 py-3 text-sm">${booking.delivered_items}/${booking.total_items} items</td>
                 </tr>
            `;
        });
    } else {
        bookingsHtml = '<tr><td colspan="8" class="text-center py-8 text-gray-500">No bookings found</td></tr>';
    }
    
    const html = `
        <div class="space-y-6">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-5">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div><p class="text-xs text-gray-500">Name</p><p class="font-semibold">${escapeHtml(customer.name)}</p></div>
                    <div><p class="text-xs text-gray-500">Mobile</p><p class="font-semibold">${escapeHtml(customer.mobile)}</p></div>
                    <div><p class="text-xs text-gray-500">Area</p><p class="font-semibold">${escapeHtml(customer.area || 'N/A')}</p></div>
                    <div class="col-span-2"><p class="text-xs text-gray-500">Customer Codes</p><p class="font-semibold">${codesHtml}</p></div>
                </div>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 rounded-xl p-4 text-center"><p class="text-xs text-gray-500">Total Bookings</p><p class="text-2xl font-bold text-blue-600">${stats.total_bookings}</p></div>
                <div class="bg-green-50 rounded-xl p-4 text-center"><p class="text-xs text-gray-500">Total Spent</p><p class="text-2xl font-bold text-green-600">Rs. ${stats.total_amount.toFixed(2)}</p></div>
                <div class="bg-yellow-50 rounded-xl p-4 text-center"><p class="text-xs text-gray-500">Total Paid</p><p class="text-2xl font-bold">Rs. ${stats.total_paid.toFixed(2)}</p></div>
                <div class="bg-red-50 rounded-xl p-4 text-center"><p class="text-xs text-gray-500">Outstanding</p><p class="text-2xl font-bold text-red-600">Rs. ${stats.outstanding.toFixed(2)}</p></div>
            </div>
            
            <div>
                <h4 class="font-semibold text-gray-800 mb-3">Booking History</h4>
                <div class="overflow-x-auto">
                    <table class="w-full border border-gray-200 rounded-lg">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold">Invoice</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold">Date</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold">Amount</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold">Paid</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold">Due</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold">Payment</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold">Delivery</th>
                             </tr>
                        </thead>
                        <tbody>
                            ${bookingsHtml}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
    document.getElementById('customerModalContent').innerHTML = html;
}

function closeCustomerModal() {
    document.getElementById('customerModal').classList.add('hidden');
    document.getElementById('customerModal').classList.remove('flex');
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

<style>
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .animate-spin {
        animation: spin 1s linear infinite;
    }
</style>
@endsection