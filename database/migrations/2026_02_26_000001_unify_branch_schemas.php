<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration موحد: يضيف الأعمدة الناقصة لكل قاعدة بيانات بأمان
     * - لـ rogence: أعمدة syramik (area, tiles, grades, quotation, fixed_discount)
     * - لـ syramik: أعمدة rogence (target fields)
     * كل الأعمدة nullable — آمنة تماماً
     */
    public function up(): void
    {
        // === Products: أعمدة المساحة والبلاط (syramik) ===
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'area_per_unit')) {
                $table->decimal('area_per_unit', 10, 4)->nullable()->after('notes')
                    ->comment('مساحة الوحدة بالمتر المربع');
            }
            if (!Schema::hasColumn('products', 'tiles_per_box')) {
                $table->integer('tiles_per_box')->nullable()->after('area_per_unit')
                    ->comment('عدد البلاطات في العلبة');
            }
            if (!Schema::hasColumn('products', 'tile_area')) {
                $table->decimal('tile_area', 10, 4)->nullable()->after('tiles_per_box')
                    ->comment('مساحة البلاطة الواحدة');
            }
        });

        // === Sale Items: مساحة وفرز (syramik) ===
        Schema::table('sale_items', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_items', 'total_area')) {
                $table->decimal('total_area', 12, 4)->nullable()->after('total')
                    ->comment('إجمالي المساحة');
            }
            if (!Schema::hasColumn('sale_items', 'grade_id')) {
                $table->foreignId('grade_id')->nullable()->after('total_area')
                    ->comment('الفرز/الجودة');
            }
        });

        // === Purchase Items: مساحة وفرز (syramik) ===
        if (Schema::hasTable('purchase_items')) {
            Schema::table('purchase_items', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_items', 'total_area')) {
                    $table->decimal('total_area', 12, 4)->nullable()->after('total')
                        ->comment('إجمالي المساحة');
                }
                if (!Schema::hasColumn('purchase_items', 'grade_id')) {
                    $table->foreignId('grade_id')->nullable()->after('total_area')
                        ->comment('الفرز/الجودة');
                }
            });
        }

        // === Customers: خصم ثابت (syramik) + target (rogence) ===
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'fixed_discount')) {
                $table->decimal('fixed_discount', 5, 2)->nullable()->after('payment_terms_days')
                    ->comment('نسبة خصم ثابتة للعميل');
            }
            // Target fields — already exist on rogence, add for syramik
            if (!Schema::hasColumn('customers', 'target_amount')) {
                $table->decimal('target_amount', 12, 2)->nullable()->after('fixed_discount');
            }
            if (!Schema::hasColumn('customers', 'target_discount_percentage')) {
                $table->decimal('target_discount_percentage', 5, 2)->nullable()->after('target_amount');
            }
            if (!Schema::hasColumn('customers', 'target_paid_amount')) {
                $table->decimal('target_paid_amount', 12, 2)->nullable()->default(0)->after('target_discount_percentage');
            }
        });

        // === Sales: quotation (syramik) ===
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'is_quotation')) {
                $table->boolean('is_quotation')->default(false)->after('status')
                    ->comment('هل هي عرض سعر؟');
            }
            if (!Schema::hasColumn('sales', 'quotation_number')) {
                $table->string('quotation_number')->nullable()->after('is_quotation')
                    ->comment('رقم عرض السعر');
            }
        });

        // === Purchases: quotation (syramik) ===
        if (Schema::hasTable('purchases')) {
            Schema::table('purchases', function (Blueprint $table) {
                if (!Schema::hasColumn('purchases', 'is_quotation')) {
                    $table->boolean('is_quotation')->default(false)->after('status')
                        ->comment('هل هي عرض سعر؟');
                }
                if (!Schema::hasColumn('purchases', 'quotation_number')) {
                    $table->string('quotation_number')->nullable()->after('is_quotation')
                        ->comment('رقم عرض السعر');
                }
            });
        }

        // === Grades table (syramik) ===
        if (!Schema::hasTable('grades')) {
            Schema::create('grades', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('name_ar')->nullable();
                $table->string('code')->nullable()->unique();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop grades table
        Schema::dropIfExists('grades');

        // Remove added columns from purchases
        if (Schema::hasTable('purchases')) {
            Schema::table('purchases', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('purchases', 'is_quotation')) $columns[] = 'is_quotation';
                if (Schema::hasColumn('purchases', 'quotation_number')) $columns[] = 'quotation_number';
                if (!empty($columns)) $table->dropColumn($columns);
            });
        }

        // Remove added columns from sales
        Schema::table('sales', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('sales', 'is_quotation')) $columns[] = 'is_quotation';
            if (Schema::hasColumn('sales', 'quotation_number')) $columns[] = 'quotation_number';
            if (!empty($columns)) $table->dropColumn($columns);
        });

        // Remove added columns from customers
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'fixed_discount')) {
                $table->dropColumn('fixed_discount');
            }
        });

        // Remove added columns from purchase_items
        if (Schema::hasTable('purchase_items')) {
            Schema::table('purchase_items', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('purchase_items', 'total_area')) $columns[] = 'total_area';
                if (Schema::hasColumn('purchase_items', 'grade_id')) $columns[] = 'grade_id';
                if (!empty($columns)) $table->dropColumn($columns);
            });
        }

        // Remove added columns from sale_items
        Schema::table('sale_items', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('sale_items', 'total_area')) $columns[] = 'total_area';
            if (Schema::hasColumn('sale_items', 'grade_id')) $columns[] = 'grade_id';
            if (!empty($columns)) $table->dropColumn($columns);
        });

        // Remove added columns from products
        Schema::table('products', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('products', 'area_per_unit')) $columns[] = 'area_per_unit';
            if (Schema::hasColumn('products', 'tiles_per_box')) $columns[] = 'tiles_per_box';
            if (Schema::hasColumn('products', 'tile_area')) $columns[] = 'tile_area';
            if (!empty($columns)) $table->dropColumn($columns);
        });
    }
};
