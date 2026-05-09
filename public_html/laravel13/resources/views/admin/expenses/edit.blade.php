@extends('layouts.admin')

@section('title', 'Edit Expense')
@section('subtitle', 'Update expense details')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h3 class="text-lg font-semibold">Edit Expense</h3>
        </div>
        
        <form id="expense-form" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Category *</label>
                <select name="expense_category_id" id="category_id" class="w-full border rounded-lg px-3 py-2" required>
                    <option value="">Select Category</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ $expense->expense_category_id == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Amount (Rs.) *</label>
                <input type="number" name="amount" id="amount" class="w-full border rounded-lg px-3 py-2" step="0.01" value="{{ $expense->amount }}" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Expense Date *</label>
                <input type="date" name="expense_date" id="expense_date" class="w-full border rounded-lg px-3 py-2" value="{{ $expense->expense_date->format('Y-m-d') }}" required>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea name="description" id="description" class="w-full border rounded-lg px-3 py-2" rows="3">{{ $expense->description }}</textarea>
            </div>
            
            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.expenses.index') }}" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Update Expense</button>
            </div>
        </form>
    </div>
</div>

<script>
$('#expense-form').submit(function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '{{ route("admin.expenses.update", $expense->id) }}',
        method: 'POST',
        data: {
            _method: 'PUT',
            expense_category_id: $('#category_id').val(),
            amount: $('#amount').val(),
            expense_date: $('#expense_date').val(),
            description: $('#description').val(),
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if(response.success) {
                alert('Expense updated successfully!');
                window.location.href = '{{ route("admin.expenses.index") }}';
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr) {
            alert('Error updating expense');
        }
    });
});
</script>
@endsection