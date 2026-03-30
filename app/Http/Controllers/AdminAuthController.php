<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    // 1. Hiển thị form đăng nhập riêng của Admin
    public function showLoginForm()
    {
        // Nếu Admin đang đăng nhập sẵn rồi thì đá thẳng vào trong luôn, khỏi bắt đăng nhập lại
        if (Auth::check() && (Auth::user()->role == 'admin' || Auth::user()->role == 1)) {
            return redirect('/admin/orders');
        }
        
        return view('admin.login');
    }

    // 2. Xử lý kiểm tra tài khoản
    public function login(Request $request)
    {
        // Kiểm tra xem có nhập email và pass không
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Thử đăng nhập bằng dữ liệu vừa nhập
        if (Auth::attempt($credentials)) {
            
            // KIỂM TRA QUYỀN: Nếu đăng nhập đúng, kiểm tra tiếp cột 'role' xem có phải admin không?
            // (Tùy Database của bạn lưu admin là chữ 'admin' hay số 1, tôi viết cả 2 cho chắc ăn)
            if (Auth::user()->role == 'admin' || Auth::user()->role == 1) {
                return redirect('/admin/orders')->with('success', '👑 Chào mừng Sếp quay trở lại!');
            }

            // NẾU LÀ KHÁCH HÀNG MÀ DÁM MÒ VÀO TRANG ADMIN -> Đuổi ra, bắt đăng xuất ngay!
            Auth::logout();
            return back()->withErrors(['email' => '❌ CẢNH BÁO: Tài khoản của bạn không có quyền Quản trị viên!']);
        }

        // Nếu sai email hoặc mật khẩu
        return back()->withErrors(['email' => '❌ Sai email hoặc mật khẩu quản trị!']);
    }

    // 3. Đăng xuất của Admin
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/admin/login');
    }
}