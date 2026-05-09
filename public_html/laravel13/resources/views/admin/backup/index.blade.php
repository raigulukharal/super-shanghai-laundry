@extends('layouts.admin')

@section('title', 'Backup Manager')
@section('subtitle', 'Manage database backups')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
            <p class="text-gray-500 text-xs">Total Backups</p>
            <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500">
            <p class="text-gray-500 text-xs">Total Size</p>
            <p class="text-2xl font-bold">{{ number_format($stats['total_size'] / 1024 / 1024, 2) }} MB</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-yellow-500">
            <p class="text-gray-500 text-xs">Database Backups</p>
            <p class="text-2xl font-bold">{{ $stats['database_count'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-purple-500">
            <p class="text-gray-500 text-xs">Last Backup</p>
            <p class="text-sm font-bold">{{ $stats['last_backup'] ? \Carbon\Carbon::parse($stats['last_backup'])->diffForHumans() : 'Never' }}</p>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex flex-wrap gap-3">
            <button onclick="createBackup()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                <i class="ri-database-line"></i> Create Database Backup
            </button>
            <button onclick="scheduleBackup()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                <i class="ri-calendar-line"></i> Schedule Backup
            </button>
        </div>
        <p class="text-xs text-gray-500 mt-3">
            <i class="ri-information-line"></i> Backups are stored in <code>storage/app/backups/</code> folder
        </p>
    </div>

    <!-- Backups Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h3 class="text-lg font-semibold">📁 Backup History</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs">#</th>
                        <th class="px-4 py-3 text-left text-xs">File Name</th>
                        <th class="px-4 py-3 text-left text-xs">Type</th>
                        <th class="px-4 py-3 text-right text-xs">Size</th>
                        <th class="px-4 py-3 text-left text-xs">Created By</th>
                        <th class="px-4 py-3 text-left text-xs">Date</th>
                        <th class="px-4 py-3 text-center text-xs">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($backups as $backup)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-3">#{{ $backup->id }}</td>
                        <td class="px-4 py-3 font-mono text-sm">{{ $backup->file_name }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700">
                                {{ ucfirst($backup->type) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">{{ number_format($backup->size / 1024, 2) }} KB</td>
                        <td class="px-4 py-3">{{ $backup->creator->name ?? 'System' }}</td>
                        <td class="px-4 py-3">{{ \Carbon\Carbon::parse($backup->created_at)->format('d-m-Y H:i:s') }}</td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex gap-2 justify-center">
                                <a href="{{ route('admin.backup.download', $backup->id) }}" class="text-green-600 hover:text-green-800" title="Download">
                                    <i class="ri-download-line text-xl"></i>
                                </a>
                                <button onclick="restoreBackup({{ $backup->id }})" class="text-blue-600 hover:text-blue-800" title="Restore">
                                    <i class="ri-restart-line text-xl"></i>
                                </button>
                                <button onclick="deleteBackup({{ $backup->id }})" class="text-red-600 hover:text-red-800" title="Delete">
                                    <i class="ri-delete-bin-line text-xl"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            <i class="ri-database-2-line text-4xl mb-2 block"></i>
                            No backups found
                            <p class="text-sm mt-1">Click "Create Database Backup" to create your first backup</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Backup Schedule Settings -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h3 class="text-lg font-semibold">⏰ Scheduled Backup Settings</h3>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-4">Configure automatic backups to run daily at midnight</p>
            
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="font-mono text-sm">
                    <strong>Cron Command:</strong><br>
                    <code class="text-blue-600">0 0 * * * cd {{ base_path() }} && php artisan backup:run >> storage/logs/backup.log 2>&1</code>
                </p>
                <p class="text-xs text-gray-500 mt-2">
                    <i class="ri-information-line"></i> Add this command to your server's crontab for automatic daily backups
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function createBackup() {
    if(confirm('Create database backup? This may take a few minutes.')) {
        $.ajax({
            url: '{{ route("admin.backup.create") }}',
            method: 'POST',
            data: { type: 'database', _token: '{{ csrf_token() }}' },
            beforeSend: function() {
                showLoader();
            },
            success: function(response) {
                hideLoader();
                if(response.success) {
                    alert('✅ ' + response.message);
                    location.reload();
                } else {
                    alert('❌ Error: ' + response.message);
                }
            },
            error: function() {
                hideLoader();
                alert('❌ Error creating backup');
            }
        });
    }
}

function scheduleBackup() {
    alert('⏰ Scheduled backup is configured via cron job.\n\nAdd the cron command to your server crontab:\n\n0 0 * * * cd ' + window.location.origin + ' && php artisan backup:run');
}

function restoreBackup(id) {
    if(confirm('⚠️ WARNING: Restoring will overwrite current database! Continue?')) {
        $.ajax({
            url: '/admin/backup/restore/' + id,
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if(response.success) {
                    alert('✅ Database restored successfully!');
                } else {
                    alert('❌ Error: ' + response.message);
                }
            }
        });
    }
}

function deleteBackup(id) {
    if(confirm('Delete this backup file?')) {
        $.ajax({
            url: '/admin/backup/delete/' + id,
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if(response.success) {
                    alert('Backup deleted successfully');
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    }
}

function showLoader() {
    $('body').append('<div id="loader" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; display:flex; align-items:center; justify-content:center"><div style="background:white; padding:20px; border-radius:10px"><i class="ri-loader-4-line ri-spin text-3xl text-blue-600"></i><p class="mt-2">Creating backup...</p></div></div>');
}

function hideLoader() {
    $('#loader').remove();
}
</script>

<style>
.ri-spin {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endsection