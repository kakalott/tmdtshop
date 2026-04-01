<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;

class CartController extends Controller
{
    // 1. Xem giỏ hàng (Chỉ lấy đồ của user đang đăng nhập)
    public function index()
    {
        $cartItems = Cart::with(['product', 'variant'])
            ->where('user_id', auth()->id())
            ->whereHas('product') // Chỉ lấy nếu sản phẩm vẫn tồn tại
            ->get();
        return view('cart.index', compact('cartItems'));
    }

    // 2. Thêm vào giỏ hàng (Giữ nguyên tên hàm add, thêm tham số Request)
    public function add(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $userId = auth()->id();
        
        // Hứng dữ liệu Màu sắc và Số lượng từ trang Chi tiết gửi lên (nếu không có số lượng thì mặc định là 1)
        $variantId = $request->variant_id;
        $quantity = $request->quantity ?? 1;

        // KIỂM TRA KÉP: Cùng User + Cùng Sản Phẩm + CÙNG MÀU SẮC
        $cartItem = Cart::where('user_id', $userId)
                        ->where('product_id', $id)
                        ->where('variant_id', $variantId)
                        ->first();

        if($cartItem) {
            // Có đúng cái màu đó rồi thì cộng dồn số lượng
            $cartItem->increment('quantity', $quantity);
        } else {
            // Chưa có màu này thì đẻ ra dòng mới trong giỏ
            Cart::create([
                'user_id' => $userId,
                'product_id' => $id,
                'variant_id' => $variantId, // Bổ sung lưu variant_id
                'quantity' => $quantity
            ]);
        }
        
       return back()->with('success', ' Đã thêm ' . $product->name . ' vào giỏ hàng thành công!');
    }

    // 3. Cập nhật số lượng
    public function update(Request $request)
    {
        // Lấy giỏ hàng kèm theo thông tin sản phẩm VÀ phân loại
        $cartItem = Cart::with(['product', 'variant'])->where('id', $request->cart_id)->where('user_id', auth()->id())->first();
        
        if($cartItem && $request->quantity > 0) {
            
            // Lấy số tồn kho thực tế của Màu đó (nếu có màu), nếu không thì lấy kho tổng
            $maxStock = $cartItem->variant ? $cartItem->variant->stock_quantity : $cartItem->product->stock_quantity;

            // KIỂM TRA: Nếu khách nhập số lượng lớn hơn trong kho của màu đó -> Báo lỗi
            if($request->quantity > $maxStock) {
                return back()->withErrors([' Số lượng bạn chọn (' . $request->quantity . ') vượt quá số lượng còn lại trong kho (' . $maxStock . ')!']);
            }

            // Nếu hợp lệ thì cho phép lưu
            $cartItem->update(['quantity' => $request->quantity]);
            return back()->with('success', ' Đã cập nhật số lượng thành công!');
        }
    }

    // 4. Xóa món
    public function remove(Request $request)
    {
        Cart::where('id', $request->cart_id)->where('user_id', auth()->id())->delete();
        return back()->with('success', ' Đã xóa sản phẩm khỏi giỏ!');
    }
}