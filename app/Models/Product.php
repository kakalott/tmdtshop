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
    ];
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}