<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;

class CheckoutController extends Controller
{
    // Hàm mở trang Giao diện Thanh toán
    public function index(Request $request)
    {
        // 1. Lấy mảng ID các món khách vừa tick (từ thanh URL)
        $selectedCarts = $request->selected_carts;

        // 2. Nếu khách cố tình gõ thẳng link /checkout mà không tick món nào
        if(!$selectedCarts || empty($selectedCarts)) {
            return redirect('/cart')->withErrors(['❌ Vui lòng chọn ít nhất 1 sản phẩm để thanh toán!']);
        }

        // 3. Lấy chi tiết các món trong giỏ hàng từ Database dựa vào mảng ID vừa tick
        $cartItems = Cart::with('product')
                    ->whereIn('id', $selectedCarts)
                    ->where('user_id', auth()->id())
                    ->get();

        if($cartItems->isEmpty()) {
            return redirect('/cart')->withErrors(['❌ Dữ liệu giỏ hàng không hợp lệ!']);
        }

        // 4. Mở trang thanh toán và mang theo các món đồ này
        return view('checkout.index', compact('cartItems'));
    }
    public function process(Request $request)
    {
        // 1. Tự động xét trạng thái dựa trên phương thức thanh toán
        $trang_thai = ($request->payment_method == 'ONLINE') ? 'unpaid' : 'pending';

        // 2. Tạo Đơn hàng mới
        $order = Order::create([
            'user_id' => auth()->id(),
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'shipping_address' => $request->shipping_address,
            'notes' => $request->notes,
            'payment_method' => $request->payment_method,
            'total_amount' => $request->total_amount,
            'status' => $trang_thai // SỬA DÒNG NÀY (Lưu 'unpaid' hoặc 'pending')
        ]);

        // (Phần lưu OrderDetail, trừ kho, xóa Giỏ hàng... giữ nguyên như cũ)
        foreach($request->cart_ids as $cart_id) {
            $cartItem = \App\Models\Cart::with('product')->find($cart_id);
            if($cartItem && $cartItem->product) {
                \App\Models\OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->product->sale_price ?? $cartItem->product->price
                ]);
                $cartItem->product->decrement('stock_quantity', $cartItem->quantity);
                $cartItem->delete();
            }
        }

        // 3. Phân luồng Thanh Toán
        if ($request->payment_method == 'ONLINE') {
            return redirect('/checkout/payment/' . $order->id);
        }

        // Chuyển về trang Lịch sử đơn hàng
        return redirect('/profile/orders')->with('success', '🎉 Đặt hàng thành công! Vui lòng chờ giao hàng.');
    }
    public function payment($id)
    {
        $order = Order::findOrFail($id);
        // Đảm bảo chỉ chính chủ mới được xem hóa đơn này
        if($order->user_id != auth()->id()) abort(403); 
        
        return view('checkout.payment', compact('order'));
    }
}