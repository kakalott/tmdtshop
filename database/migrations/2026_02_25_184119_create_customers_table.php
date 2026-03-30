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
        Schema::create('customers', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // Tên người đại diện mua hàng
        $table->string('company_name')->nullable(); // Tên đại lý/cửa hàng nhập sỉ
        $table->string('tax_code')->nullable(); // Mã số thuế để xuất hóa đơn
        $table->string('phone')->unique(); 
        $table->string('email')->nullable();
        $table->text('address')->nullable();
        $table->decimal('discount_rate', 5, 2)->default(0); // % Chiết khấu (VD: 5.50%)
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
