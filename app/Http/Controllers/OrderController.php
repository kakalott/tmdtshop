<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Hàm 1: Liệt kê toàn bộ đơn hàng
    public function index()
    {
        // Lấy đơn hàng mới nhất xếp lên đầu
        $orders = Order::orderBy('id', 'desc')->get();
        return view('orders.index', compact('orders'));
    }

    // Hàm 2: Cập nhật trạng thái đơn hàng (Đang xử lý -> Đã giao)
    // Cập nhật trạng thái đơn hàng (Duyệt, Giao, Hủy)
    public function updateStatus(Request $request, $id)
    {
        // Lấy đơn hàng kèm chi tiết sản phẩm để lỡ hủy thì còn biết đường cộng lại kho
        $order = Order::with('details.product')->findOrFail($id);
        
        $oldStatus = $order->status;
        $newStatus = $request->status;

        // KIỂM TRA QUAN TRỌNG: Nếu Admin chuyển sang HỦY ĐƠN (và trước đó đơn chưa bị hủy) -> Trả lại kho
        if ($newStatus == 'cancelled' && $oldStatus != 'cancelled') {
            foreach ($order->details as $detail) {
                if ($detail->product) {
                    $detail->product->increment('stock_quantity', $detail->quantity);
                }
            }
        }

        // Cập nhật trạng thái mới
        $order->update([
            'status' => $newStatus
        ]);

        return back()->with('success', ' Đã cập nhật luồng trạng thái đơn hàng #' . $id . ' thành công!');
    }
}