<?php

namespace App\Services;

use App\Models\InvoiceSequence;
use Illuminate\Support\Facades\DB;

class InvoiceNumberService
{
    /**
     * Generate invoice number in format: PREFIX-SEQUENCE
     */
    public static function generate()
    {
        return DB::transaction(function () {
            // Get the latest booking invoice number
            $lastBooking = DB::table('bookings')->orderBy('id', 'desc')->first();
            
            $lastPrefix = 1;
            // CHANGE HERE: Set default to 50000 instead of 0
            $lastNumber = 50000;  // ← YAHAN CHANGE KIYA
            
            if ($lastBooking && $lastBooking->invoice_no) {
                $parts = explode('-', $lastBooking->invoice_no);
                if (count($parts) == 2) {
                    $lastPrefix = (int)$parts[0];
                    $lastNumber = (int)$parts[1];
                }
            }
            
            // Calculate next number
            $nextNumber = $lastNumber + 1;
            $nextPrefix = $lastPrefix;
            
            if ($nextNumber > 99999) {
                $nextPrefix = $lastPrefix + 1;
                $nextNumber = 1;
            }
            
            // Update sequence
            $sequence = InvoiceSequence::first();
            if (!$sequence) {
                $sequence = InvoiceSequence::create([
                    'prefix' => $nextPrefix,
                    'last_number' => $nextNumber
                ]);
            } else {
                $sequence->prefix = $nextPrefix;
                $sequence->last_number = $nextNumber;
                $sequence->save();
            }
            
            return $nextPrefix . '-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        });
    }
    
    public static function getCurrent()
    {
        $lastBooking = DB::table('bookings')->orderBy('id', 'desc')->first();
        
        if ($lastBooking && $lastBooking->invoice_no) {
            return $lastBooking->invoice_no;
        }
        // CHANGE HERE: Return 1-50000 instead of 1-00000
        return '1-50000';
    }
    
    public static function getNext()
    {
        $lastBooking = DB::table('bookings')->orderBy('id', 'desc')->first();
        
        $lastPrefix = 1;
        // CHANGE HERE: Set default to 50000 instead of 0
        $lastNumber = 50000;  // ← YAHAN CHANGE KIYA
        
        if ($lastBooking && $lastBooking->invoice_no) {
            $parts = explode('-', $lastBooking->invoice_no);
            if (count($parts) == 2) {
                $lastPrefix = (int)$parts[0];
                $lastNumber = (int)$parts[1];
            }
        }
        
        $nextNumber = $lastNumber + 1;
        $nextPrefix = $lastPrefix;
        
        if ($nextNumber > 99999) {
            $nextPrefix = $lastPrefix + 1;
            $nextNumber = 1;
        }
        
        return $nextPrefix . '-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}