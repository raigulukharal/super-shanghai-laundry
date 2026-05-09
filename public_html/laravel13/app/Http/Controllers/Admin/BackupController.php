<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BackupLog;
use App\Services\BackupService;
use Illuminate\Http\Request;

class BackupController extends Controller
{
    /**
     * Display backups list
     */
    public function index()
    {
        $data = BackupService::getAllBackups();
        
        return view('admin.backup.index', [
            'backups' => $data['backups'],
            'stats' => $data['stats']
        ]);
    }
    
    /**
     * Create new backup
     */
    public function createBackup(Request $request)
    {
        $type = $request->type ?? 'database';
        
        if ($type == 'database') {
            $result = BackupService::backupDatabase();
        } else {
            $result = ['success' => false, 'message' => 'Invalid backup type'];
        }
        
        if ($request->ajax()) {
            return response()->json($result);
        }
        
        if ($result['success']) {
            return redirect()->route('admin.backup.index')->with('success', $result['message']);
        }
        
        return redirect()->route('admin.backup.index')->with('error', $result['message']);
    }
    
    /**
     * Download backup
     */
    public function download($id)
    {
        $response = BackupService::downloadBackup($id);
        
        if (!$response) {
            return redirect()->route('admin.backup.index')->with('error', 'Backup file not found');
        }
        
        // Update download count
        $backup = BackupLog::find($id);
        if ($backup) {
            $backup->download_count = ($backup->download_count ?? 0) + 1;
            $backup->save();
        }
        
        return $response;
    }
    
    /**
     * Restore backup
     */
    public function restore($id)
    {
        $backup = BackupLog::find($id);
        
        if (!$backup) {
            return response()->json(['success' => false, 'message' => 'Backup not found']);
        }
        
        $result = BackupService::restoreDatabase(basename($backup->file_path));
        
        return response()->json($result);
    }
    
    /**
     * Delete backup
     */
    public function delete($id)
    {
        $result = BackupService::deleteBackup($id);
        
        if (request()->ajax()) {
            return response()->json($result);
        }
        
        if ($result['success']) {
            return redirect()->route('admin.backup.index')->with('success', $result['message']);
        }
        
        return redirect()->route('admin.backup.index')->with('error', $result['message']);
    }
    
    /**
     * Get backup info
     */
    public function info($id)
    {
        $backup = BackupLog::with('creator')->find($id);
        
        if (!$backup) {
            return response()->json(['success' => false, 'message' => 'Backup not found']);
        }
        
        return response()->json([
            'success' => true,
            'backup' => $backup
        ]);
    }
}