<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceSequence extends Model
{
    use HasFactory;

    protected $table = 'invoice_sequences';

    protected $fillable = [
        'prefix', 'last_number'
    ];

    public $timestamps = true;
}