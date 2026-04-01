<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    // CẬP NHẬT: Thêm variant_id vào đây
    protected $fillable = [
        'order_id', 
        'product_id', 
        'variant_id', // <--- BẮT BUỘC PHẢI CÓ DÒNG NÀY
        'quantity', 
        'price'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    // Quan hệ này giờ sẽ chạy mượt mà
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}