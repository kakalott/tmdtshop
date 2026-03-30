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
        Schema::create('orders', function (Blueprint $table) {
        $table->id();
        // user_id có thể null vì khách vào tận quầy mua thì không cần tạo tài khoản web
        $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); 
        
        $table->decimal('total_amount', 12, 0); // Tổng tiền hóa đơn
        
        // CỘT QUAN TRỌNG NHẤT DỰ ÁN: Phân biệt kênh bán
        $table->enum('order_channel', ['web', 'pos'])->default('web'); 
        
        // Trạng thái đơn hàng
        $table->enum('status', ['pending', 'processing', 'completed', 'cancelled'])->default('pending');
        
        $table->string('payment_method')->default('cash'); // cash, transfer, cod
        $table->text('shipping_address')->nullable(); // Địa chỉ giao hàng (dành cho đơn Web)
        $table->string('customer_phone')->nullable(); // SĐT khách
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
