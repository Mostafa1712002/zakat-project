<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('name')
                ->constrained('categories')->nullOnDelete();
        });

        // Auto-map existing suppliers to categories by matching names
        $suppliers = DB::table('suppliers')->get();
        $categories = DB::table('categories')->get();

        foreach ($suppliers as $supplier) {
            foreach ($categories as $category) {
                if (trim($supplier->name) === trim($category->name)) {
                    DB::table('suppliers')
                        ->where('id', $supplier->id)
                        ->update(['category_id' => $category->id]);
                    break;
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
