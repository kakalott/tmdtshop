<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BannerController;


Route::get('/', [ShopController::class, 'index']);
Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/products/create', [ProductController::class, 'create']); // Mở form
    Route::post('/admin/products/store', [ProductController::class, 'store']); // Bấm nút Lưu
    // Quản lý người dùng
Route::get('/admin/users', [UserController::class, 'index']);
Route::post('/admin/users/{id}/role', [UserController::class, 'updateRole']);
// Quản lý Sản phẩm
Route::get('/admin/products', [ProductController::class, 'index']); // Xem danh sách
Route::delete('/admin/products/{id}', [ProductController::class, 'destroy']); // Xóa
Route::get('/admin/products/{id}/edit', [ProductController::class, 'edit']); // Mở form sửa
Route::put('/admin/products/{id}', [ProductController::class, 'update']); // Bấm lưu đè
// Quản lý Đơn hàng
    Route::get('/admin/orders', [OrderController::class, 'index']); // Xem danh sách
    Route::post('/admin/orders/{id}/status', [OrderController::class, 'updateStatus']);
// Bảng điều khiển Thống kê (Dashboard)
    Route::get('/admin/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index']);
    // Chỉnh sửa hồ sơ cá nhân
    Route::get('/profile/edit', [ProfileController::class, 'edit']); // Mở form
    Route::put('/profile/update', [ProfileController::class, 'update']); // Bấm lưu đè
    // Quản lý Danh mục
    Route::get('/admin/categories', [\App\Http\Controllers\CategoryController::class, 'index']);
    Route::post('/admin/categories', [\App\Http\Controllers\CategoryController::class, 'store']);
    Route::delete('/admin/categories/{id}', [\App\Http\Controllers\CategoryController::class, 'destroy']);
    Route::get('/admin/banners', function () {
    // Dấu chấm "." thay cho dấu gạch chéo "/" trong cấu trúc thư mục views
    return view('admin.banners.index'); 
});
Route::resource('admin/banners', BannerController::class)->names('admin.banners');
    // Xem chi tiết Giỏ hàng
Route::get('/cart', [CartController::class, 'index']);
// Trang thông tin cá nhân của khách hàng
    Route::get('/profile', [ProfileController::class, 'index']);
    // Cập nhật và Xóa sản phẩm trong giỏ
Route::patch('/cart/update', [CartController::class, 'update']);
Route::delete('/cart/remove', [CartController::class, 'remove']);
// Trang Thanh Toán (Checkout)
Route::get('/checkout', [CheckoutController::class, 'index']);
// Đường dẫn thêm sản phẩm vào giỏ hàng
Route::get('/cart/add/{id}', [CartController::class, 'add']);
Route::post('/checkout/process', [CheckoutController::class, 'process']);
Route::get('/checkout/payment/{id}', [CheckoutController::class, 'payment']);
// Xem lịch sử đơn hàng (Khách hàng)
    Route::get('/profile/orders', [ProfileController::class, 'orders']);
    // Khách hàng tự hủy đơn
    Route::post('/profile/orders/{id}/cancel', [ProfileController::class, 'cancelOrder']);
});

Route::get('/admin/login', [App\Http\Controllers\AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [App\Http\Controllers\AdminAuthController::class, 'login']);
Route::post('/admin/logout', [App\Http\Controllers\AdminAuthController::class, 'logout'])->name('admin.logout');

