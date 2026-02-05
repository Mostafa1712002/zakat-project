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
        Schema::create('commission_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_rep_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expense_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('year');
            $table->integer('month');
            $table->decimal('amount', 12, 2);
            $table->foreignId('withdrawn_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            // منع السحب المكرر لنفس الشهر
            $table->unique(['sales_rep_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_withdrawals');
    }
};
