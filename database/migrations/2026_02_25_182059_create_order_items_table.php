<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
        $table->id();
        // Khóa ngoại liên kết với bảng orders
        $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
        
        // Khóa ngoại liên kết với bảng products (giữ lại null nếu sau này xóa sản phẩm đi thì đơn hàng cũ ko bị lỗi)
        $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
        
        $table->integer('quantity'); // Số lượng mua (VD: 2 cái ghế)
        
        // CỰC KỲ QUAN TRỌNG: Phải lưu lại giá tại thời điểm mua. 
        // Đề phòng năm sau ghế tăng giá thì xem lại hóa đơn cũ tiền không bị nhảy sai.
        $table->decimal('unit_price', 10, 0); 
        $table->decimal('subtotal', 12, 0); // = quantity * unit_price
        
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
