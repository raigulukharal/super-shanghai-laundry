@extends('layouts.admin')

@section('title', 'Add Expense')
@section('subtitle', 'Record new business expense')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h3 class="text-lg font-semibold">Add New Expense</h3>
        </div>
        
        <form id="expense-form" class="p-6">
            @csrf
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Category *</label>
                <select name="expense_category_id" id="category_id" class="w-full border rounded-lg px-3 py-2" required>
                    <option value="">Select Category</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Amount (Rs.) *</label>
                <input type="number" name="amount" id="amount" class="w-full border rounded-lg px-3 py-2" step="0.01" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Expense Date *</label>
                <input type="date" name="expense_date" id="expense_date" class="w-full border rounded-lg px-3 py-2" value="{{ date('Y-m-d') }}" required>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea name="description" id="description" class="w-full border rounded-lg px-3 py-2" rows="3" placeholder="Enter expense details..."></textarea>
            </div>
            
            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.expenses.index') }}" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Save Expense</button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#expense-form').on('submit', function(e) {
        e.preventDefault();
        
        // Disable submit button
        var submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).text('Saving...');
        
        $.ajax({
            url: '{{ route("admin.expenses.store") }}',
            method: 'POST',
            data: {
                expense_category_id: $('#category_id').val(),
                amount: $('#amount').val(),
                expense_date: $('#expense_date').val(),
                description: $('#description').val(),
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if(response.success) {
                    alert('✅ Expense added successfully!');
                    window.location.href = '{{ route("admin.expenses.index") }}';
                } else {
                    alert('❌ Error: ' + response.message);
                    submitBtn.prop('disabled', false).text('Save Expense');
                }
            },
            error: function(xhr) {
                var errorMsg = 'Error adding expense';
                if(xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                alert('❌ ' + errorMsg);
                submitBtn.prop('disabled', false).text('Save Expense');
            }
        });
    });
});
</script>
@endsection