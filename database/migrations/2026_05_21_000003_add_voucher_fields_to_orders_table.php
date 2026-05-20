<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'subtotal_amount')) {
                $table->decimal('subtotal_amount', 12, 0)->default(0)->after('payment_method');
            }

            if (!Schema::hasColumn('orders', 'voucher_id')) {
                $table->foreignId('voucher_id')->nullable()->after('subtotal_amount')->constrained('vouchers')->onDelete('set null');
            }

            if (!Schema::hasColumn('orders', 'voucher_code')) {
                $table->string('voucher_code')->nullable()->after('voucher_id');
            }

            if (!Schema::hasColumn('orders', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 0)->default(0)->after('voucher_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'voucher_id')) {
                $table->dropConstrainedForeignId('voucher_id');
            }

            if (Schema::hasColumn('orders', 'voucher_code')) {
                $table->dropColumn('voucher_code');
            }

            if (Schema::hasColumn('orders', 'subtotal_amount')) {
                $table->dropColumn('subtotal_amount');
            }
        });
    }
};
