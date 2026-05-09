<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryItem extends Model
{
    use HasFactory;

    protected $table = 'delivery_items';

    protected $fillable = [
        'delivery_id', 'booking_item_id', 'quantity_delivered',
        'unit_price_at_delivery', 'total'
    ];

    public $timestamps = true;

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function bookingItem()
    {
        return $this->belongsTo(BookingItem::class, 'booking_item_id');
    }
}