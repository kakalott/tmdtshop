<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Hiển thị danh sách người dùng
    public function index()
    {
        // Chặn cửa: Nếu không phải admin thì đá văng ra Trang chủ
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Chỉ Admin tối cao mới có quyền vào đây!');
        }

        $users = User::all();
        return view('users.index', compact('users'));
    }

    // Xử lý nút bấm "Cập nhật chức vụ"
    public function updateRole(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $user = User::findOrFail($id);
        $user->update(['role' => $request->role]);

        return back()->with('success', 'Đã thăng chức cho ' . $user->name . ' thành công!');
    }
}
