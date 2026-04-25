<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 6: Treasury — payments received against invoices.
 *
 * Replaces the Phase 1 stub `payments` migration. New schema is
 * invoice-scoped (no morph), with a treasury FK.
 *
 * Reference: .kiro/specs/ammrk-platform/design.md (Treasury schema)
 *            .kiro/specs/ammrk-platform/tasks.md  (Task 6.1)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number', 50)->unique();
            $table->foreignId('invoice_id')->constrained('invoices')->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('treasury_id')->constrained('treasuries')->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->enum('method', ['cash', 'bank', 'transfer', 'check'])->default('cash');
            $table->string('reference_number', 100)->nullable();
            $table->date('payment_date');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('invoice_id');
            $table->index('customer_id');
            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
