<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('zatca_uuid')->nullable()->unique();
            $table->string('zatca_invoice_type')->nullable();
            $table->string('zatca_status')->default('draft');
            $table->timestamp('zatca_issued_at')->nullable();
            $table->longText('zatca_qr_tlv')->nullable();
            $table->longText('zatca_xml')->nullable();
            $table->timestamp('zatca_xml_generated_at')->nullable();
            $table->timestamp('zatca_cleared_at')->nullable();
            $table->timestamp('zatca_reported_at')->nullable();
            $table->string('zatca_response_reference')->nullable();
            $table->text('zatca_last_error')->nullable();

            $table->index('zatca_invoice_type');
            $table->index('zatca_status');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['zatca_invoice_type']);
            $table->dropIndex(['zatca_status']);
            $table->dropUnique(['zatca_uuid']);

            $table->dropColumn([
                'zatca_uuid',
                'zatca_invoice_type',
                'zatca_status',
                'zatca_issued_at',
                'zatca_qr_tlv',
                'zatca_xml',
                'zatca_xml_generated_at',
                'zatca_cleared_at',
                'zatca_reported_at',
                'zatca_response_reference',
                'zatca_last_error',
            ]);
        });
    }
};
