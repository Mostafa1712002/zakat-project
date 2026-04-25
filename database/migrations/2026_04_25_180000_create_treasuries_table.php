<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 6: Treasury — vaults that hold cash/bank balances.
 *
 * Reference: .kiro/specs/ammrk-platform/design.md (Treasury schema)
 *            .kiro/specs/ammrk-platform/tasks.md  (Task 6.1)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treasuries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->foreignId('branch_id')->nullable()
                ->constrained('branches')->nullOnDelete();
            $table->enum('type', ['cash', 'bank'])->default('cash');
            $table->decimal('balance', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treasuries');
    }
};
