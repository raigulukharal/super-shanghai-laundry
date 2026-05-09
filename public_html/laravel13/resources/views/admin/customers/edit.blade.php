@extends('layouts.admin')

@section('title', 'Edit Customer')
@section('subtitle', 'Update customer information')

@section('content')
<style>
    .form-section {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
        margin-bottom: 24px;
    }
    .section-header {
        padding: 16px 24px;
        border-bottom: 1px solid #e5e7eb;
    }
    .section-header h3 {
        font-size: 18px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .section-body {
        padding: 24px;
    }
    .code-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #e0e7ff;
        color: #3730a3;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
    }
    .code-badge .remove-code {
        cursor: pointer;
        color: #dc2626;
        font-size: 14px;
        transition: all 0.2s;
    }
    .code-badge .remove-code:hover {
        color: #991b1b;
        transform: scale(1.1);
    }
</style>

<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Edit Customer</h2>
            <p class="text-gray-500 text-sm mt-1">Update customer information and codes</p>
        </div>
        <a href="{{ route('admin.customers.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2">
            <i class="ri-arrow-left-line"></i> Back to Customers
        </a>
    </div>

    <div class="form-section">
        <div class="section-header bg-gradient-to-r from-blue-50 to-blue-100">
            <h3>
                <i class="ri-user-line text-blue-600 text-xl"></i>
                Customer Information
            </h3>
        </div>
        <div class="section-body">
            <form id="customer-form">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                        <input type="text" name="name" id="name" value="{{ $customer->name }}" 
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mobile Number *</label>
                        <input type="text" name="mobile" id="mobile" value="{{ $customer->mobile }}" 
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Area / Address</label>
                        <input type="text" name="area" id="area" value="{{ $customer->area }}" 
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
                
                <div class="mt-6 pt-4 border-t">
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('admin.customers.show', $customer->id) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                            Cancel
                        </a>
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                            <i class="ri-save-line"></i> Update Customer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Customer Codes Section -->
    <div class="form-section">
        <div class="section-header bg-gradient-to-r from-green-50 to-green-100">
            <h3>
                <i class="ri-price-tag-line text-green-600 text-xl"></i>
                Customer Codes
            </h3>
        </div>
        <div class="section-body">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Existing Codes</label>
                <div class="flex flex-wrap gap-2" id="codes-list">
                    @foreach($customer->codes as $code)
                    <span class="code-badge">
                        {{ $code->code }}
                        <i class="ri-close-line remove-code" onclick="removeCode({{ $code->id }})" style="cursor: pointer;"></i>
                    </span>
                    @endforeach
                    @if($customer->codes->count() == 0)
                    <p class="text-gray-500 text-sm">No codes added yet</p>
                    @endif
                </div>
            </div>
            
            <div class="flex gap-3 mt-4 pt-4 border-t">
                <input type="text" id="new_code" placeholder="Enter new customer code" 
                       class="flex-1 border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-green-500">
                <button type="button" onclick="addCode()" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                    <i class="ri-add-line"></i> Add Code
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let csrfToken = '{{ csrf_token() }}';

$('#customer-form').submit(function(e) {
    e.preventDefault();
    
    let submitBtn = $(this).find('button[type="submit"]');
    let originalText = submitBtn.html();
    submitBtn.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin"></i> Updating...');
    
    $.ajax({
        url: '{{ route("admin.customers.update", $customer->id) }}',
        method: 'POST',
        data: {
            _method: 'PUT',
            name: $('#name').val(),
            mobile: $('#mobile').val(),
            area: $('#area').val(),
            _token: csrfToken
        },
        success: function(response) {
            if(response.success) {
                alert('✅ Customer updated successfully!');
                window.location.href = '{{ route("admin.customers.show", $customer->id) }}';
            } else {
                alert('❌ Error: ' + response.message);
                submitBtn.prop('disabled', false).html(originalText);
            }
        },
        error: function(xhr) {
            let msg = xhr.responseJSON?.message || 'Error updating customer';
            alert('❌ ' + msg);
            submitBtn.prop('disabled', false).html(originalText);
        }
    });
});

function addCode() {
    let code = $('#new_code').val().trim();
    if(!code) {
        alert('Please enter a code');
        return;
    }
    
    $.ajax({
        url: '{{ route("admin.customers.add-code", $customer->id) }}',
        method: 'POST',
        data: {
            code: code,
            _token: csrfToken
        },
        success: function(response) {
            if(response.success) {
                alert('✅ Code added successfully');
                location.reload();
            } else {
                alert('❌ Error: ' + response.message);
            }
        },
        error: function(xhr) {
            let msg = xhr.responseJSON?.message || 'Error adding code';
            alert('❌ ' + msg);
        }
    });
}

function removeCode(codeId) {
    if(confirm('Remove this customer code?')) {
        $.ajax({
            url: '{{ url("admin/customers") }}/{{ $customer->id }}/code/' + codeId,
            method: 'DELETE',
            data: { _token: csrfToken },
            success: function(response) {
                if(response.success) {
                    alert('✅ Code removed successfully');
                    location.reload();
                } else {
                    alert('❌ Error: ' + response.message);
                }
            },
            error: function() {
                alert('❌ Error removing code');
            }
        });
    }
}
</script>
@endsection