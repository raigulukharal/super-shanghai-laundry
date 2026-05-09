@extends('layouts.admin')

@section('title', 'Edit Booking')
@section('subtitle', 'Edit Booking #' . $booking->invoice_no)

@section('content')
<style>
    .delivery-card {
        transition: all 0.2s ease;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 12px;
        text-align: center;
        cursor: pointer;
    }
    .delivery-card:hover, .delivery-card.selected {
        border-color: #3b82f6;
        background: #eff6ff;
    }
    .delivery-card i {
        font-size: 24px;
        margin-bottom: 8px;
        display: block;
    }
    .delivery-card.selected i {
        color: #3b82f6;
    }
    .item-row {
        transition: all 0.2s ease;
    }
    .item-row:hover {
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .delivery-icon-btn {
        transition: all 0.2s ease;
        cursor: pointer;
        padding: 8px;
        border-radius: 8px;
        background: #f3f4f6;
        text-align: center;
    }
    .delivery-icon-btn.selected {
        background: #3b82f6;
        color: white;
    }
    .delivery-icon-btn i {
        font-size: 20px;
    }
    .delivery-icon-btn span {
        font-size: 10px;
        display: block;
    }
    .select-colors-btn {
        background: #f3f4f6;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s;
        width: 100%;
        text-align: left;
    }
    .select-colors-btn:hover {
        background: #e5e7eb;
        border-color: #3b82f6;
    }
    .color-checkbox-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        margin-bottom: 8px;
        background: white;
    }
    .color-checkbox-item:hover {
        background: #f9fafb;
    }
</style>

