<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // ربط المدفوعات بفواتير محددة
            $table->foreignId('sale_id')->nullable()->after('payable_id')->constrained()->nullOnDelete();
            $table->foreignId('purchase_id')->nullable()->after('sale_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['sale_id']);
            $table->dropForeign(['purchase_id']);
            $table->dropColumn(['sale_id', 'purchase_id']);
        });
    }
};
