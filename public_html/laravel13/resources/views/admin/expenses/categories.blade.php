@extends('layouts.admin')

@section('title', 'Expense Categories')
@section('subtitle', 'Manage expense categories')

@section('content')
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
        <h3 class="text-lg font-semibold">Expense Categories</h3>
        <button onclick="showAddCategory()" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">+ Add Category</button>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left">Category Name</th>
                    <th class="px-4 py-3 text-center">Total Expenses</th>
                    <th class="px-4 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-3">{{ $cat->name }}</td>
                    <td class="px-4 py-3 text-center">{{ $cat->expenses_count }} expenses</td>
                    <td class="px-4 py-3 text-center">
                        <button onclick="editCategory({{ $cat->id }}, '{{ $cat->name }}')" class="text-green-600 hover:text-green-800 mx-1">
                            <i class="ri-edit-line"></i>
                        </button>
                        <button onclick="deleteCategory({{ $cat->id }})" class="text-red-600 hover:text-red-800 mx-1">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="px-4 py-8 text-center">No categories found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Category Modal -->
<div id="categoryModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl w-96 p-6">
        <h3 id="modalTitle" class="text-xl font-bold mb-4">Add Category</h3>
        <input type="hidden" id="categoryId">
        <input type="text" id="categoryName" placeholder="Category Name" class="w-full border rounded-lg px-3 py-2 mb-4">
        <div class="flex justify-end gap-3">
            <button onclick="closeModal()" class="px-4 py-2 border rounded-lg">Cancel</button>
            <button onclick="saveCategory()" class="bg-blue-600 text-white px-4 py-2 rounded-lg">Save</button>
        </div>
    </div>
</div>

<script>
function showAddCategory() {
    $('#modalTitle').text('Add Category');
    $('#categoryId').val('');
    $('#categoryName').val('');
    $('#categoryModal').removeClass('hidden').addClass('flex');
}

function editCategory(id, name) {
    $('#modalTitle').text('Edit Category');
    $('#categoryId').val(id);
    $('#categoryName').val(name);
    $('#categoryModal').removeClass('hidden').addClass('flex');
}

function closeModal() {
    $('#categoryModal').addClass('hidden').removeClass('flex');
}

function saveCategory() {
    let id = $('#categoryId').val();
    let name = $('#categoryName').val();
    let url = id ? '/admin/expense-categories/' + id : '/admin/expense-categories';
    let method = id ? 'PUT' : 'POST';
    
    $.ajax({
        url: url,
        method: method,
        data: {
            name: name,
            _token: '{{ csrf_token() }}',
            _method: method
        },
        success: function(response) {
            if(response.success) {
                alert(response.message);
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        }
    });
}

function deleteCategory(id) {
    if(confirm('Delete this category? Expenses under this category will remain.')) {
        $.ajax({
            url: '/admin/expense-categories/' + id,
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if(response.success) {
                    alert('Category deleted');
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    }
}
</script>
@endsection