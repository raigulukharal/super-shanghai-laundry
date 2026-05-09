<?php

namespace App\Services;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    /**
     * Generate PDF, save to server, download to PC
     */
    public static function generateAndDownload($bookingId)
    {
        $booking = Booking::with(['customer', 'items.clothType', 'items.color', 'payments'])
            ->findOrFail($bookingId);
        
        // Generate PDF
        $pdf = Pdf::loadView('admin.bookings.pdf-invoice', compact('booking'));
        
        // File name with invoice number
        $fileName = 'invoice_' . $booking->invoice_no . '.pdf';
        
        // ========== 1. SAVE TO SERVER (Domain) ==========
        // Save in public/invoices folder (publicly accessible)
        $publicPath = public_path('invoices/' . $fileName);
        $publicDir = public_path('invoices');
        
        if (!file_exists($publicDir)) {
            mkdir($publicDir, 0777, true);
        }
        $pdf->save($publicPath);
        
        // ========== 2. SAVE TO STORAGE (Backup) ==========
        $storagePath = storage_path('app/public/invoices/' . $fileName);
        $storageDir = storage_path('app/public/invoices');
        
        if (!file_exists($storageDir)) {
            mkdir($storageDir, 0777, true);
        }
        $pdf->save($storagePath);
        
        // ========== 3. DOWNLOAD TO PC ==========
        // This will trigger browser download
        return $pdf->download($fileName);
    }
    
    /**
     * Get all saved invoices from server
     */
    public static function getAllInvoices()
    {
        $invoices = [];
        
        // Get from public/invoices folder
        $publicPath = public_path('invoices');
        if (file_exists($publicPath)) {
            $files = glob($publicPath . '/*.pdf');
            foreach ($files as $file) {
                $invoices[] = [
                    'name' => basename($file),
                    'path' => $file,
                    'url' => asset('invoices/' . basename($file)),
                    'size' => filesize($file),
                    'created_at' => date('Y-m-d H:i:s', filectime($file)),
                    'location' => 'Server (Public)'
                ];
            }
        }
        
        // Get from storage
        $storagePath = storage_path('app/public/invoices');
        if (file_exists($storagePath)) {
            $files = glob($storagePath . '/*.pdf');
            foreach ($files as $file) {
                // Avoid duplicates
                $existing = array_filter($invoices, fn($i) => $i['name'] === basename($file));
                if (empty($existing)) {
                    $invoices[] = [
                        'name' => basename($file),
                        'path' => $file,
                        'url' => Storage::url('invoices/' . basename($file)),
                        'size' => filesize($file),
                        'created_at' => date('Y-m-d H:i:s', filectime($file)),
                        'location' => 'Server (Storage)'
                    ];
                }
            }
        }
        
        return $invoices;
    }
    
    /**
     * Delete invoice from server
     */
    public static function deleteInvoice($fileName)
    {
        $deleted = false;
        
        // Delete from public
        $publicPath = public_path('invoices/' . $fileName);
        if (file_exists($publicPath)) {
            unlink($publicPath);
            $deleted = true;
        }
        
        // Delete from storage
        $storagePath = storage_path('app/public/invoices/' . $fileName);
        if (file_exists($storagePath)) {
            unlink($storagePath);
            $deleted = true;
        }
        
        return $deleted;
    }
}