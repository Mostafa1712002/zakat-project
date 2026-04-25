<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Quote Items — Phase 5a.
 *
 * tax_rate defaults to 15 at the DB level — fixes the historical bug where
 * VAT was sometimes saved as zero on legacy invoices.
 *
 * Reference: .kiro/specs/ammrk-platform/design.md (Sales schema — quote_items)
 *            .kiro/specs/ammrk-platform/tasks.md  (Task 5.1)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained('quotes')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->restrictOnDelete();

            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(15); // critical: 15% default at DB level
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->index('quote_id', 'idx_quote_items_quote');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_items');
    }
};
