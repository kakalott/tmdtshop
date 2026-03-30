<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Product;
use App\Models\Category; // Gọi Model Category
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {

    {
        // Banner đang bật
        $banners = Banner::where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        // 1. Lấy toàn bộ danh mục từ bảng categories để đổ ra Cột trái
        $categories = Category::all();
        
        // ... (Các đoạn code lấy sản phẩm ở dưới bạn giữ nguyên nhé)
        $categories = Category::all();
        
        // 2. Chuẩn bị câu lệnh lấy Sản phẩm (Chỉ lấy hàng còn trong kho)
        $query = Product::where('stock_quantity', '>', 0);

        // 3. Xử lý TÌM KIẾM (Nếu khách gõ vào ô tìm kiếm)
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // 4. Xử lý LỌC DANH MỤC (ĐÂY LÀ PHẦN CHÚNG TA VỪA THÊM)
        // Nếu trên thanh địa chỉ có chữ ?category=... thì lọc theo mã đó
        if ($request->has('category') && $request->category != '') {
            $query->where('category_id', $request->category);
        }

        // Thực thi câu lệnh và lấy dữ liệu
        $products = $query->orderBy('id', 'desc')->get();
        
// Gửi các biến products, categories và banners ra ngoài Giao diện
        return view('home', compact('products', 'categories', 'banners'));
    }
}
}