<div>
    <!-- Invoice Info Card -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg shadow-lg mb-6 p-5 text-white">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-sm opacity-90">Invoice Number</p>
                <p class="text-3xl font-bold font-mono tracking-wider">{{ $booking->invoice_no }}</p>
                <p class="text-xs opacity-75 mt-1">Editing existing booking</p>
            </div>
            <div class="text-right">
                <div class="bg-white/20 rounded-lg px-4 py-2 backdrop-blur-sm">
                    <p class="text-xs">Edit Mode</p>
                    <p class="text-lg font-bold" id="current-date-display"></p>
                    <p class="text-xs" id="current-time-display"></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-6">
            <form id="booking-form">
                @csrf
                @method('PUT')
                
                <!-- Customer Section -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-3 text-lg">👤 Customer Details</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Customer Name *</label>
                            <input type="text" id="customer_name" name="customer_name" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500" value="{{ $booking->customer->name ?? '' }}" placeholder="Enter customer name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mobile Number *</label>
                            <input type="text" id="customer_mobile" name="customer_mobile" maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')" pattern="03[0-9]{9}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500" value="{{ $booking->customer->mobile ?? '' }}" placeholder="03XXXXXXXXX" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Customer Code</label>
                            <input type="text" id="customer_code" name="customer_code" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500" value="{{ $booking->customer_code_used ?? '' }}" placeholder="Enter customer code">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Area / Address</label>
                            <input type="text" id="customer_area" name="customer_area" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500" value="{{ $booking->customer->area ?? '' }}" placeholder="Area or address">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Customer Notes</label>
                        <textarea id="customer_notes" name="customer_notes" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500" rows="2">{{ $booking->customer_notes }}</textarea>
                    </div>
                    <input type="hidden" id="customer_id" name="customer_id" value="{{ $booking->customer_id }}">
                </div>

                <!-- Booking & Delivery Dates -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">📅 Booking Date</label>
                        <input type="date" id="booking_date" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500" value="{{ $booking->booking_date instanceof \Carbon\Carbon ? $booking->booking_date->format('Y-m-d') : date('Y-m-d', strtotime($booking->booking_date)) }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">🚚 Delivery Type</label>
                        <div class="grid grid-cols-3 gap-2">
                            <div class="delivery-icon-btn" data-delivery="normal"><i class="ri-truck-line"></i><span>Normal (3d)</span></div>
                            <div class="delivery-icon-btn" data-delivery="same_day"><i class="ri-flashlight-line"></i><span>Same Day</span></div>
                            <div class="delivery-icon-btn" data-delivery="next_day"><i class="ri-speed-line"></i><span>Next Day</span></div>
                            <div class="delivery-icon-btn" data-delivery="urgent"><i class="ri-fire-line"></i><span>Urgent</span></div>
                            <div class="delivery-icon-btn" data-delivery="4days"><i class="ri-calendar-line"></i><span>4 Days</span></div>
                            <div class="delivery-icon-btn" data-delivery="5days"><i class="ri-calendar-line"></i><span>5 Days</span></div>
                        </div>
                        <input type="hidden" id="delivery_override" name="delivery_override" value="normal">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">📅 Expected Delivery Date</label>
                        <input type="date" id="expected_delivery" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500" value="{{ $booking->expected_delivery_date ? (\Carbon\Carbon::parse($booking->expected_delivery_date)->format('Y-m-d')) : date('Y-m-d') }}">
                    </div>
                </div>

                <!-- Items Section -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-3">
                        <label class="block text-gray-700 font-semibold">📦 Booking Items</label>
                        <button type="button" id="add-item" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition">+ Add Item</button>
                    </div>
                    <div id="items-container" class="space-y-3"></div>
                </div>

                <!-- Charges -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">💰 Discount (Rs.)</label><input type="number" id="discount" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500" value="{{ $booking->discount }}" step="0.01"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">➕ Other Charges (Rs.)</label><input type="number" id="other_charges" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500" value="{{ $booking->other_charges }}" step="0.01"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">💵 Paid Amount (Rs.)</label><input type="number" id="paid_amount" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500" value="{{ $booking->paid_amount }}" step="0.01"></div>
                </div>
                
                <!-- Payment Method -->
                <div id="payment-method-div" class="{{ $booking->paid_amount > 0 ? '' : 'hidden' }} mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">💳 Payment Method</label>
                    <select id="payment_method" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500">
                        <option value="cash">💵 Cash</option>
                        <option value="card">💳 Card</option>
                        <option value="bank_transfer">🏦 Bank Transfer</option>
                        <option value="online">📱 Online Payment</option>
                    </select>
                </div>

                <!-- Totals -->
                <div class="bg-gray-50 rounded-xl p-5 mb-6">
                    <div class="space-y-2">
                        <div class="flex justify-between"><span class="text-gray-600">Total Amount:</span><span id="total-amount" class="font-semibold">Rs. 0.00</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Discount:</span><span id="discount-amount" class="text-red-600">- Rs. 0.00</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Other Charges:</span><span id="charges-amount" class="text-blue-600">+ Rs. 0.00</span></div>
                        <div class="border-t border-gray-200 my-2"></div>
                        <div class="flex justify-between"><span class="text-lg font-bold text-gray-800">Grand Total:</span><span id="grand-total" class="text-2xl font-bold text-blue-600">Rs. 0.00</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Paid:</span><span id="paid-display" class="text-green-600 font-semibold">Rs. 0.00</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Due:</span><span id="due-display" class="text-red-600 font-semibold">Rs. 0.00</span></div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-green-600 text-white py-3 rounded-xl hover:bg-green-700 transition font-semibold text-lg flex items-center justify-center gap-2">
                    <i class="ri-save-line"></i> Update Booking
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Color Selection Modal -->
<div id="colorModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4" style="z-index: 10000;">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-5 py-3 flex justify-between items-center rounded-t-xl">
            <h3 class="text-white font-semibold">Select Colors</h3>
            <button type="button" id="closeModalBtn" class="text-white text-2xl hover:text-gray-200">&times;</button>
        </div>
        <div class="p-5">
            <p class="text-sm text-gray-600 mb-3">Item: <strong id="modalItemName"></strong></p>
            <div id="modalColorList" class="space-y-2 max-h-96 overflow-y-auto"></div>
            <div class="mt-4 pt-3 border-t flex justify-end gap-2">
                <button type="button" id="cancelModalBtn" class="px-4 py-2 border rounded-lg hover:bg-gray-100">Cancel</button>
                <button type="button" id="saveModalBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let currentColorRowIndex = null;
    let colorsList = @json($colors);
    let itemIndex = {{ $booking->items->count() }};
    
    // Load existing items
    @foreach($booking->items as $index => $item)
    addItemRow({{ $index }}, {
        category_id: {{ $item->clothType->category_id ?? 0 }},
        cloth_type_id: {{ $item->cloth_type_id }},
        color_data: @json($item->color_data),
        unit_price: {{ $item->unit_price }},
        delivery_type: '{{ $item->delivery_type }}',
        cloth_name: '{{ addslashes($item->clothType->name ?? "") }}'
    });
    @endforeach
    
    // Modal elements
    const $colorModal = $('#colorModal');
    const $closeModalBtn = $('#closeModalBtn');
    const $cancelModalBtn = $('#cancelModalBtn');
    const $saveModalBtn = $('#saveModalBtn');
    const $modalItemName = $('#modalItemName');
    const $modalColorList = $('#modalColorList');
    
    function closeModal() {
        $colorModal.addClass('hidden').removeClass('flex');
        currentColorRowIndex = null;
    }
    
    $closeModalBtn.on('click', closeModal);
    $cancelModalBtn.on('click', closeModal);
    
    // Display current date in PKT
    function getCurrentDateInPKT() {
        let now = new Date();
        let pktTime = new Date(now.toLocaleString("en-US", {timeZone: "Asia/Karachi"}));
        let year = pktTime.getFullYear();
        let month = String(pktTime.getMonth() + 1).padStart(2, '0');
        let day = String(pktTime.getDate()).padStart(2, '0');
        return year + '-' + month + '-' + day;
    }
    
    function getCurrentTimeInPKT() {
        let now = new Date();
        let pktTime = new Date(now.toLocaleString("en-US", {timeZone: "Asia/Karachi"}));
        let hours = String(pktTime.getHours()).padStart(2, '0');
        let minutes = String(pktTime.getMinutes()).padStart(2, '0');
        let seconds = String(pktTime.getSeconds()).padStart(2, '0');
        return hours + ':' + minutes + ':' + seconds;
    }
    
    $('#current-date-display').text(getCurrentDateInPKT());
    
    function updateTime() { $('#current-time-display').text(getCurrentTimeInPKT()); }
    updateTime();
    setInterval(updateTime, 1000);
    
    // Set delivery icon based on existing expected delivery
    function setDeliveryIcon() {
        let expectedDate = $('#expected_delivery').val();
        let bookingDate = $('#booking_date').val();
        if (expectedDate && bookingDate) {
            let date1 = new Date(bookingDate);
            let date2 = new Date(expectedDate);
            let diffDays = Math.ceil((date2 - date1) / (1000 * 60 * 60 * 24));
            let deliveryType = 'normal';
            if (diffDays == 0) deliveryType = 'same_day';
            else if (diffDays == 1) deliveryType = 'next_day';
            else if (diffDays == 4) deliveryType = '4days';
            else if (diffDays == 5) deliveryType = '5days';
            else deliveryType = 'normal';
            
            $('.delivery-icon-btn').removeClass('selected');
            $(`.delivery-icon-btn[data-delivery="${deliveryType}"]`).addClass('selected');
            $('#delivery_override').val(deliveryType);
        }
    }
    setDeliveryIcon();
    
    // Delivery icon selection
    $('.delivery-icon-btn').click(function() {
        $('.delivery-icon-btn').removeClass('selected');
        $(this).addClass('selected');
        let deliveryType = $(this).data('delivery');
        $('#delivery_override').val(deliveryType);
        calculateExpectedDelivery();
    });
    
    function calculateExpectedDelivery() {
        var bookingDate = $('#booking_date').val();
        var deliveryType = $('#delivery_override').val();
        if (!bookingDate) return;
        var date = new Date(bookingDate);
        var daysToAdd = 3;
        switch(deliveryType) {
            case 'same_day': daysToAdd = 0; break;
            case 'next_day': daysToAdd = 1; break;
            case 'urgent': daysToAdd = 1; break;
            case '4days': daysToAdd = 4; break;
            case '5days': daysToAdd = 5; break;
            default: daysToAdd = 3;
        }
        date.setDate(date.getDate() + daysToAdd);
        $('#expected_delivery').val(date.toISOString().split('T')[0]);
    }
    
    $('#booking_date').on('change', function() {
        calculateExpectedDelivery();
        setDeliveryIcon();
    });
    
    // Payment method visibility
    $('#paid_amount').on('keyup change', function() {
        if (parseFloat($(this).val()) > 0) $('#payment-method-div').removeClass('hidden');
        else $('#payment-method-div').addClass('hidden');
        calculateTotals();
    });
    
    function addItemRow(idx, data = null) {
        let categoryId = data ? data.category_id : '';
        let clothTypeId = data ? data.cloth_type_id : '';
        let colorData = data ? data.color_data : '';
        let unitPrice = data ? data.unit_price : '';
        let deliveryType = data ? data.delivery_type : 'normal';
        let clothName = data ? data.cloth_name : '';
        
        let rowHtml = '<div class="item-row bg-white border border-gray-200 rounded-lg p-3 mb-3" data-index="' + idx + '">';
        rowHtml += '<div class="grid grid-cols-1 md:grid-cols-5 gap-3">';
        
        rowHtml += '<div><label class="text-xs text-gray-500 block mb-1">Category</label>';
        rowHtml += '<select name="items[' + idx + '][category_id]" class="category w-full border border-gray-300 rounded-lg px-2 py-2 text-sm" required>';
        rowHtml += '<option value="">Select Category</option>';
        @foreach($categories as $category)
        rowHtml += '<option value="{{ $category->id }}" ' + (categoryId == {{ $category->id }} ? 'selected' : '') + '>{{ $category->name }}</option>';
        @endforeach
        rowHtml += '</select></div>';
        
        rowHtml += '<div><label class="text-xs text-gray-500 block mb-1">Cloth Type</label>';
        rowHtml += '<select name="items[' + idx + '][cloth_type_id]" class="cloth-type w-full border border-gray-300 rounded-lg px-2 py-2 text-sm" required>';
        rowHtml += '<option value="">Select cloth type</option>';
        rowHtml += '</select></div>';
        
        rowHtml += '<div><label class="text-xs text-gray-500 block mb-1">Colors</label>';
        rowHtml += '<button type="button" class="select-colors-btn w-full border border-gray-300 rounded-lg px-2 py-2 text-sm bg-gray-50 hover:bg-gray-100" data-index="' + idx + '" data-cloth-name="' + clothName + '">';
        rowHtml += '<i class="ri-palette-line"></i> Select Colors';
        rowHtml += '</button>';
        rowHtml += '<input type="hidden" name="items[' + idx + '][color_data]" class="color-data" value=\'' + JSON.stringify(colorData || {}) + '\'>';
        rowHtml += '<div class="selected-colors-display text-xs text-gray-500 mt-1"></div>';
        rowHtml += '</div>';
        
        rowHtml += '<div><label class="text-xs text-gray-500 block mb-1">Unit Price</label>';
        rowHtml += '<input type="number" name="items[' + idx + '][unit_price]" class="unit-price w-full border border-gray-300 rounded-lg px-2 py-2 text-sm" placeholder="Price" value="' + unitPrice + '" step="0.01" required>';
        rowHtml += '</div>';
        
        rowHtml += '<div><label class="text-xs text-gray-500 block mb-1">Delivery Type</label>';
        rowHtml += '<select name="items[' + idx + '][delivery_type]" class="delivery-type w-full border border-gray-300 rounded-lg px-2 py-2 text-sm">';
        rowHtml += '<option value="normal" ' + (deliveryType == 'normal' ? 'selected' : '') + '>Normal</option>';
        rowHtml += '<option value="urgent" ' + (deliveryType == 'urgent' ? 'selected' : '') + '>Urgent</option>';
        rowHtml += '</select></div>';
        
        rowHtml += '</div>';
        rowHtml += '<input type="hidden" name="items[' + idx + '][quantity]" class="quantity" value="1">';
        rowHtml += '</div>';
        
        $('#items-container').append(rowHtml);
        
        // ✅ AUTO LOAD CLOTH TYPES FOR EXISTING CATEGORY (Fix for Male and other categories)
        let $newRow = $('.item-row').last();
        let $categorySelect = $newRow.find('.category');
        let existingCategoryId = $categorySelect.val();
        
        if (existingCategoryId) {
            let $clothTypeSelect = $newRow.find('.cloth-type');
            let $priceInput = $newRow.find('.unit-price');
            
            $clothTypeSelect.html('<option value="">Loading...</option>');
            $.ajax({
                url: '{{ route("admin.api.cloth-types-by-category") }}',
                method: 'GET',
                data: { category_id: existingCategoryId },
                dataType: 'json',
                success: function(types) {
                    let options = '<option value="">Select cloth type</option>';
                    if (types && types.length > 0) {
                        for (let i = 0; i < types.length; i++) {
                            let type = types[i];
                            let displayPrice = type.base_price || 0;
                            let selected = (clothTypeId == type.id) ? 'selected' : '';
                            options += '<option value="' + type.id + '" data-price="' + displayPrice + '" ' + selected + '>' + escapeHtml(type.name) + ' (Rs. ' + parseFloat(displayPrice).toFixed(2) + ')</option>';
                        }
                    }
                    $clothTypeSelect.html(options);
                    
                    if (clothTypeId) {
                        let selectedOption = $clothTypeSelect.find('option:selected');
                        let price = selectedOption.data('price');
                        if (price) {
                            $priceInput.val(parseFloat(price).toFixed(2));
                        }
                    }
                    calculateTotals();
                },
                error: function() { 
                    $clothTypeSelect.html('<option value="">Error loading</option>'); 
                }
            });
        }
        
        // Update color display if exists
        if (colorData && Object.keys(colorData).length > 0) {
            updateColorDisplay($('.item-row').last(), colorData);
        }
        
        attachEvents();
        calculateTotals();
    }
    
    function updateColorDisplay($row, colorData) {
        let colorNames = [];
        let totalQty = 0;
        for (let id in colorData) {
            colorNames.push(colorData[id].name + ' (' + colorData[id].qty + ')');
            totalQty += colorData[id].qty;
        }
        $row.find('.selected-colors-display').html(colorNames.join(', '));
        $row.find('.quantity').val(totalQty);
        $row.find('.select-colors-btn').html('<i class="ri-checkbox-circle-line"></i> ' + colorNames.length + ' color(s) selected');
    }
    
    function attachEvents() {
        $('.category').off('change').on('change', function() {
            let categoryId = $(this).val();
            let $row = $(this).closest('.item-row');
            let $clothTypeSelect = $row.find('.cloth-type');
            let $priceInput = $row.find('.unit-price');
            
            if (categoryId) {
                $clothTypeSelect.html('<option value="">Loading...</option>');
                $.ajax({
                    url: '{{ route("admin.api.cloth-types-by-category") }}',
                    method: 'GET',
                    data: { category_id: categoryId },
                    dataType: 'json',
                    success: function(types) {
                        let options = '<option value="">Select cloth type</option>';
                        if (types && types.length > 0) {
                            for (let i = 0; i < types.length; i++) {
                                let type = types[i];
                                let displayPrice = type.base_price || 0;
                                options += '<option value="' + type.id + '" data-price="' + displayPrice + '">' + escapeHtml(type.name) + ' (Rs. ' + parseFloat(displayPrice).toFixed(2) + ')</option>';
                            }
                        }
                        $clothTypeSelect.html(options);
                        $priceInput.val('');
                    },
                    error: function() { $clothTypeSelect.html('<option value="">Error loading</option>'); }
                });
            } else {
                $clothTypeSelect.html('<option value="">Select category first</option>');
                $priceInput.val('');
            }
        });
        
        $('.cloth-type').off('change').on('change', function() {
            let price = $(this).find(':selected').data('price');
            let clothName = $(this).find(':selected').text().split(' (Rs')[0];
            let $row = $(this).closest('.item-row');
            let $priceInput = $row.find('.unit-price');
            let $colorBtn = $row.find('.select-colors-btn');
            
            if (price !== undefined && price !== null && !isNaN(parseFloat(price))) {
                $priceInput.val(parseFloat(price).toFixed(2));
            }
            $colorBtn.attr('data-cloth-name', clothName);
            calculateTotals();
        });
        
        $('.unit-price').off('change keyup').on('change keyup', function() {
            calculateTotals();
        });
        
        // Color selection button click
        $('.select-colors-btn').off('click').on('click', function() {
            currentColorRowIndex = $(this).data('index');
            let $row = $(this).closest('.item-row');
            let clothTypeName = $row.find('.cloth-type option:selected').text().split(' (Rs')[0];
            if (!clothTypeName || clothTypeName === 'Select cloth type' || clothTypeName === 'Select category first') {
                alert('Please select cloth type first');
                return;
            }
            $modalItemName.text(clothTypeName);
            loadColorOptions($row);
            $colorModal.removeClass('hidden').addClass('flex');
        });
    }
    
    function loadColorOptions($row) {
        let savedData = $row.find('.color-data').val();
        let savedColors = savedData ? JSON.parse(savedData) : {};
        let html = '';
        
        for (let i = 0; i < colorsList.length; i++) {
            let color = colorsList[i];
            let qtyValue = savedColors[color.id] ? savedColors[color.id].qty : 0;
            let isChecked = qtyValue > 0;
            
            html += '<div class="color-checkbox-item">';
            html += '<input type="checkbox" class="color-checkbox w-4 h-4 rounded" data-color-id="' + color.id + '" data-color-name="' + escapeHtml(color.name) + '" ' + (isChecked ? 'checked' : '') + '>';
            html += '<div class="w-5 h-5 rounded-full border" style="background-color: ' + (color.code || '#cccccc') + '"></div>';
            html += '<span class="flex-1 text-sm">' + escapeHtml(color.name) + '</span>';
            html += '<input type="number" class="color-qty w-24 border rounded-lg px-2 py-1 text-center text-sm" data-color-id="' + color.id + '" data-color-name="' + escapeHtml(color.name) + '" value="' + qtyValue + '" min="0" max="100" ' + (isChecked ? '' : 'disabled') + '>';
            html += '</div>';
        }
        
        $modalColorList.html(html);
        
        $('.color-checkbox').off('change').on('change', function() {
            let $qtyInput = $(this).closest('.color-checkbox-item').find('.color-qty');
            if ($(this).is(':checked')) {
                $qtyInput.prop('disabled', false).val(1);
            } else {
                $qtyInput.prop('disabled', true).val(0);
            }
        });
        
        $('.color-qty').off('change keyup').on('change keyup', function() {
            let qtyValue = parseInt($(this).val()) || 0;
            let $checkbox = $(this).closest('.color-checkbox-item').find('.color-checkbox');
            if (qtyValue > 0 && !$checkbox.is(':checked')) {
                $checkbox.prop('checked', true);
            } else if (qtyValue === 0 && $checkbox.is(':checked')) {
                $checkbox.prop('checked', false);
                $(this).prop('disabled', true);
            }
        });
    }
    
    $saveModalBtn.on('click', function() {
        let selectedColors = {};
        let totalQty = 0;
        
        $('.color-checkbox:checked').each(function() {
            let $item = $(this).closest('.color-checkbox-item');
            let qtyValue = parseInt($item.find('.color-qty').val()) || 1;
            let colorId = $(this).data('color-id');
            let colorName = $(this).data('color-name');
            selectedColors[colorId] = { id: colorId, name: colorName, qty: qtyValue };
            totalQty += qtyValue;
        });
        
        let $row = $('.item-row').eq(currentColorRowIndex);
        
        if (totalQty === 0) {
            $row.find('.color-data').val('');
            $row.find('.quantity').val(1);
            $row.find('.select-colors-btn').html('<i class="ri-palette-line"></i> Select Colors');
            $row.find('.selected-colors-display').html('');
        } else {
            $row.find('.color-data').val(JSON.stringify(selectedColors));
            $row.find('.quantity').val(totalQty);
            
            let colorNames = [];
            for (let id in selectedColors) {
                colorNames.push(selectedColors[id].name + ' (' + selectedColors[id].qty + ')');
            }
            $row.find('.select-colors-btn').html('<i class="ri-checkbox-circle-line"></i> ' + colorNames.length + ' color(s) selected');
            $row.find('.selected-colors-display').html(colorNames.join(', '));
        }
        
        closeModal();
        calculateTotals();
    });
    
    function calculateTotals() {
        let total = 0;
        $('.item-row').each(function() {
            let price = parseFloat($(this).find('.unit-price').val()) || 0;
            let qty = parseFloat($(this).find('.quantity').val()) || 0;
            total += qty * price;
        });
        
        let discount = parseFloat($('#discount').val()) || 0;
        let charges = parseFloat($('#other_charges').val()) || 0;
        let paid = parseFloat($('#paid_amount').val()) || 0;
        let grand = total - discount + charges;
        let due = grand - paid;
        
        $('#total-amount').text('Rs. ' + total.toFixed(2));
        $('#discount-amount').text('- Rs. ' + discount.toFixed(2));
        $('#charges-amount').text('+ Rs. ' + charges.toFixed(2));
        $('#grand-total').text('Rs. ' + grand.toFixed(2));
        $('#paid-display').text('Rs. ' + paid.toFixed(2));
        $('#due-display').text('Rs. ' + due.toFixed(2));
    }
    
    $('#discount, #other_charges').on('keyup', calculateTotals);
    
    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }
    
    // Add new item button
    $('#add-item').click(function() {
        addItemRow(itemIndex++);
    });
    
    // Form submit
    $('#booking-form').submit(function(e) {
        e.preventDefault();
        
        let name = $('#customer_name').val().trim();
        let mobile = $('#customer_mobile').val().trim();
        
        if (!name || !mobile) {
            if (!name) $('#customer_name').addClass('border-red-500');
            if (!mobile) $('#customer_mobile').addClass('border-red-500');
            setTimeout(() => {
                $('#customer_name, #customer_mobile').removeClass('border-red-500');
            }, 3000);
            alert('Please enter customer name and mobile number');
            return;
        }
        
        let items = [];
        let hasError = false;
        
        $('.item-row').each(function() {
            let categoryId = $(this).find('.category').val();
            let clothType = $(this).find('.cloth-type').val();
            let price = $(this).find('.unit-price').val();
            
            if (!categoryId || !clothType || !price) {
                hasError = true;
                return false;
            }
            
            items.push({
                category_id: categoryId,
                cloth_type_id: clothType,
                color_data: $(this).find('.color-data').val() || '',
                unit_price: price,
                quantity: $(this).find('.quantity').val() || 1,
                delivery_type: $(this).find('.delivery-type').val()
            });
        });
        
        if (hasError) {
            alert('Please fill all required item details');
            return;
        }
        
        if (items.length === 0) {
            alert('Please add at least one item');
            return;
        }
        
        let submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin"></i> Updating...');
        
        $.ajax({
            url: '{{ route("admin.bookings.update", $booking->id) }}',
            method: 'POST',
            data: {
                customer_name: name,
                customer_mobile: mobile,
                customer_id: $('#customer_id').val(),
                customer_code_used: $('#customer_code').val(),
                customer_area: $('#customer_area').val(),
                customer_notes: $('#customer_notes').val(),
                items: items,
                discount: $('#discount').val(),
                other_charges: $('#other_charges').val(),
                paid_amount: $('#paid_amount').val(),
                payment_method: $('#payment_method').val(),
                booking_date: $('#booking_date').val(),
                expected_delivery_date: $('#expected_delivery').val(),
                _token: '{{ csrf_token() }}',
                _method: 'PUT'
            },
            success: function(res) {
                if (res.success) {
                    alert('✅ Booking updated successfully!');
                    window.location.href = '{{ route("admin.bookings.show", $booking->id) }}';
                } else {
                    alert('❌ Error: ' + (res.message || 'Something went wrong'));
                    submitBtn.prop('disabled', false).html('<i class="ri-save-line"></i> Update Booking');
                }
            },
            error: function(xhr) {
                let msg = xhr.responseJSON?.message || 'Error updating booking';
                alert('❌ ' + msg);
                submitBtn.prop('disabled', false).html('<i class="ri-save-line"></i> Update Booking');
            }
        });
    });
    
    // Initial calculation
    calculateTotals();
    
    // If paid amount > 0, show payment method
    if (parseFloat($('#paid_amount').val()) > 0) {
        $('#payment-method-div').removeClass('hidden');
    }
});
</script>
@endsection