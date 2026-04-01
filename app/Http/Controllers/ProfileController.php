<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\ProductVariant; // Thêm Model này để hoàn kho phân loại

class ProfileController extends Controller
{
    // Hiển thị trang thông tin cá nhân (Giữ nguyên)
    public function index()
    {
        $user = auth()->user(); 
        return view('profile.index', compact('user'));
    }

    // Mở Form chỉnh sửa thông tin (Giữ nguyên)
    public function edit()
    {
        $user = auth()->user();
        return view('profile.edit', compact('user'));
    }

    // Nhận dữ liệu từ Form và lưu đè vào Database (Giữ nguyên)
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();
        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return redirect('/profile')->with('success', '✅ Đã cập nhật thông tin cá nhân thành công!');
    }

    // 1. CẬP NHẬT: Xem lịch sử đơn hàng
    public function orders()
    {
        // BỔ SUNG: Nạp thêm 'details.variant' để trang orders.blade.php hiện được tên màu
        $orders = Order::with(['details.product', 'details.variant'])
                    ->where('user_id', auth()->id())
                    ->orderBy('id', 'desc')
                    ->get();
                    
        return view('profile.orders', compact('orders'));
    }

    // 2. CẬP NHẬT: Hàm Hủy đơn hàng cho Khách
    public function cancelOrder($id)
    {
        // BỔ SUNG: Nạp thêm variant để biết đường hoàn kho đúng màu
        $order = Order::with(['details.product', 'details.variant'])->findOrFail($id);

        // Kiểm tra bảo mật
        if ($order->user_id != auth()->id()) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này!');
        }

        // Chỉ cho phép hủy khi đơn ở trạng thái "pending" hoặc "unpaid"
        if (in_array($order->status, ['pending', 'unpaid'])) {
            
            // Đổi trạng thái thành Đã hủy
            $order->update(['status' => 'cancelled']);

            // TRẢ HÀNG VỀ KHO (HOÀN KHO KÉP)
            foreach ($order->details as $detail) {
                // Bước 1: Cộng lại kho của đúng Phân loại (Màu sắc)
                if ($detail->variant_id) {
                    ProductVariant::where('id', $detail->variant_id)
                                  ->increment('stock_quantity', $detail->quantity);
                }

                // Bước 2: Cộng lại kho tổng của Sản phẩm (Để đồng bộ số liệu)
                if ($detail->product) {
                    $detail->product->increment('stock_quantity', $detail->quantity);
                }
            }

            return back()->with('success', ' Đã hủy đơn hàng #' . $id . ' thành công! Kho hàng đã được hoàn trả.');
        }

        return back()->withErrors([' Không thể hủy đơn hàng đang giao hoặc đã hoàn thành!']);
    }
}