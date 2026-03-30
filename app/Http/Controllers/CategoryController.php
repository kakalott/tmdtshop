<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class CategoryController extends Controller
{
    // Hiển thị danh sách và Form thêm mới
    public function index()
    {
        $categories = Category::orderBy('id', 'desc')->get();
        return view('admin.categories.index', compact('categories'));
    }

    // Lưu danh mục mới vào Database
    // Lưu danh mục mới vào Database
    public function store(Request $request)
    {
        // Kiểm tra dữ liệu
        $request->validate(['name' => 'required|string|max:255']);
        
        // Tạo dữ liệu mới kèm tự động tạo slug (Ví dụ: "Hộp Nhựa" -> "hop-nhua")
        Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);
        
        return back()->with('success', '🗂️ Đã thêm danh mục thành công!');
    }

    // Xóa danh mục
    public function destroy($id)
    {
        Category::destroy($id);
        return back()->with('success', '🗑️ Đã xóa danh mục!');
    }
}