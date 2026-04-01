<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    // Bắt buộc phải có để được phép lưu vào DB
    protected $fillable = ['product_id', 'user_id', 'rating', 'comment'];

    // 1 Đánh giá thuộc về 1 Người dùng (Khách hàng)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}