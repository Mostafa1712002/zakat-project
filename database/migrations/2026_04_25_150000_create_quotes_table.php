<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Quotes — Phase 5a (Sales / Quote workflow).
 *
 * Reference: .kiro/specs/ammrk-platform/design.md (Sales schema — quotes)
 *            .kiro/specs/ammrk-platform/tasks.md  (Task 5.1)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('quote_number', 50)->unique();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();

            // Event details
            $table->string('event_name', 255);
            $table->date('event_start_date')->nullable();
            $table->date('event_end_date')->nullable();
            $table->string('event_location', 255)->nullable();
            $table->string('event_type', 100)->nullable();

            // Totals
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_total', 15, 2)->default(0);
            $table->decimal('tax_total', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);

            // Workflow
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'converted'])
                ->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->date('valid_until')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_id', 'idx_quotes_customer');
            $table->index('status', 'idx_quotes_status');
            $table->index('created_by', 'idx_quotes_creator');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
