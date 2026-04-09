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
            if (!Schema::hasColumn('products', 'supplier_id')) {
                $table->foreignId('supplier_id')->nullable()->after('category_id')
                    ->constrained('suppliers')->nullOnDelete();
            }
        });

        // Populate supplier_id from category_id via supplier.category_id mapping
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::table('products')
                ->whereNotNull('category_id')
                ->get(['id', 'category_id'])
                ->each(function ($product) {
                    $supplierId = DB::table('suppliers')
                        ->where('category_id', $product->category_id)
                        ->whereNull('deleted_at')
                        ->value('id');

                    if ($supplierId) {
                        DB::table('products')
                            ->where('id', $product->id)
                            ->update(['supplier_id' => $supplierId]);
                    }
                });
        } else {
            DB::statement("
                UPDATE products p
                INNER JOIN suppliers s ON s.category_id = p.category_id
                SET p.supplier_id = s.id
                WHERE p.category_id IS NOT NULL
                  AND s.deleted_at IS NULL
            ");
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'supplier_id')) {
                $table->dropConstrainedForeignId('supplier_id');
            }
        });
    }
};
