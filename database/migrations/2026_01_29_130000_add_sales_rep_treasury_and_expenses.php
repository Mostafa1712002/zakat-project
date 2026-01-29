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
        // إضافة رصيد الخزينة للمندوب
        Schema::table('sales_reps', function (Blueprint $table) {
            $table->decimal('treasury_balance', 12, 2)->default(0)->after('sales_target');
        });

        // إنشاء جدول معاملات خزينة المندوب
        Schema::create('sales_rep_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_rep_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['deposit', 'withdrawal', 'expense', 'collection']);
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->string('description')->nullable();
            $table->string('reference_type')->nullable(); // payment, expense, manual
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['sales_rep_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });

        // إضافة sales_rep_id للمصروفات
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('sales_rep_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['sales_rep_id']);
            $table->dropColumn('sales_rep_id');
        });

        Schema::dropIfExists('sales_rep_transactions');

        Schema::table('sales_reps', function (Blueprint $table) {
            $table->dropColumn('treasury_balance');
        });
    }
};
