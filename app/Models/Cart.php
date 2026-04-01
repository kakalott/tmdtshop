<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['user_id', 'product_id', 'quantity','variant_id'];

    // Giỏ hàng này chứa Sản phẩm nào?
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function variant()
{
    // Nếu bạn lưu khóa ngoại là variant_id trong bảng carts
    return $this->belongsTo(ProductVariant::class, 'variant_id'); 
}
}