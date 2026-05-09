<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceRange extends Model
{
    protected $fillable = [
        'range_name', 'start_invoice', 'end_invoice', 'range_date', 'description'
    ];

    protected $casts = [
        'range_date' => 'date'
    ];

    public function shopInvoices(): HasMany
    {
        return $this->hasMany(ShopInvoice::class);
    }
}