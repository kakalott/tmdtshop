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
        // Nối Category vào Product
    Schema::table('products', function (Blueprint $table) {
        $table->foreignId('category_id')->nullable()->after('id')->constrained('categories')->onDelete('set null');
    });

    // Nối Customer vào Order
    Schema::table('orders', function (Blueprint $table) {
        $table->foreignId('customer_id')->nullable()->after('user_id')->constrained('customers')->onDelete('set null');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products_and_orders', function (Blueprint $table) {
            //
        });
    }
};
