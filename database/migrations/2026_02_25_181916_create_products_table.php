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
        Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // Tên sản phẩm (VD: Ghế nhựa tựa lưng)
        $table->string('sku')->unique()->nullable(); // Mã quản lý nội bộ
        $table->string('barcode')->unique()->nullable(); // Mã vạch để quét máy POS
        $table->decimal('price', 10, 0); // Giá bán lẻ
        $table->decimal('wholesale_price', 10, 0)->nullable(); // Giá bán sỉ cho đối tác B2B
        $table->integer('stock_quantity')->default(0); // Số lượng tồn kho
        $table->string('dimensions')->nullable(); // Kích thước (Dài x Rộng x Cao)
        $table->string('image')->nullable(); // Đường dẫn ảnh
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
