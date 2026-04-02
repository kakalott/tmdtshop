<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Khai báo danh sách các cột được phép lưu dữ liệu từ Form (Chống hack)
    protected $fillable = [
        'name', 
        'sku', 
        'barcode', 
        'price', 
        'wholesale_price', 
        'stock_quantity', 
        'dimensions', 
        'image', 
        'category_id',
        'description',
    ];
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    // 1 Sản phẩm có nhiều Đánh giá
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // 1 Sản phẩm có nhiều Biến thể (Màu/Size)
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
    public function getPriceByQuantity($quantity)
{
    // Nếu có nhập giá sỉ và số lượng >= 10
    if ($this->wholesale_price > 0 && $quantity >= 10) {
        return $this->wholesale_price;
    }
    // Ngược lại lấy giá bán lẻ (ưu tiên giá sale nếu có)
    return $this->sale_price ?? $this->price;
}
}