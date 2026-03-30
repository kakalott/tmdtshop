<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    // Thêm 'slug' vào đây để cho phép lưu dữ liệu
    protected $fillable = ['name', 'slug']; 
}