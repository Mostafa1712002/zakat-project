<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('zatca_invoice_hash')->nullable();
            $table->string('zatca_previous_invoice_hash')->nullable();
            $table->unsignedBigInteger('zatca_invoice_counter')->nullable();
            $table->string('zatca_note_type')->nullable();
            $table->unsignedBigInteger('zatca_original_sale_id')->nullable();
            $table->string('zatca_note_reason')->nullable();
            $table->unsignedInteger('zatca_retry_count')->default(0);
            $table->timestamp('zatca_next_retry_at')->nullable();

            $table->foreign('zatca_original_sale_id')
                ->references('id')
                ->on('sales')
                ->nullOnDelete();

            $table->index('zatca_original_sale_id');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['zatca_original_sale_id']);
            $table->dropIndex(['zatca_original_sale_id']);
            $table->dropColumn([
                'zatca_invoice_hash',
                'zatca_previous_invoice_hash',
                'zatca_invoice_counter',
                'zatca_note_type',
                'zatca_original_sale_id',
                'zatca_note_reason',
                'zatca_retry_count',
                'zatca_next_retry_at',
            ]);
        });
    }
};
