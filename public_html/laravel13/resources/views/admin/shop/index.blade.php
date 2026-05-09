@extends('layouts.admin')

@section('title', 'At Shop')
@section('subtitle', 'Manage invoices, deliveries, and shop operations')

@section('content')
<style>
    .status-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 500;
    }
    .status-in-shop { background: #dbeafe; color: #1e40af; }
    .status-delivered { background: #d1fae5; color: #065f46; }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-partial { background: #fed7aa; color: #ea580c; }
    .type-regular { background: #e0e7ff; color: #3730a3; }
    .type-extra { background: #fed7aa; color: #9a3412; }
    .missing { background: #fee2e2; color: #991b1b; }
    .action-btn {
        transition: all 0.2s ease;
    }
    .action-btn:hover {
        transform: scale(1.1);
    }
    .table-container {
        overflow-x: auto;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        background: white;
    }
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .data-table th {
        background: #f8fafc;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #1e293b;
        border-bottom: 1px solid #e2e8f0;
    }
    .data-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
    }
    .data-table tr:hover {
        background: #f8fafc;
    }
    .modal {
        transition: all 0.3s ease;
    }
    .select2-container .select2-selection--single {
        height: 42px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 5px;
    }
</style>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-7 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
            <p class="text-gray-500 text-xs">Total Ranges</p>
            <p class="text-2xl font-bold">{{ $stats['total_ranges'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-gray-500">
            <p class="text-gray-500 text-xs">Total Invoices</p>
            <p class="text-2xl font-bold">{{ $stats['total_invoices'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
            <p class="text-gray-500 text-xs">In Shop</p>
            <p class="text-2xl font-bold text-blue-600">{{ $stats['in_shop'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500">
            <p class="text-gray-500 text-xs">Delivered</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['delivered'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-yellow-500">
            <p class="text-gray-500 text-xs">Pending</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-red-500">
            <p class="text-gray-500 text-xs">Missing</p>
            <p class="text-2xl font-bold text-red-600">{{ $stats['missing'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-orange-500">
            <p class="text-gray-500 text-xs">Extra</p>
            <p class="text-2xl font-bold text-orange-600">{{ $stats['extra'] }}</p>
        </div>
    </div>

    <!-- Generate Range Form -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold mb-4">📋 Generate Invoice Range</h3>
        <form id="generate-range-form" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Invoice</label>
                <select name="start_invoice" id="start_invoice" class="w-full border rounded-lg px-3 py-2" required>
                    <option value="">Select Start Invoice</option>
                    @foreach($bookings as $booking)
                    <option value="{{ $booking->invoice_no }}">{{ $booking->invoice_no }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">End Invoice</label>
                <select name="end_invoice" id="end_invoice" class="w-full border rounded-lg px-3 py-2" required>
                    <option value="">Select End Invoice</option>
                    @foreach($bookings as $booking)
                    <option value="{{ $booking->invoice_no }}">{{ $booking->invoice_no }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                <input type="date" name="range_date" class="w-full border rounded-lg px-3 py-2" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="ri-add-line"></i> Generate Range
                </button>
            </div>
            <div class="md:col-span-4">
                <textarea name="description" class="w-full border rounded-lg px-3 py-2" rows="2" placeholder="Description (optional)"></textarea>
            </div>
        </form>
    </div>

    <!-- Add Extra Invoice -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold mb-4">➕ Add Extra Invoice</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <select id="range_id" class="w-full border rounded-lg px-3 py-2">
                    <option value="">Select Range</option>
                    @foreach($ranges as $range)
                    <option value="{{ $range->id }}">{{ $range->range_name }} ({{ $range->range_date }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <input type="text" id="extra_invoice_no" class="w-full border rounded-lg px-3 py-2" placeholder="Invoice Number (e.g., 1-00150)">
            </div>
            <div>
                <button id="add-extra-btn" class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition">
                    <i class="ri-add-circle-line"></i> Add Extra Invoice
                </button>
            </div>
        </div>
    </div>

    <!-- Search Section -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold mb-4">🔍 Search Invoices</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <input type="text" id="search_invoice" class="w-full border rounded-lg px-3 py-2" placeholder="Invoice Number">
            <select id="search_range" class="w-full border rounded-lg px-3 py-2">
                <option value="">All Ranges</option>
                @foreach($ranges as $range)
                <option value="{{ $range->id }}">{{ $range->range_name }}</option>
                @endforeach
            </select>
            <input type="date" id="search_date" class="w-full border rounded-lg px-3 py-2">
            <select id="search_status" class="w-full border rounded-lg px-3 py-2">
                <option value="all">All Status</option>
                <option value="in_shop">In Shop</option>
                <option value="delivered">Delivered</option>
                <option value="pending">Pending</option>
                <option value="partial_delivered">Partial Delivered</option>
            </select>
            <button id="search-btn" class="bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 transition flex items-center justify-center gap-2">
                <i class="ri-search-line"></i> Search
            </button>
        </div>
        <div id="search-results" class="mt-4"></div>
    </div>

    <!-- Ranges List -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h3 class="text-lg font-semibold">📊 Invoice Ranges</h3>
        </div>
        <div class="divide-y">
            @foreach($ranges as $range)
            <div class="p-4 hover:bg-gray-50">
                <div class="flex justify-between items-center">
                    <div class="flex-1 cursor-pointer" onclick="toggleRange({{ $range->id }})">
                        <p class="font-semibold">{{ $range->range_name }}</p>
                        <p class="text-sm text-gray-500">{{ $range->range_date }} | {{ $range->description ?? 'No description' }}</p>
                    </div>
                    <div class="flex gap-2 items-center">
                        <span class="status-badge bg-blue-100 text-blue-700">{{ $range->shopInvoices->count() }} Total</span>
                        <span class="status-badge bg-red-100 text-red-700">{{ $range->shopInvoices->where('is_missing', true)->count() }} Missing</span>
                        <span class="status-badge bg-orange-100 text-orange-700">{{ $range->shopInvoices->where('type', 'extra')->count() }} Extra</span>
                        <button onclick="deleteRange({{ $range->id }}, '{{ addslashes($range->range_name) }}')" 
                                class="bg-red-500 text-white px-3 py-1 rounded-lg text-sm hover:bg-red-600 transition flex items-center gap-1">
                            <i class="ri-delete-bin-line"></i> Delete
                        </button>
                        <i class="ri-arrow-down-s-line text-xl cursor-pointer" onclick="toggleRange({{ $range->id }})"></i>
                    </div>
                </div>
                <div id="range-{{ $range->id }}" class="hidden mt-3">
                    <div class="loading text-center py-4">Loading...</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Full Delivery Modal -->
<div id="fullDeliveryModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4 modal">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
        <div class="bg-gradient-to-r from-green-600 to-green-700 px-5 py-3 flex justify-between items-center rounded-t-xl">
            <h3 class="text-white font-semibold">Full Delivery</h3>
            <button onclick="closeFullDeliveryModal()" class="text-white text-2xl">&times;</button>
        </div>
        <div class="p-5">
            <p class="text-sm text-gray-600 mb-3">Invoice: <strong id="fullDeliveryInvoiceNo"></strong></p>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Receiver Name <span class="text-gray-400 text-xs">(Optional)</span></label>
                <input type="text" id="full_receiver_name" class="w-full border rounded-lg px-3 py-2" placeholder="Leave empty if same as customer">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Receiver Mobile <span class="text-gray-400 text-xs">(Optional)</span></label>
                <input type="text" id="full_receiver_mobile" class="w-full border rounded-lg px-3 py-2" placeholder="Leave empty if same as customer">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea id="full_notes" class="w-full border rounded-lg px-3 py-2" rows="2"></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button onclick="closeFullDeliveryModal()" class="px-4 py-2 border rounded-lg">Cancel</button>
                <button onclick="confirmFullDelivery()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Confirm Delivery</button>
            </div>
        </div>
    </div>
</div>

<!-- Partial Delivery Modal -->
<div id="partialDeliveryModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4 modal" style="z-index: 10000;">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-5 py-3 flex justify-between items-center rounded-t-xl sticky top-0">
            <h3 class="text-white font-semibold">Partial Delivery</h3>
            <button onclick="closePartialDeliveryModal()" class="text-white text-2xl">&times;</button>
        </div>
        <div class="p-5">
            <p class="text-sm text-gray-600 mb-3">Invoice: <strong id="partialDeliveryInvoiceNo"></strong></p>
            <div id="partialItemsList" class="space-y-3 mb-4 max-h-96 overflow-y-auto"></div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Receiver Name <span class="text-gray-400 text-xs">(Optional)</span></label>
                <input type="text" id="partial_receiver_name" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Receiver Mobile <span class="text-gray-400 text-xs">(Optional)</span></label>
                <input type="text" id="partial_receiver_mobile" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Amount</label>
                <input type="number" id="partial_payment_amount" class="w-full border rounded-lg px-3 py-2" value="0">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                <select id="partial_payment_method" class="w-full border rounded-lg px-3 py-2">
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                    <option value="bank_transfer">Bank Transfer</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea id="partial_notes" class="w-full border rounded-lg px-3 py-2" rows="2"></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button onclick="closePartialDeliveryModal()" class="px-4 py-2 border rounded-lg">Cancel</button>
                <button onclick="confirmPartialDelivery()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Process</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentBookingId = null;
let currentInvoiceNo = null;

$(document).ready(function() {
    $('#start_invoice, #end_invoice, #range_id, #search_range').select2({ width: '100%' });
    
    $('#generate-range-form').submit(function(e) {
        e.preventDefault();
        let btn = $(this).find('button[type="submit"]');
        let original = btn.html();
        btn.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin"></i> Generating...');
        $.ajax({
            url: '{{ route("admin.shop.generate-range") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                alert(res.success ? '✅ ' + res.message : '❌ ' + res.message);
                if(res.success) location.reload();
                btn.prop('disabled', false).html(original);
            },
            error: function() { alert('❌ Error'); btn.prop('disabled', false).html(original); }
        });
    });
    
    $('#add-extra-btn').click(function() {
        let rangeId = $('#range_id').val();
        let invoiceNo = $('#extra_invoice_no').val().trim();
        if(!rangeId) { alert('Select range'); return; }
        if(!invoiceNo) { alert('Enter invoice number'); return; }
        let btn = $(this);
        let original = btn.html();
        btn.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin"></i> Adding...');
        $.ajax({
            url: '{{ route("admin.shop.add-extra") }}',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', range_id: rangeId, invoice_no: invoiceNo },
            success: function(res) {
                alert(res.success ? '✅ ' + res.message : '❌ ' + res.message);
                if(res.success) location.reload();
                btn.prop('disabled', false).html(original);
            },
            error: function() { alert('❌ Error'); btn.prop('disabled', false).html(original); }
        });
    });
    
    $('#search-btn').click(function() {
        let invoiceNo = $('#search_invoice').val();
        let rangeId = $('#search_range').val();
        let date = $('#search_date').val();
        let status = $('#search_status').val();
        
        $.ajax({
            url: '{{ route("admin.shop.search") }}',
            method: 'GET',
            data: { invoice_no: invoiceNo, range_id: rangeId, date: date, status: status, ajax: 1 },
            success: function(res) {
                if(res.success && res.invoices) {
                    displaySearchResults(res.invoices);
                } else {
                    $('#search-results').html('<div class="text-center py-8 text-gray-500">No results found</div>');
                }
            },
            error: function() { alert('Error searching'); }
        });
    });
});

function displaySearchResults(invoices) {
    if(!invoices || invoices.length === 0) {
        $('#search-results').html('<div class="text-center py-8 text-gray-500">No invoices found</div>');
        return;
    }
    
    let html = '<div class="table-container"><table class="data-table"><thead>';
    html += '<th>Invoice #</th><th>Customer Name</th><th>Customer Code</th><th>Mobile</th>';
    html += '<th>Date</th><th class="text-right">Amount</th><th class="text-right">Paid</th><th class="text-right">Due</th>';
    html += '<th>Status</th><th>Delivery</th><th class="text-center">Actions</th></thead><tbody>';
    
    for(let i=0; i<invoices.length; i++) {
        let inv = invoices[i];
        let statusClass = '';
        let statusText = '';
        if(inv.status === 'pending') { statusClass = 'bg-yellow-100 text-yellow-800'; statusText = 'Pending'; }
        else if(inv.status === 'delivered') { statusClass = 'bg-green-100 text-green-800'; statusText = 'Delivered'; }
        else if(inv.status === 'partial_delivered') { statusClass = 'bg-orange-100 text-orange-800'; statusText = 'Partial'; }
        else { statusClass = 'bg-gray-100 text-gray-800'; statusText = inv.status || 'N/A'; }
        
        let dueClass = inv.due_amount > 0 ? 'text-red-600 font-bold' : 'text-green-600';
        let deliveryText = inv.delivered_items + '/' + inv.total_items + ' items';
        let canPartial = (inv.remaining_items > 0 || inv.due_amount > 0);
        
        html += '<tr>';
        html += '<td class="font-mono font-bold">' + escapeHtml(inv.invoice_no) + '</td>';
        html += '<td>' + escapeHtml(inv.customer_name) + '</td>';
        html += '<td><span class="bg-gray-100 px-2 py-1 rounded text-xs">' + escapeHtml(inv.customer_code) + '</span></td>';
        html += '<td>' + escapeHtml(inv.customer_mobile) + '</td>';
        html += '<td>' + inv.booking_date + '</td>';
        html += '<td class="text-right">Rs. ' + inv.grand_total.toFixed(2) + '</td>';
        html += '<td class="text-right">Rs. ' + inv.paid_amount.toFixed(2) + '</td>';
        html += '<td class="text-right ' + dueClass + '">Rs. ' + inv.due_amount.toFixed(2) + '</td>';
        html += '<td><span class="status-badge ' + statusClass + '">' + statusText + '</span></td>';
        html += '<td>' + deliveryText + '</td>';
        html += '<td class="text-center"><div class="flex gap-2 justify-center">';
        
        if(inv.booking_id) {
            html += '<a href="/admin/bookings/' + inv.booking_id + '/download-invoice" target="_blank" class="text-purple-600 hover:text-purple-800" title="Download"><i class="ri-download-line text-xl"></i></a>';
        }
        
        if(inv.status !== 'delivered' && inv.booking_id) {
            html += '<button onclick="openFullDeliveryModal(' + inv.booking_id + ', \'' + inv.invoice_no + '\')" class="text-green-600 hover:text-green-800" title="Full Delivery"><i class="ri-checkbox-circle-line text-xl"></i></button>';
            if(canPartial) {
                html += '<button onclick="openPartialDeliveryModal(' + inv.booking_id + ', \'' + inv.invoice_no + '\')" class="text-blue-600 hover:text-blue-800" title="Partial Delivery"><i class="ri-truck-line text-xl"></i></button>';
            }
        }
        
        if((inv.status === 'delivered' || inv.status === 'partial_delivered') && inv.booking_id) {
            html += '<button onclick="undeliverInvoice(' + inv.booking_id + ', \'' + inv.invoice_no + '\')" class="text-orange-600 hover:text-orange-800" title="Undeliver"><i class="ri-arrow-go-back-line text-xl"></i></button>';
        }
        
        html += '</div></div></tr>';
    }
    
    html += '</tbody></table></div>';
    $('#search-results').html(html);
}

function toggleRange(id) {
    let container = $('#range-' + id);
    if(container.hasClass('hidden')) {
        container.removeClass('hidden');
        if(container.find('.loading').length) {
            $.get('{{ url("admin/shop/range") }}/' + id, function(res) {
                if(res.success) displayRangeInvoices(res);
            });
        }
    } else {
        container.addClass('hidden');
    }
}

function displayRangeInvoices(data) {
    let container = $('#range-' + data.range.id);
    let stats = data.stats;
    let invoices = data.range.shop_invoices;
    let html = '<div class="bg-gray-50 p-3 rounded-lg mb-3"><div class="grid grid-cols-2 md:grid-cols-7 gap-2 text-sm">';
    html += '<div><strong>Total:</strong> ' + stats.total + '</div><div><strong>Regular:</strong> ' + stats.regular + '</div>';
    html += '<div><strong>Extra:</strong> ' + stats.extra + '</div><div><strong>Missing:</strong> ' + stats.missing + '</div>';
    html += '<div><strong>In Shop:</strong> ' + stats.in_shop + '</div><div><strong>Delivered:</strong> ' + stats.delivered + '</div>';
    html += '<div><strong>Pending:</strong> ' + stats.pending + '</div></div></div>';
    html += '<div class="grid grid-cols-2 md:grid-cols-4 gap-2">';
    for(let i=0; i<invoices.length; i++) {
        let inv = invoices[i];
        let statusClass = inv.status === 'in_shop' ? 'bg-blue-100 text-blue-700' : (inv.status === 'delivered' ? 'bg-green-100 text-green-700' : (inv.status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700'));
        html += '<div class="border rounded-lg p-2 flex justify-between items-center"><span class="font-mono text-sm">' + inv.invoice_no + '</span>';
        html += '<div class="flex gap-1"><span class="status-badge ' + statusClass + ' text-xs px-2">' + (inv.status === 'in_shop' ? 'In Shop' : ucfirst(inv.status)) + '</span>';
        html += '<button onclick="removeInvoice(' + inv.id + ')" class="text-red-500"><i class="ri-delete-bin-line"></i></button></div></div>';
    }
    html += '</div>';
    container.html(html);
}

function openFullDeliveryModal(id, invoice) { 
    currentBookingId = id; 
    currentInvoiceNo = invoice; 
    $('#fullDeliveryInvoiceNo').text(invoice); 
    $('#full_receiver_name').val(''); 
    $('#full_receiver_mobile').val(''); 
    $('#full_notes').val(''); 
    $('#fullDeliveryModal').removeClass('hidden').addClass('flex'); 
}

function closeFullDeliveryModal() { 
    $('#fullDeliveryModal').addClass('hidden').removeClass('flex'); 
}

function confirmFullDelivery() { 
    if(!currentBookingId) return; 
    
    let name = $('#full_receiver_name').val(); 
    let mobile = $('#full_receiver_mobile').val(); 
    let notes = $('#full_notes').val(); 
    
    $.ajax({ 
        url: '{{ url("admin/deliveries/full") }}/' + currentBookingId, 
        method: 'POST', 
        data: { 
            _token: '{{ csrf_token() }}', 
            receiver_name: name, 
            receiver_mobile: mobile, 
            notes: notes
        }, 
        success: function(res) { 
            if(res.success) {
                alert('✅ Delivery completed');
                closeFullDeliveryModal(); 
                $('#search-btn').click(); 
            } else {
                alert('❌ ' + res.message);
            }
        }, 
        error: function() { 
            alert('❌ Error'); 
        } 
    }); 
}

function openPartialDeliveryModal(id, invoice) { 
    currentBookingId = id; 
    currentInvoiceNo = invoice; 
    $('#partialDeliveryInvoiceNo').text(invoice); 
    $.get('{{ url("admin/shop/booking") }}/' + invoice, function(res) { 
        if(res.success && res.booking) { 
            displayPartialItems(res.booking); 
            $('#partial_receiver_name').val(res.booking.customer_name); 
            $('#partial_receiver_mobile').val(res.booking.customer_mobile); 
            $('#partial_payment_amount').val(0); 
            $('#partialDeliveryModal').removeClass('hidden').addClass('flex'); 
        } else { 
            alert('Error loading booking'); 
        } 
    }).fail(function() { alert('Error'); }); 
}

function displayPartialItems(booking) { 
    let items = booking.non_delivered_items || []; 
    let html = ''; 
    for(let i=0; i<items.length; i++) { 
        let item = items[i]; 
        html += '<div class="item-row border rounded-lg p-3 mb-2"><div class="flex items-center gap-3"><input type="checkbox" class="item-checkbox w-5 h-5" data-id="' + item.id + '" data-price="' + item.unit_price + '"><div class="flex-1"><p><strong>' + escapeHtml(item.cloth_type) + '</strong> - ' + escapeHtml(item.color) + '</p><p class="text-sm text-gray-500">Remaining: ' + item.remaining_quantity + ' / ' + item.total_quantity + ' | Rs. ' + item.unit_price + '</p></div><div><input type="number" class="item-quantity w-24 border rounded px-2 py-1 text-center" value="' + item.remaining_quantity + '" min="1" max="' + item.remaining_quantity + '" disabled></div></div></div>'; 
    } 
    $('#partialItemsList').html(html); 
    $('.item-checkbox').off('change').on('change', function() { 
        let $row = $(this).closest('.item-row'); 
        let $qty = $row.find('.item-quantity'); 
        $(this).is(':checked') ? $qty.prop('disabled', false) : $qty.prop('disabled', true).val(0); 
        calculatePartialTotal(); 
    }); 
    $('.item-quantity').off('change keyup').on('change keyup', calculatePartialTotal); 
}

function calculatePartialTotal() { 
    let total = 0; 
    $('.item-checkbox:checked').each(function() { 
        let $row = $(this).closest('.item-row'); 
        let qty = parseInt($row.find('.item-quantity').val()) || 0; 
        total += qty * ($(this).data('price') || 0); 
    }); 
    $('#partial_payment_amount').val(total.toFixed(2)); 
}

function confirmPartialDelivery() { 
    let items = []; 
    $('.item-checkbox:checked').each(function() { 
        let $row = $(this).closest('.item-row'); 
        let qty = parseInt($row.find('.item-quantity').val()) || 0; 
        if(qty > 0) items.push({ booking_item_id: $(this).data('id'), delivered_quantity: qty }); 
    }); 
    if(items.length === 0) { alert('Select at least one item'); return; } 
    $.ajax({ 
        url: '{{ url("admin/bookings") }}/' + currentBookingId + '/partial-delivery', 
        method: 'POST', 
        data: { 
            _token: '{{ csrf_token() }}', 
            items: items, 
            payment_amount: $('#partial_payment_amount').val(), 
            payment_method: $('#partial_payment_method').val(), 
            receiver_name: $('#partial_receiver_name').val(), 
            receiver_mobile: $('#partial_receiver_mobile').val(), 
            notes: $('#partial_notes').val() 
        }, 
        success: function(res) { 
            alert(res.success ? '✅ Partial delivery processed' : '❌ ' + res.message); 
            if(res.success) { 
                closePartialDeliveryModal(); 
                $('#search-btn').click(); 
            } 
        }, 
        error: function() { alert('Error'); } 
    }); 
}

function closePartialDeliveryModal() { 
    $('#partialDeliveryModal').addClass('hidden').removeClass('flex'); 
}

function removeInvoice(id) { 
    if(confirm('Remove this invoice?')) $.ajax({ url: '{{ url("admin/shop/invoice") }}/' + id, method: 'DELETE', data: { _token: '{{ csrf_token() }}' }, success: function(res) { alert(res.success ? '✅ Removed' : '❌ Error'); if(res.success) location.reload(); } }); 
}

function deleteRange(id, name) { 
    if(confirm('Delete range "' + name + '"?')) $.ajax({ url: '{{ url("admin/shop/range") }}/' + id, method: 'DELETE', data: { _token: '{{ csrf_token() }}' }, success: function(res) { alert(res.success ? '✅ Deleted' : '❌ Error'); if(res.success) location.reload(); } }); 
}

function undeliverInvoice(id, invoice) { 
    if(confirm('Undeliver invoice ' + invoice + '?\n\nThis will move it back to pending.')) { 
        $.ajax({ 
            url: '{{ url("admin/shop/undeliver") }}/' + id, 
            method: 'POST', 
            data: { _token: '{{ csrf_token() }}' }, 
            success: function(res) { 
                alert(res.success ? '✅ Undelivered! Booking is now pending.' : '❌ ' + res.message); 
                if(res.success) $('#search-btn').click(); 
            }, 
            error: function() { alert('Error'); } 
        }); 
    } 
}

function escapeHtml(str) { 
    if(!str) return ''; 
    return String(str).replace(/[&<>]/g, function(m) { 
        if(m === '&') return '&amp;'; 
        if(m === '<') return '&lt;'; 
        if(m === '>') return '&gt;'; 
        return m; 
    }); 
}

function ucfirst(str) { 
    if(!str) return ''; 
    return str.charAt(0).toUpperCase() + str.slice(1); 
}
</script>
@endsection