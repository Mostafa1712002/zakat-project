<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 3: Catalog — service_types table.
 *
 * Reference: .kiro/specs/ammrk-platform/design.md (Catalog schema)
 *            .kiro/specs/ammrk-platform/tasks.md (Task 3.1)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('icon', 50)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order'], 'idx_service_types_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_types');
    }
};
