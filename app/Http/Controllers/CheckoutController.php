<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\ProductVariant; // Thêm Model này để trừ kho phân loại

class CheckoutController extends Controller
{
    // 1. Mở trang Giao diện Thanh toán
    public function index(Request $request)
    {
        $selectedCarts = $request->selected_carts;

        if(!$selectedCarts || empty($selectedCarts)) {
            return redirect('/cart')->withErrors(['❌ Vui lòng chọn ít nhất 1 sản phẩm để thanh toán!']);
        }

        // BỔ SUNG: Phải có 'variant' trong with để hiện màu sắc và ảnh màu
        $cartItems = Cart::with(['product', 'variant']) 
                    ->whereIn('id', $selectedCarts)
                    ->where('user_id', auth()->id())
                    ->get();

        if($cartItems->isEmpty()) {
            return redirect('/cart')->withErrors(['❌ Dữ liệu giỏ hàng không hợp lệ!']);
        }

        return view('checkout.index', compact('cartItems'));
    }

    // 2. Xử lý Đặt hàng
    public function process(Request $request)
    {
        $trang_thai = ($request->payment_method == 'ONLINE') ? 'unpaid' : 'pending';

        // Tạo Đơn hàng
        $order = Order::create([
            'user_id' => auth()->id(),
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'shipping_address' => $request->shipping_address,
            'notes' => $request->notes,
            'payment_method' => $request->payment_method,
            'total_amount' => $request->total_amount,
            'status' => $trang_thai 
        ]);

        // Vòng lặp lưu chi tiết và TRỪ KHO THÔNG MINH
        foreach($request->cart_ids as $cart_id) {
            // Phải kéo cả variant để biết trừ kho ở đâu
            $cartItem = Cart::with(['product', 'variant'])->find($cart_id);

            if($cartItem && $cartItem->product) {
                // Lưu vào OrderDetail (Nhớ thêm variant_id nếu bảng OrderDetail của bạn có cột này)
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'variant_id' => $cartItem->variant_id, // Lưu ID màu sắc vào đơn hàng
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->product->sale_price ?? $cartItem->product->price
                ]);

                // --- LOGIC TRỪ KHO ---
                
                // 1. Trừ kho của đúng Phân loại (Màu sắc)
                if($cartItem->variant_id) {
                    ProductVariant::where('id', $cartItem->variant_id)
                                  ->decrement('stock_quantity', $cartItem->quantity);
                }

                // 2. Trừ kho tổng của Sản phẩm (Để đồng bộ số liệu)
                $cartItem->product->decrement('stock_quantity', $cartItem->quantity);

                // Xóa món này khỏi giỏ hàng
                $cartItem->delete();
            }
        }

        if ($request->payment_method == 'ONLINE') {
            return redirect('/checkout/payment/' . $order->id);
        }

        return redirect('/profile/orders')->with('success', '🎉 Đặt hàng thành công! Vui lòng chờ giao hàng.');
    }

    public function payment($id)
    {
        $order = Order::with('details.product', 'details.variant')->findOrFail($id);
        if($order->user_id != auth()->id()) abort(403); 
        
        return view('checkout.payment', compact('order'));
    }
}