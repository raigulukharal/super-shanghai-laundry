@extends('layouts.admin')

@section('title', 'New Delivery')
@section('subtitle', 'Process delivery for booking #' . ($booking->invoice_no ?? ''))

@section('content')
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-lg font-semibold">Delivery Form</h3>
                <p class="text-sm text-gray-500">
                    Booking: <strong>{{ $booking->invoice_no }}</strong> | 
                    Customer: <strong>{{ $booking->customer->name ?? 'N/A' }}</strong>
                </p>
            </div>
            <a href="{{ route('admin.deliveries.index') }}" class="text-gray-600 hover:text-gray-800">← Back</a>
        </div>
    </div>
    
    <div class="p-6">
        <form id="delivery-form">
            @csrf
            
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Receiver Name *</label>
                    <input type="text" name="receiver_name" id="receiver_name" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" 
                           value="{{ $booking->customer->name ?? '' }}" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Receiver Mobile</label>
                    <input type="text" name="receiver_mobile" id="receiver_mobile" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
                           value="{{ $booking->customer->mobile ?? '' }}">
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Notes</label>
                <textarea name="notes" id="notes" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" rows="2"></textarea>
            </div>
            
            <h4 class="font-semibold text-gray-800 mb-3">Items to Deliver</h4>
            <div id="items-container" class="space-y-2 mb-6">
                @foreach($booking->items as $item)
                @php 
                    $remaining = $item->quantity - ($item->delivered_quantity ?? 0);
                @endphp
                @if($remaining > 0)
                <div class="item-row bg-gray-50 border border-gray-200 rounded-lg p-3 flex items-center gap-4">
                    <input type="checkbox" class="item-checkbox w-5 h-5 text-blue-600" data-id="{{ $item->id }}" data-max="{{ $remaining }}" checked>
                    <div class="flex-1">
                        <p class="font-medium">{{ $item->clothType->name ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-500">Color: {{ $item->color->name ?? 'N/A' }} | Unit Price: Rs. {{ number_format($item->unit_price, 2) }}</p>
                        <p class="text-sm">Remaining: <strong>{{ $remaining }}</strong> / {{ $item->quantity }}</p>
                    </div>
                    <div>
                        <input type="number" class="item-quantity w-24 border border-gray-300 rounded-lg px-2 py-1 text-center" 
                               value="{{ $remaining }}" min="1" max="{{ $remaining }}">
                    </div>
                </div>
                @endif
                @endforeach
            </div>
            
            <div class="mb-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="collect_payment" class="w-4 h-4 text-blue-600">
                    <span class="text-sm font-medium">Collect Payment</span>
                </label>
            </div>
            
            <div id="payment-section" class="hidden bg-blue-50 p-4 rounded-lg mb-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Amount</label>
                        <input type="number" name="payment_amount" id="payment_amount" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2" 
                               step="0.01" value="{{ $booking->grand_total - $booking->paid_amount }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="online">Online Payment</option>
                        </select>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">Due Amount: Rs. {{ number_format($booking->grand_total - $booking->paid_amount, 2) }}</p>
            </div>
            
            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.deliveries.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                    Process Delivery
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Handle checkbox change
    $('.item-checkbox').change(function() {
        let qtyInput = $(this).closest('.item-row').find('.item-quantity');
        qtyInput.prop('disabled', !$(this).is(':checked'));
        if(!$(this).is(':checked')) {
            qtyInput.val(0);
        } else {
            qtyInput.val($(this).data('max'));
        }
    });
    
    // Handle quantity change
    $('.item-quantity').on('change', function() {
        let max = $(this).closest('.item-row').find('.item-checkbox').data('max');
        let val = parseInt($(this).val()) || 0;
        if(val > max) {
            $(this).val(max);
            alert('Quantity cannot exceed ' + max);
        }
        if(val < 1) {
            $(this).val(1);
        }
    });
    
    // Show/hide payment section
    $('#collect_payment').change(function() {
        if($(this).is(':checked')) {
            $('#payment-section').removeClass('hidden');
        } else {
            $('#payment-section').addClass('hidden');
        }
    });
    
    // Form submission
    $('#delivery-form').submit(function(e) {
        e.preventDefault();
        
        let items = [];
        $('.item-row').each(function() {
            let checkbox = $(this).find('.item-checkbox');
            if(checkbox.is(':checked')) {
                let qty = parseInt($(this).find('.item-quantity').val()) || 0;
                if(qty > 0) {
                    items.push({
                        booking_item_id: checkbox.data('id'),
                        quantity: qty
                    });
                }
            }
        });
        
        if(items.length === 0) {
            alert('Please select at least one item to deliver');
            return;
        }
        
        // Disable submit button
        let submitBtn = $(this).find('button[type="submit"]');
        submitBtn.text('Processing...').prop('disabled', true);
        
        let formData = {
            items: items,
            receiver_name: $('#receiver_name').val(),
            receiver_mobile: $('#receiver_mobile').val(),
            notes: $('#notes').val(),
            collect_payment: $('#collect_payment').is(':checked') ? 1 : 0,
            payment_amount: $('#payment_amount').val(),
            payment_method: $('#payment_method').val(),
            _token: '{{ csrf_token() }}'
        };
        
        $.ajax({
            url: '{{ route("admin.deliveries.partial", $booking->id) }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if(response.success) {
                    alert('✅ Delivery processed successfully!');
                    window.location.href = response.redirect;
                } else {
                    alert('Error: ' + response.message);
                    submitBtn.text('Process Delivery').prop('disabled', false);
                }
            },
            error: function(xhr) {
                let errorMsg = 'Error processing delivery';
                if(xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                alert(errorMsg);
                submitBtn.text('Process Delivery').prop('disabled', false);
            }
        });
    });
});
</script>
@endpush
@endsection