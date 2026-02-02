<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // مخزن المندوب - الأصناف المخصصة لكل مندوب
        Schema::create('sales_rep_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_rep_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('reserved_quantity', 12, 2)->default(0); // محجوز للفواتير
            $table->timestamps();

            $table->unique(['sales_rep_id', 'product_id']);
            $table->index('sales_rep_id');
        });

        // حركات مخزن المندوب
        Schema::create('sales_rep_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_rep_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['in', 'out', 'sale', 'return', 'adjustment']);
            $table->decimal('quantity', 12, 2);
            $table->decimal('quantity_before', 12, 2);
            $table->decimal('quantity_after', 12, 2);
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['sales_rep_id', 'created_at']);
            $table->index(['sales_rep_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_rep_stock_movements');
        Schema::dropIfExists('sales_rep_inventory');
    }
};
