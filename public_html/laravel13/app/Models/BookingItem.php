<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingItem extends Model
{
    use HasFactory;

    protected $fillable = [
    'booking_id', 'cloth_type_id', 'color_id', 'category_id',
    'quantity', 'unit_price', 'total', 'delivery_type',
    'expected_delivery_date', 'delivered_quantity', 'status'
];

    protected $casts = [
        'expected_delivery_date' => 'date',
        'delivered_quantity' => 'integer'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function clothType()
    {
        return $this->belongsTo(ClothType::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function deliveryItems()
    {
        return $this->hasMany(DeliveryItem::class);
    }

    public function getRemainingQuantityAttribute()
    {
        return $this->quantity - ($this->delivered_quantity ?? 0);
    }
}