<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\ExpenseCategory;
use App\Models\ExpensePaymentMethod;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * NOTE: Phase 1 cleanup stub.
 * Original seeder created Category, Warehouse, and product/sales/supplier permission strings —
 * all referencing deleted domains. Will be fully replaced in Phase 2 (Foundation) by:
 *   - SettingsSeeder (default_tax_rate=15, etc.)
 *   - RolePermissionSeeder (Super Admin, Admin, Account Manager, Accountant)
 *   - DefaultUsersSeeder (super-admin@ammrk.com, admin@ammrk.com)
 *   - ServiceTypeSeeder + UnitSeeder (Phase 3)
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->createBasicPermissions();
        $this->createBasicRoles();
        $this->createDefaultBranch();
        $this->createAdminUser();
        $this->createExpenseCategories();
        $this->createExpensePaymentMethods();
    }

    private function createBasicPermissions(): void
    {
        $modules = [
            'users', 'roles', 'branches',
            'customers', 'employees',
            'payments', 'expenses',
            'reports', 'settings', 'audit_logs',
        ];

        $actions = ['view', 'create', 'edit', 'delete'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$action}_{$module}"]);
            }
        }

        Permission::firstOrCreate(['name' => 'manage_all_branches']);
        Permission::firstOrCreate(['name' => 'approve_expenses']);
        Permission::firstOrCreate(['name' => 'view_financial_reports']);
        Permission::firstOrCreate(['name' => 'export_data']);
    }

    private function createBasicRoles(): void
    {
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->syncPermissions(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::whereNotIn('name', [
            'delete_users', 'delete_roles', 'delete_branches',
        ])->get());

        $accountant = Role::firstOrCreate(['name' => 'accountant']);
        $accountant->syncPermissions(Permission::whereIn('name', [
            'view_payments', 'create_payments',
            'view_expenses', 'create_expenses',
            'view_reports', 'view_financial_reports', 'export_data',
        ])->get());
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

    private function createAdminUser(): void
    {
        $branch = Branch::where('code', 'MAIN')->first();

        $user = User::firstOrCreate(
            ['email' => 'admin@crm.test'],
            [
                'name' => 'مدير النظام',
                'password' => Hash::make('password'),
                'branch_id' => $branch?->id,
                'is_active' => true,
            ]
        );

        $user->assignRole('super_admin');
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
