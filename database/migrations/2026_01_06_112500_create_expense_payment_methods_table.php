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
        Schema::create('expense_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('expense_payment_method_id')
                ->nullable()
                ->constrained('expense_payment_methods')
                ->nullOnDelete()
                ->after('payment_method');
        });

        $defaults = [
            ['name' => 'نقدي', 'code' => 'cash', 'sort_order' => 10],
            ['name' => 'تحويل بنكي', 'code' => 'bank_transfer', 'sort_order' => 20],
            ['name' => 'شيك', 'code' => 'check', 'sort_order' => 30],
            ['name' => 'بطاقة', 'code' => 'card', 'sort_order' => 40],
            ['name' => 'أخرى', 'code' => 'other', 'sort_order' => 50],
            ['name' => 'فودافون كاش', 'code' => 'vodafone_cash', 'sort_order' => 60],
            ['name' => 'إنستا باي', 'code' => 'instapay', 'sort_order' => 70],
            ['name' => 'الموظفين', 'code' => 'employees', 'sort_order' => 80],
        ];

        foreach ($defaults as $method) {
            DB::table('expense_payment_methods')->insert([
                'name' => $method['name'],
                'code' => $method['code'],
                'is_active' => true,
                'sort_order' => $method['sort_order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $methodMap = DB::table('expense_payment_methods')
            ->pluck('id', 'code')
            ->all();

        foreach (['cash', 'bank_transfer', 'check', 'card', 'other'] as $code) {
            if (!isset($methodMap[$code])) {
                continue;
            }
            DB::table('expenses')
                ->where('payment_method', $code)
                ->update(['expense_payment_method_id' => $methodMap[$code]]);
        }

        if (isset($methodMap['bank_transfer'])) {
            DB::table('expenses')
                ->where('payment_method', 'bank')
                ->update(['expense_payment_method_id' => $methodMap['bank_transfer']]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('expense_payment_method_id');
        });

        Schema::dropIfExists('expense_payment_methods');
    }
};
