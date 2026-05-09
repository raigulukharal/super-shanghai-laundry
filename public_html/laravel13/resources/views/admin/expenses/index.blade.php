@extends('layouts.admin')

@section('title', 'Expenses')
@section('subtitle', 'Manage all business expenses')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
            <p class="text-gray-500 text-xs">Total Expenses</p>
            <p class="text-2xl font-bold">Rs. {{ number_format($stats['total'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500">
            <p class="text-gray-500 text-xs">This Month</p>
            <p class="text-2xl font-bold">Rs. {{ number_format($stats['this_month'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-yellow-500">
            <p class="text-gray-500 text-xs">This Week</p>
            <p class="text-2xl font-bold">Rs. {{ number_format($stats['this_week'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-red-500">
            <p class="text-gray-500 text-xs">Today</p>
            <p class="text-2xl font-bold">Rs. {{ number_format($stats['today'] ?? 0, 2) }}</p>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex justify-between items-center">
        <div class="flex gap-3">
            <a href="{{ route('admin.expenses.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">
                <i class="ri-add-line"></i> Add Expense
            </a>
            <a href="{{ route('admin.expense-categories.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-700">
                <i class="ri-price-tag-line"></i> Manage Categories
            </a>
        </div>
        
        <!-- Filter Form -->
        <form id="filter-form" class="flex gap-2">
            <select name="category_id" class="border rounded-lg px-3 py-2 text-sm">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            <input type="date" name="start_date" class="border rounded-lg px-3 py-2 text-sm">
            <input type="date" name="end_date" class="border rounded-lg px-3 py-2 text-sm">
            <button type="submit" class="bg-gray-500 text-white px-4 py-2 rounded-lg text-sm">Filter</button>
        </form>
    </div>

    <!-- Expenses Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs">Date</th>
                        <th class="px-4 py-3 text-left text-xs">Category</th>
                        <th class="px-4 py-3 text-left text-xs">Description</th>
                        <th class="px-4 py-3 text-right text-xs">Amount</th>
                        <th class="px-4 py-3 text-left text-xs">Added By</th>
                        <th class="px-4 py-3 text-center text-xs">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $expense->expense_date->format('d-m-Y') }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100">
                                {{ $expense->category->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $expense->description ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-red-600">
                            Rs. {{ number_format($expense->amount, 2) }}
                        </td>
                        <td class="px-4 py-3">{{ $expense->creator->name ?? 'Admin' }}</td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex gap-2 justify-center">
                                <a href="{{ route('admin.expenses.edit', $expense->id) }}" class="text-green-600 hover:text-green-800">
                                    <i class="ri-edit-line text-lg"></i>
                                </a>
                                <button onclick="deleteExpense({{ $expense->id }})" class="text-red-600 hover:text-red-800">
                                    <i class="ri-delete-bin-line text-lg"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            <i class="ri-wallet-line text-4xl mb-2 block"></i>
                            No expenses found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t">
            {{ $expenses->links() }}
        </div>
    </div>

    <!-- Category Stats -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h3 class="text-lg font-semibold">Expense by Category</h3>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @foreach($categoryStats as $cat)
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <p class="text-sm font-semibold">{{ $cat->name }}</p>
                    <p class="text-lg font-bold text-red-600">Rs. {{ number_format($cat->expenses_sum_amount ?? 0, 2) }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
function deleteExpense(id) {
    if(confirm('Are you sure you want to delete this expense?')) {
        $.ajax({
            url: '/admin/expenses/' + id,
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if(response.success) {
                    alert('Expense deleted successfully');
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    }
}

$('#filter-form').on('submit', function(e) {
    e.preventDefault();
    let params = new URLSearchParams();
    if($('[name=category_id]').val()) params.append('category_id', $('[name=category_id]').val());
    if($('[name=start_date]').val()) params.append('start_date', $('[name=start_date]').val());
    if($('[name=end_date]').val()) params.append('end_date', $('[name=end_date]').val());
    window.location.href = '{{ route("admin.expenses.index") }}?' + params.toString();
});
</script>
@endsection