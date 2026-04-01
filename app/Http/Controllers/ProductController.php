<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product; 
use App\Models\Category;
use App\Models\ProductVariant;

class ProductController extends Controller
{
    // Hàm 1: Hiển thị giao diện Form thêm mới
    public function create()
    {
        $categories = Category::all();
        // Sửa lại đường dẫn view cho chuẩn thư mục admin nhé
        return view('products.create', compact('categories'));
    }

    // Hàm 2: Nhận dữ liệu từ Form và lưu vào Database
    public function store(Request $request)
    {
        // 1. Luôn tính tổng Tồn kho từ bảng phân loại (Vì form luôn gửi lên ít nhất 1 dòng Mặc định)
        $totalStock = 0;
        if ($request->has('variants')) {
            $totalStock = collect($request->variants)->sum('stock_quantity');
        }

        // 2. Lưu VỎ sản phẩm
        $product = Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'wholesale_price' => $request->wholesale_price ?? 0,
            'stock_quantity' => $totalStock, // Tự động chốt số từ dưới lên
            'image' => $request->image,
            'category_id' => $request->category_id,
            'description' => $request->description,
        ]);

        // 3. Lưu CHI TIẾT các Màu sắc / Phân loại
        if ($request->has('variants')) {
            foreach ($request->variants as $variant) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'size' => null,
                    'color' => $variant['color'] ?? 'Mặc định', // Nếu lỡ để trống thì gán là Mặc định
                    'price' => $request->price,                 // Copy giá lẻ xuống
                    'stock_quantity' => $variant['stock_quantity'] ?? 0,
                    'image' => $variant['image'] ?? null,
                ]);
            }
        }
        
        return redirect('/admin/products')->with('success', ' Đã thêm sản phẩm thành công!');
    }

    // Hàm 3: Hiển thị danh sách sản phẩm
    public function index()
    {
        $products = Product::orderBy('id', 'desc')->get();
        return view('products.index', compact('products'));
    }

    // Hàm 4: Xóa sản phẩm
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete(); // Lệnh này cũng sẽ tự xóa luôn các ProductVariant nhờ khóa ngoại onDelete('cascade')
        
        return back()->with('success', ' Đã xóa sản phẩm thành công!');
    }

    // Hàm 5: Hiển thị form Sửa (kèm dữ liệu cũ của sản phẩm)
    public function edit($id)
    {
        $product = Product::with('variants')->findOrFail($id);
        $categories = Category::all();

        return view('products.edit', compact('product', 'categories'));
    }

    // Hàm 6: Nhận dữ liệu mới và Lưu đè vào Database
    public function update(Request $request, $id)
    {
        // 1. TÌM SẢN PHẨM CŨ
        $product = Product::findOrFail($id);

        // 2. Tính lại tổng Tồn kho từ bảng phân loại mới gửi lên
        $totalStock = 0;
        if ($request->has('variants')) {
            $totalStock = collect($request->variants)->sum('stock_quantity');
        }

        // 3. CẬP NHẬT (UPDATE) VỎ SẢN PHẨM CŨ
        $product->update([
            'name' => $request->name,
            'price' => $request->price,
            'wholesale_price' => $request->wholesale_price ?? 0,
            'stock_quantity' => $totalStock, 
            'image' => $request->image,
            'category_id' => $request->category_id,
            'description' => $request->description,
        ]);

        // 4. XỬ LÝ CHI TIẾT MÀU SẮC (Dọn rác cũ -> Thêm mới vào)
        if ($request->has('variants')) {
            // Xóa sạch các màu cũ của sản phẩm này để tránh bị nhân đôi
            ProductVariant::where('product_id', $product->id)->delete();
            
            // Lưu lại danh sách màu mới
            foreach ($request->variants as $variant) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'size' => null,
                    'color' => $variant['color'] ?? 'Mặc định',
                    'price' => $request->price,
                    'stock_quantity' => $variant['stock_quantity'] ?? 0,
                    'image' => $variant['image'] ?? null,
                ]);
            }
        }

        return redirect('/admin/products')->with('success', ' Đã cập nhật sản phẩm thành công!');
    }
}