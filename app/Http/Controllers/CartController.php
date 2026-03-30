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
        $cartItems = Cart::with('product')->where('user_id', auth()->id())->get();
        return view('cart.index', compact('cartItems'));
    }

    // 2. Thêm vào giỏ hàng
    public function add($id)
    {
        $product = Product::findOrFail($id);
        $userId = auth()->id();

        // Kiểm tra xem món này đã có trong giỏ của user chưa?
        $cartItem = Cart::where('user_id', $userId)->where('product_id', $id)->first();

        if($cartItem) {
            // Có rồi thì cộng thêm 1
            $cartItem->increment('quantity');
        } else {
            // Chưa có thì tạo mới
            Cart::create([
                'user_id' => $userId,
                'product_id' => $id,
                'quantity' => 1
            ]);
        }
        return back()->with('success', ' Đã thêm ' . $product->name . ' vào giỏ!');
    }

    // 3. Cập nhật số lượng
    // 3. Cập nhật số lượng
    public function update(Request $request)
    {
        // Lấy giỏ hàng kèm theo thông tin sản phẩm
        $cartItem = Cart::with('product')->where('id', $request->cart_id)->where('user_id', auth()->id())->first();
        
        if($cartItem && $request->quantity > 0) {
            // KIỂM TRA: Nếu khách nhập số lượng lớn hơn trong kho -> Báo lỗi
            if($request->quantity > $cartItem->product->stock_quantity) {
                return back()->withErrors(['❌ Số lượng bạn chọn (' . $request->quantity . ') vượt quá số lượng còn lại trong kho (' . $cartItem->product->stock_quantity . ')!']);
            }

            // Nếu hợp lệ thì cho phép lưu
            $cartItem->update(['quantity' => $request->quantity]);
            return back()->with('success', '🔄 Đã cập nhật số lượng thành công!');
        }
    }

    // 4. Xóa món
    public function remove(Request $request)
    {
        Cart::where('id', $request->cart_id)->where('user_id', auth()->id())->delete();
        return back()->with('success', ' Đã xóa sản phẩm khỏi giỏ!');
    }
}