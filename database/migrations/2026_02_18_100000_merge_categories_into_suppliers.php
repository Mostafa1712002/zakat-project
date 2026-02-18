<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * For each category, create a supplier (if name doesn't exist),
     * then sync the category's products to the supplier via product_supplier pivot.
     */
    public function up(): void
    {
        $categories = DB::table('categories')->whereNull('deleted_at')->get();

        foreach ($categories as $category) {
            // Find or create supplier with same name
            $supplier = DB::table('suppliers')->where('name', $category->name)->first();

            if (!$supplier) {
                $supplierId = DB::table('suppliers')->insertGetId([
                    'name' => $category->name,
                    'code' => 'CAT-' . $category->id,
                    'is_active' => $category->is_active ?? true,
                    'notes' => 'تم الإنشاء تلقائياً من قسم: ' . $category->name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $supplierId = $supplier->id;
            }

            // Get all products belonging to this category
            $productIds = DB::table('products')
                ->where('category_id', $category->id)
                ->pluck('id');

            // Sync products to supplier via pivot (skip existing)
            foreach ($productIds as $productId) {
                $exists = DB::table('product_supplier')
                    ->where('product_id', $productId)
                    ->where('supplier_id', $supplierId)
                    ->exists();

                if (!$exists) {
                    DB::table('product_supplier')->insert([
                        'product_id' => $productId,
                        'supplier_id' => $supplierId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove suppliers created from categories
        DB::table('suppliers')->where('code', 'LIKE', 'CAT-%')->delete();
    }
};
