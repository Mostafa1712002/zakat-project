<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Invoices — Phase 5b (Sales / Invoice issuance + ZATCA fields).
 *
 * Reference: .kiro/specs/ammrk-platform/design.md (Sales schema — invoices)
 *            .kiro/specs/ammrk-platform/tasks.md  (Task 5.5)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->foreignId('quote_id')->nullable()->constrained('quotes')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();

            // Event details (mirrored from quote when converted)
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
            $table->decimal('paid_amount', 15, 2)->default(0);

            // Workflow
            $table->enum('status', ['draft', 'issued', 'paid_partial', 'paid', 'cancelled'])
                ->default('draft');

            // ZATCA fields
            $table->char('uuid', 36)->nullable();
            $table->unsignedBigInteger('icv')->nullable();
            $table->string('pih', 255)->nullable();
            $table->string('invoice_hash', 255)->nullable();
            $table->text('qr_code')->nullable();
            $table->longText('signed_xml')->nullable();
            $table->enum('zatca_status', ['pending', 'cleared', 'reported', 'failed'])
                ->default('pending');
            $table->string('zatca_uuid', 100)->nullable();
            $table->json('zatca_warnings')->nullable();
            $table->timestamp('zatca_submitted_at')->nullable();

            $table->timestamp('issued_at')->nullable();
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_id', 'idx_invoices_customer');
            $table->index('status', 'idx_invoices_status');
            $table->index('zatca_status', 'idx_invoices_zatca');
            $table->index('uuid', 'idx_invoices_uuid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
