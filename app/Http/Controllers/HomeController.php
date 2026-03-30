<?php

namespace App\Http\Controllers;

use App\Models\Product; // Nhúng model Product vào
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Lấy toàn bộ sản phẩm trong kho đẩy ra trang chủ
        $products = Product::all(); 
        
        // Truyền dữ liệu sang giao diện có tên là 'welcome'
        return view('welcome', compact('products'));
    }
}
