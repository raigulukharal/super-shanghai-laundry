<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClothType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 
        'base_price', 
        'is_active',
        'category_id'  // Add category_id field
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'base_price' => 'decimal:2'
    ];

    public function bookingItems()
    {
        return $this->hasMany(BookingItem::class);
    }

    // Add relationship with category
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}