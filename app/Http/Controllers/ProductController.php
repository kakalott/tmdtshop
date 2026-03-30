<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product; // Nhúng model Product vào
use App\Models\Category;
class ProductController extends Controller
{
    // Hàm 1: Hiển thị giao diện Form thêm mới
    public function create()
    {
        // Lấy toàn bộ danh mục từ Database ra
        $categories = Category::all();
        
        // Ném sang bên giao diện Form
        return view('products.create', compact('categories'));
    }
    // Hàm 2: Nhận dữ liệu từ Form và lưu vào Database
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'stock_quantity' => 'required|integer',
            'category_id' => 'required|integer' // Bắt buộc phải chọn danh mục
        ]);

        Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'wholesale_price' => $request->wholesale_price,
            'stock_quantity' => $request->stock_quantity,
            'image' => $request->image,
            'category_id' => $request->category_id, // Lưu ID danh mục vào DB
            'description' => $request->description,
        ]);

        return redirect('/admin/products')->with('success', ' Đã thêm sản phẩm mới thành công!');
    }
    // Bổ sung Hàm 3: Hiển thị danh sách sản phẩm
    public function index()
    {
        // Lấy tất cả sản phẩm, sắp xếp cái nào mới thêm lên đầu
        $products = Product::orderBy('id', 'desc')->get();
        return view('products.index', compact('products'));
    }
    // Bổ sung Hàm 4: Xóa sản phẩm
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        
        return back()->with('success', 'Đã xóa sản phẩm thành công!');
    }
    // Hàm 5: Hiển thị form Sửa (kèm dữ liệu cũ của sản phẩm)
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        
        // Phải lấy thêm toàn bộ danh mục để đổ ra Menu thả xuống
        $categories = Category::all(); 
        
        // Ném cả biến $product và $categories sang Giao diện
        // (Lưu ý đường dẫn view của bạn đang là products.edit như hình bạn gửi lúc nãy nhé)
        return view('products.edit', compact('product', 'categories')); 
    }

    // Hàm 6: Nhận dữ liệu mới và Lưu đè vào Database TiDB
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'stock_quantity' => 'required|integer',
            'category_id' => 'required|integer' // Bắt buộc phải có danh mục
        ]);

        $product = Product::findOrFail($id);
        $product->update([
            'name' => $request->name,
            'price' => $request->price,
            'wholesale_price' => $request->wholesale_price,
            'stock_quantity' => $request->stock_quantity,
            'image' => $request->image,
            'category_id' => $request->category_id, // Lưu đè danh mục mới
            'description' => $request->description,
        ]);

        return redirect('/admin/products')->with('success', ' Đã cập nhật sản phẩm thành công!');
    }
}
