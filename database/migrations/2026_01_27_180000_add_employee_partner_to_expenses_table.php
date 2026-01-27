<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->after('user_id')->constrained('employees')->nullOnDelete();
            $table->foreignId('partner_id')->nullable()->after('employee_id')->constrained('partners')->nullOnDelete();
            $table->foreignId('employee_transaction_id')->nullable()->after('partner_id')->constrained('employee_transactions')->nullOnDelete();
            $table->foreignId('partner_transaction_id')->nullable()->after('employee_transaction_id')->constrained('partner_transactions')->nullOnDelete();
        });

        // إضافة فئات جديدة للمصروفات
        DB::table('expense_categories')->insertOrIgnore([
            ['name' => 'سلف موظفين', 'code' => 'EMP_ADVANCE', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'أرباح شركاء', 'code' => 'PARTNER_PROFIT', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['partner_id']);
            $table->dropForeign(['employee_transaction_id']);
            $table->dropForeign(['partner_transaction_id']);
            $table->dropColumn(['employee_id', 'partner_id', 'employee_transaction_id', 'partner_transaction_id']);
        });

        DB::table('expense_categories')->whereIn('code', ['EMP_ADVANCE', 'PARTNER_PROFIT'])->delete();
    }
};
