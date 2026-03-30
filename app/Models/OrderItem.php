<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'product_id', 'quantity', 'price'];

    // Chi tiết này thuộc về Sản phẩm nào? (Để lát lấy tên ghế nhựa ra xem)
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}