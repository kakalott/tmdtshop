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
    // Kiểm tra: Nếu chưa có cột sale_price thì mới thêm
    Schema::table('products', function (Blueprint $table) {
        if (!Schema::hasColumn('products', 'sale_price')) {
            $table->decimal('sale_price', 10, 0)->nullable()->after('price'); 
        }
    });

    // Kiểm tra từng cột trong bảng orders, thiếu cái nào bù cái đó
    Schema::table('orders', function (Blueprint $table) {
        if (!Schema::hasColumn('orders', 'order_channel')) {
            $table->string('order_channel')->default('WEB')->after('total_amount'); 
        }
        
        if (!Schema::hasColumn('orders', 'shipping_method')) {
            $table->string('shipping_method')->default('delivery')->after('order_channel'); 
        }
        
        if (!Schema::hasColumn('orders', 'discount_amount')) {
            $table->decimal('discount_amount', 10, 0)->default(0)->after('shipping_method'); 
        }
    });
    }
    public function down(): void
    {
        Schema::table('ecommerce_tables', function (Blueprint $table) {
            //
        });
    }
};
