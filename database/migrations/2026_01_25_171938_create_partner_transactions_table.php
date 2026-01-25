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
        Schema::create('partner_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->string('transaction_number')->unique();
            $table->enum('type', ['withdrawal', 'profit_share', 'investment', 'return'])->default('withdrawal');
            // withdrawal = سحب أرباح
            // profit_share = توزيع أرباح
            // investment = إضافة رأس مال
            // return = إرجاع رأس مال
            $table->decimal('amount', 15, 2);
            $table->date('transaction_date');
            $table->string('period', 20)->nullable(); // الفترة (مثال: 2026-Q1, 2026)
            $table->enum('payment_method', ['cash', 'bank_transfer', 'check'])->default('cash');
            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('partner_id');
            $table->index('type');
            $table->index('transaction_date');
            $table->index('period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_transactions');
    }
};
