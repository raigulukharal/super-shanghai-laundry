<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
       'invoice_no', 'customer_id', 'customer_code_used', 'booking_date',
    'expected_delivery_date', 'total_amount', 'discount', 'other_charges',
    'grand_total', 'paid_amount', 'payment_status', 'customer_notes',
    'status', 'cancelled_at', 'created_by'
    ];

    protected $casts = [
        'booking_date' => 'date',
        'cancelled_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(BookingItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatePaymentStatus()
    {
        $paid = $this->payments()->sum('amount');
        $this->paid_amount = $paid;
        
        if ($paid >= $this->grand_total) {
            $this->payment_status = 'full_pay';
        } elseif ($paid > 0) {
            $this->payment_status = 'partial_pay';
        } else {
            $this->payment_status = 'full_due';
        }
        
        $this->save();
    }

    public function updateDeliveryStatus()
    {
        $totalItems = $this->items->count();
        $deliveredItems = $this->items->where('status', 'delivered')->count();
        
        if ($deliveredItems == 0) {
            $this->status = 'pending';
        } elseif ($deliveredItems == $totalItems) {
            $this->status = 'delivered';
        } else {
            $this->status = 'partial_delivered';
        }
        
        $this->save();
    }
}