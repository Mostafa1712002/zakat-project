<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\ExpenseCategory;
use App\Models\ExpensePaymentMethod;
use Illuminate\Database\Seeder;

/**
 * Phase 2 root seeder.
 *
 * Order matters:
 *   1. Default branch (users.branch_id FK target)
 *   2. SettingsSeeder (default_tax_rate, prefixes, etc.)
 *   3. RolePermissionSeeder (Spatie roles + permissions)
 *   4. DefaultUsersSeeder (depends on roles existing)
 *   5. Expense reference data (surviving legacy domains)
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->createDefaultBranch();

        $this->call([
            SettingsSeeder::class,
            RolePermissionSeeder::class,
            DefaultUsersSeeder::class,
        ]);

        $this->createExpenseCategories();
        $this->createExpensePaymentMethods();
    }

    private function createDefaultBranch(): void
    {
        Branch::firstOrCreate(
            ['code' => 'MAIN'],
            [
                'name' => 'الفرع الرئيسي',
                'is_main' => true,
                'is_active' => true,
            ]
        );
    }

    private function createExpenseCategories(): void
    {
        $categories = [
            ['name' => 'رواتب وأجور', 'code' => 'SALARY'],
            ['name' => 'إيجارات', 'code' => 'RENT'],
            ['name' => 'فواتير خدمات', 'code' => 'UTILITIES'],
            ['name' => 'صيانة', 'code' => 'MAINT'],
            ['name' => 'مواصلات', 'code' => 'TRANSPORT'],
            ['name' => 'تسويق وإعلان', 'code' => 'MARKETING'],
            ['name' => 'مصروفات إدارية', 'code' => 'ADMIN'],
            ['name' => 'أخرى', 'code' => 'OTHER'],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::firstOrCreate(['code' => $category['code']], $category);
        }
    }

    private function createExpensePaymentMethods(): void
    {
        $methods = [
            ['name' => 'نقدي', 'code' => 'cash', 'sort_order' => 10],
            ['name' => 'تحويل بنكي', 'code' => 'bank_transfer', 'sort_order' => 20],
            ['name' => 'شيك', 'code' => 'check', 'sort_order' => 30],
            ['name' => 'بطاقة', 'code' => 'card', 'sort_order' => 40],
            ['name' => 'أخرى', 'code' => 'other', 'sort_order' => 50],
            ['name' => 'فودافون كاش', 'code' => 'vodafone_cash', 'sort_order' => 60],
            ['name' => 'إنستا باي', 'code' => 'instapay', 'sort_order' => 70],
            ['name' => 'الموظفين', 'code' => 'employees', 'sort_order' => 80],
        ];

        foreach ($methods as $method) {
            ExpensePaymentMethod::firstOrCreate(['code' => $method['code']], $method);
        }
    }
}
