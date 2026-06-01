<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\ChatConversation;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use App\Models\VoucherUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        if ((int) $id === auth()->id()) {
            return back()->withErrors(['user' => 'Bạn không thể xóa chính tài khoản đang đăng nhập.']);
        }

        $user = User::findOrFail($id);

        if ($user->role === 'admin' && User::where('role', 'admin')->where('id', '!=', $user->id)->count() === 0) {
            return back()->withErrors(['user' => 'Không thể xóa admin cuối cùng của hệ thống.']);
        }

        DB::transaction(function () use ($user) {
            Cart::where('user_id', $user->id)->delete();
            Review::where('user_id', $user->id)->delete();
            Order::where('user_id', $user->id)->update(['user_id' => null]);
            VoucherUsage::where('user_id', $user->id)->update(['user_id' => null]);
            ChatConversation::where('user_id', $user->id)->update(['user_id' => null]);

            $user->delete();
        });

        return back()->with('success', 'Đã xóa tài khoản ' . $user->name . ' thành công!');
    }
}
