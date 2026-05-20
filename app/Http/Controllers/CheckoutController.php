<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\ProductVariant; // Thêm Model này để trừ kho phân loại
use App\Services\VnpayService;

class CheckoutController extends Controller
{
    protected $vnpayService;

    public function __construct(VnpayService $vnpayService)
    {
        $this->vnpayService = $vnpayService;
    }

    // 1. Mở trang Giao diện Thanh toán
    public function index(Request $request)
    {
        $order = null;
        $payUrl = null;

        if ($request->order_id) {
            $order = Order::with('details.product', 'details.variant')->findOrFail($request->order_id);
            if ($order->user_id != auth()->id()) {
                abort(403);
            }

            if ($order->status === 'unpaid' && $order->payment_method === 'ONLINE' && config('vnpay.enabled')) {
                $payUrl = $this->vnpayService->createPayment($order);
            }

            $cartItems = collect();
        } else {
            $selectedCarts = $request->selected_carts;

            if (!$selectedCarts || empty($selectedCarts)) {
                return redirect('/cart')->withErrors([' Vui lòng chọn ít nhất 1 sản phẩm để thanh toán!']);
            }

            // BỔ SUNG: Phải có 'variant' trong with để hiện màu sắc và ảnh màu
            $cartItems = Cart::with(['product', 'variant'])
                        ->whereIn('id', $selectedCarts)
                        ->where('user_id', auth()->id())
                        ->get();

            if ($cartItems->isEmpty()) {
                return redirect('/cart')->withErrors([' Dữ liệu giỏ hàng không hợp lệ!']);
            }
        }

        return view('checkout.index', compact('cartItems', 'order', 'payUrl'));
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

        foreach($request->cart_ids as $cart_id) {
            $cartItem = Cart::with(['product', 'variant'])->find($cart_id);

            // Bắt đầu kiểm tra nếu có sản phẩm
            if($cartItem && $cartItem->product) {
                
                // Tự động lấy giá sỉ nếu mua từ 10 món trở lên
                $finalPrice = $cartItem->product->getPriceByQuantity($cartItem->quantity);
                
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'variant_id' => $cartItem->variant_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $finalPrice // Lưu giá đã qua xử lý sỉ/lẻ
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
                
            } // KẾT THÚC LỆNH IF Ở ĐÂY MỚI ĐÚNG
        } // KẾT THÚC VÒNG LẶP FOREACH

        if ($request->payment_method == 'ONLINE') {
            if (config('vnpay.enabled')) {
                return redirect('/checkout?order_id=' . $order->id);
            }
            return redirect('/checkout/payment/' . $order->id);
        }

        return redirect('/profile/orders')->with('success', ' Đặt hàng thành công! Vui lòng chờ giao hàng.');
    }

    // 3. Xử lý Thanh toán
    public function payment($id)
    {
        $order = Order::with('details.product', 'details.variant')->findOrFail($id);
        if($order->user_id != auth()->id()) abort(403);

        if ($order->status === 'unpaid' && $order->payment_method === 'ONLINE') {
            return redirect('/checkout?order_id=' . $order->id);
        }

        $payUrl = null;
        if ($order->status === 'unpaid' && $order->payment_method === 'ONLINE' && config('vnpay.enabled')) {
            try {
                $payUrl = $this->vnpayService->createPayment($order);
            } catch (\Exception $e) {
                $payUrl = null;
            }
        }

        return view('checkout.payment', compact('order', 'payUrl'));
    }
    public function paymentStart($id)
    {
        $order = Order::findOrFail($id);
        if ($order->user_id != auth()->id()) abort(403);
        if ($order->status !== 'unpaid' || $order->payment_method !== 'ONLINE') {
            return redirect('/profile/orders')->with('error', 'Đơn hàng không hợp lệ để thanh toán online.');
        }

        if (!config('vnpay.enabled')) {
            return redirect('/checkout/payment/' . $order->id)->with('error', 'VNPay chưa được cấu hình.');
        }

        try {
            $payUrl = $this->vnpayService->createPayment($order);
            return redirect()->away($payUrl);
        } catch (\Exception $e) {
            return redirect('/checkout/payment/' . $order->id)->with('error', 'Lỗi VNPay: ' . $e->getMessage());
        }
    }

    public function vnpayReturn(Request $request)
    {
        if (config('vnpay.sandbox_mode')) {
            $orderId = $request->order_id ?? null;
        } else {
            $orderId = $this->vnpayService->extractOrderId($request->vnp_TxnRef ?? '');
        }

        $order = Order::find($orderId);
        if (!$order) {
            return redirect('/profile/orders')->with('error', 'Đơn hàng không tồn tại.');
        }

        $success = false;
        if (config('vnpay.sandbox_mode')) {
            $success = true;
        } else {
            $success = isset($request->vnp_ResponseCode) && $request->vnp_ResponseCode === '00';
        }

        if ($success) {
            $order->update(['status' => 'paid']);
            return redirect('/profile/orders')->with('success', 'Thanh toán VNPay thành công cho đơn #' . $order->id);
        }

        $message = $request->vnp_Message ?? $request->vnp_ResponseCode ?? 'Thanh toán không thành công.';
        return redirect('/profile/orders')->with('error', 'Thanh toán VNPay không thành công: ' . $message);
    }

    public function vnpayNotify(Request $request)
    {
        $payload = $request->all();

        if (config('vnpay.sandbox_mode')) {
            return response()->json(['RspCode' => '00', 'Message' => 'OK']);
        }

        if (!$this->vnpayService->verifySignature($payload)) {
            return response()->json(['RspCode' => '97', 'Message' => 'Invalid signature']);
        }

        $orderId = $this->vnpayService->extractOrderId($payload['vnp_TxnRef'] ?? '');
        $order = Order::find($orderId);
        if (!$order) {
            return response()->json(['RspCode' => '01', 'Message' => 'Order not found']);
        }

        if (isset($payload['vnp_ResponseCode']) && $payload['vnp_ResponseCode'] === '00') {
            $order->update(['status' => 'paid']);
        }

        return response()->json(['RspCode' => '00', 'Message' => 'OK']);
    }

    public function vnpaySandbox($id)
    {
        $order = Order::findOrFail($id);
        if ($order->user_id != auth()->id()) abort(403);
        return view('vnpay.sandbox', compact('order'));
    }

    public function vnpaySandboxPay($id)
    {
        $order = Order::findOrFail($id);
        if ($order->user_id != auth()->id()) abort(403);
        $order->update(['status' => 'paid']);

        return redirect('/profile/orders')->with('success', 'Thanh toán VNPay sandbox thành công cho đơn #' . $order->id);
    }
}
