<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('target_amount', 12, 2)->default(0)->after('payment_terms_days')->comment('مبلغ التارجت المطلوب');
            $table->decimal('target_discount_percentage', 5, 2)->default(0)->after('target_amount')->comment('نسبة خصم التارجت');
            $table->decimal('target_paid_amount', 12, 2)->default(0)->after('target_discount_percentage')->comment('إجمالي ما تم صرفه من التارجت');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['target_amount', 'target_discount_percentage', 'target_paid_amount']);
        });
    }
};
