<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Customer Contacts — Phase 4.
 *
 * Reference: .kiro/specs/ammrk-platform/design.md (Customers schema)
 *            .kiro/specs/ammrk-platform/tasks.md  (Task 4.1)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();
            $table->string('name', 255);
            $table->string('position', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index('customer_id', 'idx_contacts_customer');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_contacts');
    }
};
