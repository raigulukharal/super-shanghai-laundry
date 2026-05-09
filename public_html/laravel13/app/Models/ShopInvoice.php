<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopInvoice extends Model
{
    protected $fillable = [
        'invoice_range_id', 'invoice_no', 'status', 'type', 'is_missing', 'notes'
    ];

    protected $casts = [
        'is_missing' => 'boolean'
    ];

    public function invoiceRange(): BelongsTo
    {
        return $this->belongsTo(InvoiceRange::class);
    }

    /**
     * Get the booking associated with this shop invoice
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'invoice_no', 'invoice_no');
    }
}