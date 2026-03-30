<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }
    // Hàm này chạy ngay sau khi gõ đúng Email và Pass ở trang /login của Khách
    // Hàm này chạy ngay sau khi gõ đúng Email và Pass ở trang /login của Khách
    protected function authenticated(\Illuminate\Http\Request $request, $user)
    {
        // KIỂM TRA: Nếu phát hiện đây là Admin mà dám đi cửa của Khách
        if ($user->role == 'admin' || $user->role == 1) {
            
            // 1. Lập tức Đăng xuất (Tịch thu vé)
            \Illuminate\Support\Facades\Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // 2. Đá ngược lại đúng trang đăng nhập của Khách và hiện thông báo đỏ (KHÔNG chuyển hướng đi đâu cả)
            return redirect('/login')->withErrors(['email' => ' Tài khoản này là Quản trị viên! Vui lòng truy cập vào cổng đăng nhập dành riêng cho Admin.']);
        }

        // Nếu là Khách hàng bình thường thì vui vẻ mở cửa cho vào Trang chủ
        return redirect('/');
    }
}
