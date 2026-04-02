<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('sort_order')->latest()->get();
        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        // 1. Sửa validation: 'image' giờ là chuỗi (string) bắt buộc, có thể thêm kiểm tra định dạng 'url'
        $request->validate([
            'title' => 'nullable|string|max:255',
            'image' => 'required|string', // Yêu cầu nhập link ảnh
            'link' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        // 2. Lưu trực tiếp chuỗi link ảnh vào database
        Banner::create([
            'title' => $request->title,
            'image' => $request->image, // Nhận thẳng link từ form
            'link' => $request->link,
            'is_active' => $request->has('is_active'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.banners.index')->with('success', 'Thêm banner thành công.');
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        // 1. Sửa validation: 'image' là chuỗi, có thể bỏ trống (nếu không muốn đổi link mới)
        $request->validate([
            'title' => 'nullable|string|max:255',
            'image' => 'nullable|string', 
            'link' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        // 2. Nếu khách hàng có nhập link mới thì lấy link mới, không thì giữ link cũ
        $imagePath = $request->image ? $request->image : $banner->image;

        $banner->update([
            'title' => $request->title,
            'image' => $imagePath, // Cập nhật link ảnh
            'link' => $request->link,
            'is_active' => $request->has('is_active'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.banners.index')->with('success', 'Cập nhật banner thành công.');
    }

    public function destroy(Banner $banner)
    {
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'Xóa banner thành công.');
    }
}