<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Xem danh sách toàn bộ đơn hàng
    // Xem danh sách toàn bộ đơn hàng
    public function index()
    {
        // Thêm `with('details.product')` để lôi luôn các món đồ và ảnh sản phẩm ra cho Popup
        $orders = Order::with('details.product')->orderBy('id', 'desc')->get();
        return view('admin.orders.index', compact('orders'));
    }

    // Cập nhật trạng thái đơn hàng (Duyệt, Giao, Hủy)
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $order->update([
            'status' => $request->status
        ]);

        return back()->with('success', 'Đã cập nhật trạng thái đơn hàng #' . $id . ' thành công!');
    }
}