<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Customers — Phase 4 (rebuilt from spec).
 *
 * Reference: .kiro/specs/ammrk-platform/design.md (Customers schema)
 *            .kiro/specs/ammrk-platform/tasks.md  (Task 4.1)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->enum('type', ['company', 'government', 'individual'])->default('company');

            // ZATCA fields
            $table->string('vat_number', 15)->nullable();
            $table->string('cr_number', 20)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->boolean('is_tax_exempt')->default(false);

            // Ownership / scoping
            $table->foreignId('account_manager_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('branch_id')
                ->nullable()
                ->constrained('branches')
                ->nullOnDelete();

            // REGA Address (ZATCA-required)
            $table->string('street_name', 127)->nullable();
            $table->string('building_number', 4)->nullable();
            $table->string('secondary_number', 4)->nullable();
            $table->string('district', 127)->nullable();
            $table->string('city', 127)->nullable();
            $table->string('postal_code', 5)->nullable();
            $table->char('country_code', 2)->default('SA');

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('account_manager_id', 'idx_customers_manager');
            $table->index('vat_number', 'idx_customers_vat');
            $table->index('branch_id', 'idx_customers_branch');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
