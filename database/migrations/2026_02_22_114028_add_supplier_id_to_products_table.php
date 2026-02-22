<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('category_id')
                ->constrained('suppliers')->nullOnDelete();
        });

        // Populate supplier_id from category_id via supplier.category_id mapping
        DB::statement("
            UPDATE products p
            INNER JOIN suppliers s ON s.category_id = p.category_id
            SET p.supplier_id = s.id
            WHERE p.category_id IS NOT NULL
              AND s.deleted_at IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supplier_id');
        });
    }
};
