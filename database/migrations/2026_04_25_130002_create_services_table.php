<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 3: Catalog — services table.
 *
 * Services have NO price field — prices are set per invoice.
 *
 * Reference: .kiro/specs/ammrk-platform/design.md (Catalog schema)
 *            .kiro/specs/ammrk-platform/tasks.md (Task 3.1)
 *            .kiro/specs/ammrk-platform/requirements.md (US-002)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_type_id')
                ->constrained('service_types')
                ->restrictOnDelete();
            $table->string('name');
            $table->foreignId('unit_id')
                ->constrained('units')
                ->restrictOnDelete();
            $table->text('description')->nullable();
            $table->string('default_zatca_classification', 20)->default('S');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['service_type_id', 'is_active'], 'idx_services_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
