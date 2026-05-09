@extends('layouts.admin')

@section('title', 'At Shop - Edit Invoice')
@section('subtitle', 'Search, modify, and download invoices')

@section('content')
<style>
    .item-row { transition: all 0.2s; }
    .item-row:hover { background: #f9fafb; }
    .btn-icon { transition: all 0.2s; }
</style>

<div class="space-y-6">
    <!-- Search Bar -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex gap-3">
            <div class="flex-1 relative">
                <i class="ri-search-line absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="invoice-search" placeholder="🔍 Search by invoice number..." 
                       class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-blue-500">
            </div>
            <button id="search-btn" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Search</button>
        </div>
        <div id="search-results" class="mt-3 hidden">
            <div class="border rounded-lg max-h-60 overflow-y-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold">Invoice</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold">Customer</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold">Date</th>
                            <th class="px-4 py-2 text-center text-xs font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody id="results-list"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div id="edit-area" class="bg-white rounded-xl shadow-sm p-6 hidden">
        <form id="edit-booking-form">
            @csrf
            @method('PUT')
            <input type="hidden" name="booking_id" id="booking_id">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-gray-800">Editing Invoice: <span id="invoice_no_display" class="text-blue-600"></span></h3>
                <div class="flex gap-3">
                    <button type="button" id="download-invoice-btn" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
                        <i class="ri-download-line"></i> Download PDF
                    </button>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        <i class="ri-save-line"></i> Save Changes
                    </button>
                </div>
            </div>

            <!-- Customer Info -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer Name</label>
                    <input type="text" id="customer_name" class="w-full border rounded-lg px-3 py-2 bg-gray-100" readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mobile</label>
                    <input type="text" id="customer_mobile" class="w-full border rounded-lg px-3 py-2 bg-gray-100" readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Booking Date</label>
                    <input type="date" id="booking_date" class="w-full border rounded-lg px-3 py-2 bg-gray-100" readonly>
                </div>
            </div>

            <!-- Items Management -->
            <div class="mb-6">
                <div class="flex justify-between items-center mb-3">
                    <label class="font-semibold text-gray-700">📦 Items</label>
                    <button type="button" id="add-item-btn" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                        + Add Item
                    </button>
                </div>
                <div id="items-container" class="space-y-3"></div>
            </div>

            <!-- Financials -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Discount (Rs.)</label>
                    <input type="number" id="discount" class="w-full border rounded-lg px-3 py-2" step="0.01" value="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Other Charges (Rs.)</label>
                    <input type="number" id="other_charges" class="w-full border rounded-lg px-3 py-2" step="0.01" value="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Paid Amount (Rs.)</label>
                    <input type="number" id="paid_amount" class="w-full border rounded-lg px-3 py-2" step="0.01" value="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                    <select id="payment_method" class="w-full border rounded-lg px-3 py-2">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>
            </div>

            <!-- Totals -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="flex justify-between py-1"><span class="text-gray-600">Total:</span><span id="total_amount" class="font-semibold">Rs.0.00</span></div>
                <div class="flex justify-between py-1"><span class="text-gray-600">Discount:</span><span id="discount_display" class="text-red-600">-Rs.0.00</span></div>
                <div class="flex justify-between py-1"><span class="text-gray-600">Other Charges:</span><span id="charges_display" class="text-blue-600">+Rs.0.00</span></div>
                <div class="border-t my-2"></div>
                <div class="flex justify-between py-1"><span class="text-lg font-bold">Grand Total:</span><span id="grand_total" class="text-2xl font-bold text-blue-600">Rs.0.00</span></div>
                <div class="flex justify-between py-1"><span class="text-gray-600">Paid:</span><span id="paid_display" class="text-green-600">Rs.0.00</span></div>
                <div class="flex justify-between py-1"><span class="text-gray-600">Due:</span><span id="due_display" class="text-red-600 font-bold">Rs.0.00</span></div>
            </div>
        </form>
    </div>
</div>

<script>
let itemIndex = 0;
let currentBookingId = null;

$(document).ready(function() {
    // Search functionality
    $('#search-btn').click(function() {
        let term = $('#invoice-search').val().trim();
        if (term.length < 2) { 
            alert('Enter at least 2 characters'); 
            return; 
        }
        
        $.ajax({
            url: '{{ route("admin.bookings.at-shop") }}',
            method: 'GET',
            data: { search: term, ajax: 1 },
            success: function(res) {
                let results = res.bookings;
                let tbody = $('#results-list');
                tbody.empty();
                
                if (results.length === 0) {
                    tbody.html('<tr><td colspan="4" class="text-center py-4 text-gray-500">No pending bookings found</td></tr>');
                } else {
                    $.each(results, function(i, b) {
                        let bookingDate = b.booking_date ? new Date(b.booking_date).toLocaleDateString() : 'N/A';
                        tbody.append(`
                            <tr class="border-b hover:bg-gray-50 cursor-pointer" onclick="selectBooking(${b.id}, '${b.invoice_no}')">
                                <td class="px-4 py-2 font-mono">${b.invoice_no}</td>
                                <td class="px-4 py-2">${b.customer?.name || 'N/A'}</td>
                                <td class="px-4 py-2">${bookingDate}</td>
                                <td class="px-4 py-2 text-center"><i class="ri-edit-line text-blue-600 text-lg"></i></td>
                            </tr>
                        `);
                    });
                }
                $('#search-results').removeClass('hidden');
            },
            error: function() {
                alert('Error searching bookings');
            }
        });
    });

    // Add item row
    $('#add-item-btn').click(function() {
        addItemRow(itemIndex++);
    });

    function addItemRow(index) {
        let row = `
            <div class="item-row border rounded-lg p-3 bg-white">
                <div class="grid grid-cols-1 md:grid-cols-7 gap-3">
                    <div>
                        <label class="text-xs text-gray-500 block mb-1">Category</label>
                        <select name="items[${index}][category_id]" class="category w-full border rounded px-2 py-1 text-sm">
                            <option value="">Select</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 block mb-1">Cloth Type</label>
                        <select name="items[${index}][cloth_type_id]" class="cloth-type w-full border rounded px-2 py-1 text-sm">
                            <option value="">Select category first</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 block mb-1">Color</label>
                        <select name="items[${index}][color_id]" class="color w-full border rounded px-2 py-1 text-sm">
                            <option value="">Select</option>
                            @foreach($colors as $color)
                            <option value="{{ $color->id }}">{{ $color->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 block mb-1">Qty</label>
                        <input type="number" name="items[${index}][quantity]" class="quantity w-full border rounded px-2 py-1" value="1" min="1">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 block mb-1">Unit Price</label>
                        <input type="number" name="items[${index}][unit_price]" class="unit-price w-full border rounded px-2 py-1" step="0.01">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 block mb-1">Delivery</label>
                        <select name="items[${index}][delivery_type]" class="delivery-type w-full border rounded px-2 py-1 text-sm">
                            <option value="normal">Normal</option>
                            <option value="same_day">Same Day</option>
                            <option value="next_day">Next Day</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="button" class="remove-item w-full bg-red-500 text-white py-1 rounded text-sm hover:bg-red-600">
                            <i class="ri-delete-bin-line"></i> Remove
                        </button>
                    </div>
                </div>
            </div>
        `;
        $('#items-container').append(row);
        attachItemEvents();
    }

    function attachItemEvents() {
        // Category change - load cloth types dynamically
        $('.category').off('change').on('change', function() {
            let categoryId = $(this).val();
            let $clothSelect = $(this).closest('.item-row').find('.cloth-type');
            
            if (categoryId) {
                $clothSelect.html('<option value="">Loading...</option>');
                $.get('{{ route("admin.api.cloth-types-by-category") }}?category_id=' + categoryId, function(types) {
                    let opts = '<option value="">Select</option>';
                    $.each(types, function(i, t) {
                        opts += `<option value="${t.id}" data-price="${t.base_price}">${t.name} (Rs. ${t.base_price})</option>`;
                    });
                    $clothSelect.html(opts);
                }).fail(function() {
                    $clothSelect.html('<option value="">Error loading</option>');
                });
            } else {
                $clothSelect.html('<option value="">Select category first</option>');
            }
        });
        
        // Cloth type change - set price
        $('.cloth-type').off('change').on('change', function() {
            let price = $(this).find(':selected').data('price');
            let categoryId = $(this).closest('.item-row').find('.category').val();
            let childCategories = [3, 4];
            
            if (price) {
                if (childCategories.includes(parseInt(categoryId))) {
                    price = price / 2;
                }
                $(this).closest('.item-row').find('.unit-price').val(price);
            }
            calculateTotals();
        });
        
        // Quantity and price changes
        $('.quantity, .unit-price').off('keyup').on('keyup', function() {
            calculateTotals();
        });
        
        // Remove item
        $('.remove-item').off('click').on('click', function() {
            if ($('.item-row').length > 1) {
                $(this).closest('.item-row').remove();
                calculateTotals();
            } else {
                alert('At least one item is required');
            }
        });
    }

    function calculateTotals() {
        let total = 0;
        $('.item-row').each(function() {
            let qty = parseFloat($(this).find('.quantity').val()) || 0;
            let price = parseFloat($(this).find('.unit-price').val()) || 0;
            total += qty * price;
        });
        
        let discount = parseFloat($('#discount').val()) || 0;
        let charges = parseFloat($('#other_charges').val()) || 0;
        let paid = parseFloat($('#paid_amount').val()) || 0;
        let grand = total - discount + charges;
        let due = grand - paid;
        
        $('#total_amount').text('Rs. ' + total.toFixed(2));
        $('#discount_display').text('- Rs. ' + discount.toFixed(2));
        $('#charges_display').text('+ Rs. ' + charges.toFixed(2));
        $('#grand_total').text('Rs. ' + grand.toFixed(2));
        $('#paid_display').text('Rs. ' + paid.toFixed(2));
        $('#due_display').text('Rs. ' + due.toFixed(2));
    }

    $('#discount, #other_charges, #paid_amount').on('input', calculateTotals);

    // Save edited booking
    $('#edit-booking-form').submit(function(e) {
        e.preventDefault();
        
        let items = [];
        let hasError = false;
        
        $('.item-row').each(function() {
            let categoryId = $(this).find('.category').val();
            let clothTypeId = $(this).find('.cloth-type').val();
            let colorId = $(this).find('.color').val();
            let qty = $(this).find('.quantity').val();
            let price = $(this).find('.unit-price').val();
            
            if (!categoryId || !clothTypeId || !colorId || !qty || !price) {
                hasError = true;
                return false;
            }
            
            items.push({
                category_id: categoryId,
                cloth_type_id: clothTypeId,
                color_id: colorId,
                quantity: qty,
                unit_price: price,
                delivery_type: $(this).find('.delivery-type').val()
            });
        });
        
        if (hasError) {
            alert('Please fill all item details');
            return;
        }
        
        if (items.length === 0) {
            alert('At least one item is required');
            return;
        }
        
        let submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin"></i> Saving...');
        
        $.ajax({
            url: '/admin/bookings/' + currentBookingId,
            method: 'PUT',
            data: {
                _token: '{{ csrf_token() }}',
                items: items,
                discount: $('#discount').val(),
                other_charges: $('#other_charges').val(),
                paid_amount: $('#paid_amount').val(),
                payment_method: $('#payment_method').val()
            },
            success: function(res) {
                if (res.success) {
                    alert('✅ Booking updated successfully!');
                    window.location.reload();
                } else {
                    alert('❌ Error: ' + res.message);
                }
                submitBtn.prop('disabled', false).html('<i class="ri-save-line"></i> Save Changes');
            },
            error: function(xhr) {
                let msg = xhr.responseJSON?.message || 'Error saving booking';
                alert('❌ ' + msg);
                submitBtn.prop('disabled', false).html('<i class="ri-save-line"></i> Save Changes');
            }
        });
    });

    // Download invoice
    $('#download-invoice-btn').click(function() {
        if (currentBookingId) {
            window.open('/admin/bookings/' + currentBookingId + '/download-invoice', '_blank');
        }
    });
});

function selectBooking(id, invoiceNo) {
    currentBookingId = id;
    $('#invoice_no_display').text(invoiceNo);
    
    $.get('/admin/api/booking/' + id, function(data) {
        if (!data.success) {
            alert('Error loading booking details');
            return;
        }
        
        $('#booking_id').val(data.id);
        $('#customer_name').val(data.customer_name);
        $('#customer_mobile').val(data.customer_mobile);
        
        let bookingDate = data.booking_date.split('-').reverse().join('-');
        $('#booking_date').val(bookingDate);
        
        $('#discount').val(data.discount);
        $('#other_charges').val(data.other_charges);
        $('#paid_amount').val(data.paid_amount);
        
        // Clear and rebuild items
        $('#items-container').empty();
        itemIndex = 0;
        
        if (data.items && data.items.length > 0) {
            $.each(data.items, function(i, item) {
                addItemRow(itemIndex++);
                let $row = $('#items-container .item-row:last');
                
                // Set category and trigger change
                $row.find('.category').val(item.category_id);
                $row.find('.category').trigger('change');
                
                // Set values after a short delay to allow cloth types to load
                setTimeout(() => {
                    $row.find('.cloth-type').val(item.id);
                    $row.find('.color').val(item.color_id);
                    $row.find('.quantity').val(item.quantity);
                    $row.find('.unit-price').val(item.unit_price);
                }, 300);
            });
        } else {
            addItemRow(itemIndex++);
        }
        
        setTimeout(() => {
            calculateTotals();
        }, 500);
        
        $('#edit-area').removeClass('hidden');
        $('#search-results').addClass('hidden');
        $('#invoice-search').val('');
    }).fail(function() {
        alert('Error loading booking details');
    });
}
</script>
@endsection