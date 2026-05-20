<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\ProductVariant;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use App\Services\VnpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    protected $vnpayService;

    public function __construct(VnpayService $vnpayService)
    {
        $this->vnpayService = $vnpayService;
    }

    public function index(Request $request)
    {
        $order = null;
        $payUrl = null;
        $checkoutSummary = null;
        $voucherOptions = collect();

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
                return redirect('/cart')->withErrors(['Vui long chon it nhat 1 san pham de thanh toan!']);
            }

            $cartItems = Cart::with(['product', 'variant'])
                ->whereIn('id', $selectedCarts)
                ->where('user_id', auth()->id())
                ->whereHas('product')
                ->get();

            if ($cartItems->isEmpty()) {
                return redirect('/cart')->withErrors(['Du lieu gio hang khong hop le!']);
            }

            $checkoutSummary = $this->buildCheckoutSummary($cartItems, $request->voucher_code);
            $voucherOptions = $this->buildVoucherOptions((int) $checkoutSummary['subtotal']);
        }

        return view('checkout.index', compact('cartItems', 'order', 'payUrl', 'checkoutSummary', 'voucherOptions'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:30',
            'shipping_address' => 'required|string|max:1000',
            'payment_method' => 'required|in:COD,ONLINE',
            'cart_ids' => 'required|array|min:1',
            'cart_ids.*' => 'integer',
            'voucher_code' => 'nullable|string|max:50',
        ]);

        $status = ($request->payment_method == 'ONLINE') ? 'unpaid' : 'pending';
        $cartIds = array_unique($request->cart_ids);

        $cartItems = Cart::with(['product', 'variant'])
            ->whereIn('id', $cartIds)
            ->where('user_id', auth()->id())
            ->whereHas('product')
            ->get();

        if ($cartItems->isEmpty() || $cartItems->count() !== count($cartIds)) {
            return redirect('/cart')->withErrors(['Du lieu gio hang khong hop le.']);
        }

        $summary = $this->buildCheckoutSummary($cartItems, $request->voucher_code);

        if ($request->filled('voucher_code') && !$summary['voucher']) {
            return back()->withInput()->withErrors([$summary['voucher_error'] ?? 'Ma voucher khong hop le.']);
        }

        $order = DB::transaction(function () use ($request, $status, $cartItems, $summary) {
            $order = Order::create([
                'user_id' => auth()->id(),
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'shipping_address' => $request->shipping_address,
                'notes' => $request->notes,
                'payment_method' => $request->payment_method,
                'subtotal_amount' => $summary['subtotal'],
                'voucher_id' => $summary['voucher']?->id,
                'voucher_code' => $summary['voucher']?->code,
                'discount_amount' => $summary['discount'],
                'total_amount' => $summary['total'],
                'status' => $status,
            ]);

            foreach ($cartItems as $cartItem) {
                $finalPrice = $cartItem->product->getPriceByQuantity($cartItem->quantity);

                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'variant_id' => $cartItem->variant_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $finalPrice,
                ]);

                if ($cartItem->variant_id) {
                    ProductVariant::where('id', $cartItem->variant_id)
                        ->decrement('stock_quantity', $cartItem->quantity);
                }

                $cartItem->product->decrement('stock_quantity', $cartItem->quantity);
                $cartItem->delete();
            }

            if ($summary['voucher']) {
                VoucherUsage::create([
                    'voucher_id' => $summary['voucher']->id,
                    'user_id' => auth()->id(),
                    'order_id' => $order->id,
                    'discount_amount' => $summary['discount'],
                ]);

                $summary['voucher']->increment('used_count');
            }

            return $order;
        });

        if ($request->payment_method == 'ONLINE') {
            if (config('vnpay.enabled')) {
                return redirect('/checkout?order_id=' . $order->id);
            }

            return redirect('/checkout/payment/' . $order->id);
        }

        return redirect('/profile/orders')->with('success', 'Dat hang thanh cong! Vui long cho giao hang.');
    }

    public function payment($id)
    {
        $order = Order::with('details.product', 'details.variant')->findOrFail($id);
        if ($order->user_id != auth()->id()) {
            abort(403);
        }

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
        if ($order->user_id != auth()->id()) {
            abort(403);
        }

        if ($order->status !== 'unpaid' || $order->payment_method !== 'ONLINE') {
            return redirect('/profile/orders')->with('error', 'Don hang khong hop le de thanh toan online.');
        }

        if (!config('vnpay.enabled')) {
            return redirect('/checkout/payment/' . $order->id)->with('error', 'VNPay chua duoc cau hinh.');
        }

        try {
            $payUrl = $this->vnpayService->createPayment($order);
            return redirect()->away($payUrl);
        } catch (\Exception $e) {
            return redirect('/checkout/payment/' . $order->id)->with('error', 'Loi VNPay: ' . $e->getMessage());
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
            return redirect('/profile/orders')->with('error', 'Don hang khong ton tai.');
        }

        $success = false;
        if (config('vnpay.sandbox_mode')) {
            $success = true;
        } else {
            $success = isset($request->vnp_ResponseCode) && $request->vnp_ResponseCode === '00';
        }

        if ($success) {
            $order->update(['status' => 'paid']);
            return redirect('/profile/orders')->with('success', 'Thanh toan VNPay thanh cong cho don #' . $order->id);
        }

        $message = $request->vnp_Message ?? $request->vnp_ResponseCode ?? 'Thanh toan khong thanh cong.';
        return redirect('/profile/orders')->with('error', 'Thanh toan VNPay khong thanh cong: ' . $message);
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
        if ($order->user_id != auth()->id()) {
            abort(403);
        }

        return view('vnpay.sandbox', compact('order'));
    }

    public function vnpaySandboxPay($id)
    {
        $order = Order::findOrFail($id);
        if ($order->user_id != auth()->id()) {
            abort(403);
        }

        $order->update(['status' => 'paid']);

        return redirect('/profile/orders')->with('success', 'Thanh toan VNPay sandbox thanh cong cho don #' . $order->id);
    }

    private function buildCheckoutSummary($cartItems, ?string $voucherCode = null): array
    {
        $subtotal = 0;

        foreach ($cartItems as $item) {
            if (!$item->product) {
                continue;
            }

            $price = $item->product->getPriceByQuantity($item->quantity);
            $subtotal += $price * $item->quantity;
        }

        $voucher = null;
        $discount = 0;
        $voucherError = null;
        $voucherCode = strtoupper(trim((string) $voucherCode));

        if ($voucherCode !== '') {
            $voucher = Voucher::where('code', $voucherCode)->first();

            if (!$voucher) {
                $voucherError = 'Ma voucher khong ton tai.';
            } elseif (!$voucher->isAvailableForUser(auth()->id(), (int) $subtotal)) {
                $voucherError = 'Ma voucher khong du dieu kien ap dung.';
                $voucher = null;
            } else {
                $discount = $voucher->calculateDiscount((int) $subtotal);
            }
        }

        return [
            'subtotal' => (int) $subtotal,
            'voucher' => $voucher,
            'voucher_error' => $voucherError,
            'discount' => (int) $discount,
            'total' => max(0, (int) $subtotal - (int) $discount),
        ];
    }

    private function buildVoucherOptions(int $subtotal)
    {
        return Voucher::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->orderByDesc('id')
            ->get()
            ->map(function (Voucher $voucher) use ($subtotal) {
                $voucher->checkout_available = $voucher->isAvailableForUser(auth()->id(), $subtotal);
                $voucher->checkout_discount = $voucher->checkout_available ? $voucher->calculateDiscount($subtotal) : 0;
                $voucher->checkout_reason = $this->voucherUnavailableReason($voucher, $subtotal);

                return $voucher;
            });
    }

    private function voucherUnavailableReason(Voucher $voucher, int $subtotal): ?string
    {
        if ($subtotal < (int) $voucher->min_order_amount) {
            return 'Don hang chua dat toi thieu ' . number_format($voucher->min_order_amount, 0, ',', '.') . 'd';
        }

        if ($voucher->usage_limit !== null && $voucher->used_count >= $voucher->usage_limit) {
            return 'Voucher da het luot su dung';
        }

        if ($voucher->usage_limit_per_user !== null) {
            $usedByUser = $voucher->usages()->where('user_id', auth()->id())->count();
            if ($usedByUser >= $voucher->usage_limit_per_user) {
                return 'Ban da dung het luot cho voucher nay';
            }
        }

        return null;
    }
}
