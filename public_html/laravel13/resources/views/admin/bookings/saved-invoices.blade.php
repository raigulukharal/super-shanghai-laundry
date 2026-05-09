@extends('layouts.admin')

@section('title', 'Saved Invoices')
@section('subtitle', 'All saved invoice files')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
            <p class="text-gray-500 text-xs">Total Invoices</p>
            <p class="text-2xl font-bold">{{ $stats['total'] ?? 0 }}</p>
            <p class="text-xs text-gray-400 mt-1">DB: {{ $stats['db_count'] ?? 0 }} | Files: {{ $stats['file_count'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500">
            <p class="text-gray-500 text-xs">Total Downloads</p>
            <p class="text-2xl font-bold">{{ $stats['total_downloads'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-purple-500">
            <p class="text-gray-500 text-xs">Total Size</p>
            <p class="text-2xl font-bold">{{ round(($stats['total_size'] ?? 0) / 1024, 2) }} KB</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-orange-500">
            <p class="text-gray-500 text-xs">Storage Location</p>
            <p class="text-sm font-semibold">public/invoices/</p>
            <p class="text-xs text-gray-400">+ Database</p>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">📁 All Invoices</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Invoices are saved in database and <code>public/invoices/</code> folder
                    </p>
                </div>
                <button onclick="location.reload()" class="text-blue-600 hover:text-blue-800" title="Refresh">
                    <i class="ri-refresh-line text-xl"></i>
                </button>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Invoice #</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">File Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Size</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Downloads</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Downloaded By</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Created</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <span class="font-mono font-semibold text-blue-600">{{ $invoice['invoice_no'] }}</span>
                            @if(!$invoice['from_db'])
                                <span class="ml-2 text-xs px-1 py-0.5 bg-yellow-100 text-yellow-700 rounded">File Only</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $invoice['customer_name'] }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <i class="ri-file-pdf-line text-red-500"></i>
                                <span class="text-sm">{{ $invoice['file_name'] }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">{{ round($invoice['file_size'] / 1024, 2) }} KB</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700">
                                {{ $invoice['download_count'] }} times
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $invoice['downloaded_by'] }}</td>
                        <td class="px-4 py-3 text-sm">{{ $invoice['created_at'] }}</td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex gap-2 justify-center">
                                <a href="{{ asset($invoice['file_path']) }}" target="_blank" class="text-blue-600 hover:text-blue-800" title="View">
                                    <i class="ri-eye-line text-xl"></i>
                                </a>
                                <a href="{{ asset($invoice['file_path']) }}" download class="text-green-600 hover:text-green-800" title="Download">
                                    <i class="ri-download-line text-xl"></i>
                                </a>
                                <button onclick="deleteInvoice('{{ $invoice['file_name'] }}')" class="text-red-600 hover:text-red-800" title="Delete">
                                    <i class="ri-delete-bin-line text-xl"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                            <i class="ri-folder-info-line text-5xl mb-3 block text-gray-300"></i>
                            <p>No saved invoices found</p>
                            <p class="text-sm mt-1">Download an invoice to save it here</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function deleteInvoice(fileName) {
    if(confirm('⚠️ Are you sure you want to delete this invoice?\n\nFile: ' + fileName + '\n\nThis will delete from both database and file system.')) {
        $.ajax({
            url: '/admin/bookings/delete-invoice/' + fileName,
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if(response.success) {
                    alert('✅ ' + response.message);
                    location.reload();
                } else {
                    alert('❌ Error: ' + response.message);
                }
            },
            error: function() {
                alert('❌ Error deleting file');
            }
        });
    }
}
</script>
@endsection