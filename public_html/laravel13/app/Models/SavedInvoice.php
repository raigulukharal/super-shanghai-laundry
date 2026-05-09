<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedInvoice extends Model
{
    use HasFactory;

    protected $table = 'saved_invoices';

    protected $fillable = [
        'booking_id', 'invoice_no', 'file_name', 'file_path', 
        'file_size', 'download_count', 'created_by'
    ];

    protected $casts = [
        'download_count' => 'integer',
        'file_size' => 'integer'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}