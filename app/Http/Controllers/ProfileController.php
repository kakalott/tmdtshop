<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    // Hiển thị trang thông tin cá nhân
    public function index()
    {
        // auth()->user() là ma thuật của Laravel giúp lấy ra toàn bộ thông tin của người đang đăng nhập
        $user = auth()->user(); 
        
        return view('profile.index', compact('user'));
    }
    // Mở Form chỉnh sửa thông tin
    public function edit()
    {
        $user = auth()->user();
        return view('profile.edit', compact('user'));
    }

    // Nhận dữ liệu từ Form và lưu đè vào Database
    public function update(Request $request)
    {
        // 1. Kiểm tra dữ liệu khách nhập vào
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        // 2. Tìm tài khoản đang đăng nhập và Cập nhật
        $user = auth()->user();
        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        // 3. Quay về trang Profile và báo thành công
        return redirect('/profile')->with('success', ' Đã cập nhật thông tin cá nhân thành công!');
    }
    // Xem lịch sử đơn hàng của tài khoản đang đăng nhập
    public function orders()
    {
        // Lấy các đơn hàng của user này, lấy kèm luôn chi tiết sản phẩm bên trong
        $orders = \App\Models\Order::with('details.product')
                    ->where('user_id', auth()->id())
                    ->orderBy('id', 'desc')
                    ->get();
                    
        return view('profile.orders', compact('orders'));
    }
    // Hàm Hủy đơn hàng cho Khách
    public function cancelOrder($id)
    {
        $order = \App\Models\Order::with('details.product')->findOrFail($id);

        // 1. Kiểm tra bảo mật: Chỉ chủ nhân đơn hàng mới được hủy
        if ($order->user_id != auth()->id()) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này!');
        }

        // 2. Chỉ cho phép hủy khi đơn ở trạng thái "pending" hoặc "unpaid"
        if (in_array($order->status, ['pending', 'unpaid'])) {
            
            // Đổi trạng thái thành Đã hủy
            $order->update(['status' => 'cancelled']);

            // TRẢ HÀNG VỀ KHO: Vòng lặp cộng lại số lượng vào bảng products
            foreach ($order->details as $detail) {
                if ($detail->product) {
                    $detail->product->increment('stock_quantity', $detail->quantity);
                }
            }

            return back()->with('success', ' Đã hủy đơn hàng #' . $id . ' thành công! Kho hàng đã được hoàn trả.');
        }

        // Nếu đơn đã giao hoặc hoàn thành thì báo lỗi
        return back()->withErrors([' Không thể hủy đơn hàng đang giao hoặc đã hoàn thành!']);
    }
}