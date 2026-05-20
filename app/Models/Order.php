<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 
        'customer_name', 
        'customer_phone', 
        'shipping_address', 
        'notes', 
        'payment_method', 
        'subtotal_amount',
        'voucher_id',
        'voucher_code',
        'discount_amount',
        'total_amount', 
        'status'
    ];

    public function details()
    {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }
}
