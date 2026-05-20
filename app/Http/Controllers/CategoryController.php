<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    // Hiển thị danh sách và Form thêm mới
    public function index()
    {
        $categories = Category::orderBy('id', 'desc')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function edit($id)
    {
        $categories = Category::orderBy('id', 'desc')->get();
        $editingCategory = Category::findOrFail($id);

        return view('admin.categories.index', compact('categories', 'editingCategory'));
    }

    // Lưu danh mục mới vào Database
    // Lưu danh mục mới vào Database
    public function store(Request $request)
    {
        // Kiểm tra dữ liệu
        $request->validate(['name' => 'required|string|max:255|unique:categories,name']);
        
        // Tạo dữ liệu mới kèm tự động tạo slug (Ví dụ: "Hộp Nhựa" -> "hop-nhua")
        Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);
        
        return back()->with('success', '🗂️ Đã thêm danh mục thành công!');
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($category->id),
            ],
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return redirect('/admin/categories')->with('success', 'Đã cập nhật danh mục thành công!');
    }

    // Xóa danh mục
    public function destroy($id)
    {
        Category::destroy($id);
        return back()->with('success', '🗑️ Đã xóa danh mục!');
    }
}
