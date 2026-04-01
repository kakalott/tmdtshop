<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductVariant; // Thêm Model này để xử lý kho phân loại
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Hàm 1: Liệt kê toàn bộ đơn hàng cho Admin
    public function index()
    {
        // Load thêm details, product và variant để Admin xem được khách mua màu gì
        $orders = Order::with(['details.product', 'details.variant'])
                       ->orderBy('id', 'desc')
                       ->get();
                       
        return view('orders.index', compact('orders'));
    }

    // Hàm 2: Cập nhật trạng thái đơn hàng (Duyệt, Giao, Hủy)
    public function updateStatus(Request $request, $id)
    {
        // Lấy đơn hàng kèm chi tiết VÀ phân loại để biết đường cộng lại đúng kho màu
        $order = Order::with(['details.product', 'details.variant'])->findOrFail($id);
        
        $oldStatus = $order->status;
        $newStatus = $request->status;

        // KIỂM TRA QUAN TRỌNG: Nếu Admin chuyển sang HỦY ĐƠN (và trước đó đơn chưa bị hủy) -> Trả lại kho
        if ($newStatus == 'cancelled' && $oldStatus != 'cancelled') {
            foreach ($order->details as $detail) {
                // 1. Cộng lại kho của đúng Phân loại (Màu sắc)
                if ($detail->variant_id) {
                    ProductVariant::where('id', $detail->variant_id)
                                  ->increment('stock_quantity', $detail->quantity);
                }

                // 2. Cộng lại kho tổng của Sản phẩm
                if ($detail->product) {
                    $detail->product->increment('stock_quantity', $detail->quantity);
                }
            }
        }

        // Cập nhật trạng thái mới
        $order->update([
            'status' => $newStatus
        ]);

        return back()->with('success', '✅ Đã cập nhật trạng thái đơn hàng #' . $id . ' thành công!');
    }
}