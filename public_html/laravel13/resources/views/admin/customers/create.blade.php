@extends('layouts.admin')

@section('title', 'Add Customer')
@section('subtitle', 'Create new customer')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h3 class="text-lg font-semibold">New Customer</h3>
        </div>
        
        <form id="customer-form" class="p-6">
            @csrf
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Name *</label>
                <input type="text" name="name" id="name" class="w-full border rounded-lg px-3 py-2" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Mobile *</label>
                <input type="text" name="mobile" id="mobile" class="w-full border rounded-lg px-3 py-2" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Area</label>
                <input type="text" name="area" id="area" class="w-full border rounded-lg px-3 py-2">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Customer Codes</label>
                <input type="text" name="codes" id="codes" placeholder="CODE1, CODE2, CODE3" class="w-full border rounded-lg px-3 py-2">
                <p class="text-xs text-gray-500 mt-1">Multiple codes separated by comma</p>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium mb-1">Notes</label>
                <textarea name="notes" id="notes" class="w-full border rounded-lg px-3 py-2" rows="3"></textarea>
            </div>
            
            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.customers.index') }}" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700">Save Customer</button>
            </div>
        </form>
    </div>
</div>

<script>
$('#customer-form').submit(function(e) {
    e.preventDefault();
    
    var codes = $('#codes').val().split(',').map(c => c.trim()).filter(c => c);
    
    $.ajax({
        url: '{{ route("admin.customers.store") }}',
        method: 'POST',
        data: {
            name: $('#name').val(),
            mobile: $('#mobile').val(),
            area: $('#area').val(),
            notes: $('#notes').val(),
            codes: codes,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if(response.success) {
                alert('Customer added successfully!');
                window.location.href = '{{ route("admin.customers.index") }}';
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('Error adding customer');
        }
    });
});
</script>
@endsection