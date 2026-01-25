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
        Schema::create('employee_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('transaction_number')->unique();
            $table->enum('type', ['salary', 'advance', 'bonus', 'deduction'])->default('salary');
            $table->decimal('amount', 12, 2);
            $table->date('transaction_date');
            $table->string('month_year', 7)->nullable(); // Format: 2026-01 for salary tracking
            $table->enum('payment_method', ['cash', 'bank_transfer', 'check'])->default('cash');
            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('employee_id');
            $table->index('type');
            $table->index('transaction_date');
            $table->index('month_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_transactions');
    }
};
