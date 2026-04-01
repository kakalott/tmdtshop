<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    // Bổ sung 'image' vào cuối mảng này
    protected $fillable = ['product_id', 'size', 'color', 'price', 'stock_quantity', 'image'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}