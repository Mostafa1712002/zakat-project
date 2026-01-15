<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Category;
use App\Models\ExpenseCategory;
use App\Models\ExpensePaymentMethod;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->createPermissions();
        $this->createRoles();
        $this->createDefaultBranch();
        $this->createAdminUser();
        $this->createDefaultUnits();
        $this->createDefaultCategories();
        $this->createExpenseCategories();
        $this->createExpensePaymentMethods();
    }

    private function createPermissions(): void
    {
        $modules = [
            'users', 'roles', 'branches', 'warehouses',
            'categories', 'products', 'units',
            'customers', 'suppliers', 'employees', 'sales_reps',
            'sales', 'purchases', 'payments', 'expenses',
            'inventory', 'reports', 'settings', 'audit_logs'
        ];

        $actions = ['view', 'create', 'edit', 'delete'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$action}_{$module}"]);
            }
        }

        // Special permissions
        Permission::firstOrCreate(['name' => 'manage_all_branches']);
        Permission::firstOrCreate(['name' => 'approve_expenses']);
        Permission::firstOrCreate(['name' => 'approve_returns']);
        Permission::firstOrCreate(['name' => 'view_financial_reports']);
        Permission::firstOrCreate(['name' => 'export_data']);

        // Sales Rep specific permissions
        Permission::firstOrCreate(['name' => 'collect_customer_payments']);
        Permission::firstOrCreate(['name' => 'view_own_reports']);
        Permission::firstOrCreate(['name' => 'view_all_sales_reps_reports']);
        Permission::firstOrCreate(['name' => 'pay_suppliers']);
    }

    private function createRoles(): void
    {
        // Super Admin
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->syncPermissions(Permission::all());

        // Admin
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::whereNotIn('name', [
            'delete_users', 'delete_roles', 'delete_branches'
        ])->get());

        // Branch Manager
        $manager = Role::firstOrCreate(['name' => 'branch_manager']);
        $manager->syncPermissions(Permission::whereIn('name', [
            'view_users', 'view_branches', 'view_warehouses',
            'view_categories', 'view_products', 'create_products', 'edit_products',
            'view_customers', 'create_customers', 'edit_customers',
            'view_suppliers', 'view_employees', 'view_sales_reps',
            'view_sales', 'create_sales', 'edit_sales',
            'view_purchases', 'create_purchases', 'edit_purchases',
            'view_payments', 'create_payments', 'pay_suppliers',
            'view_expenses', 'create_expenses', 'approve_expenses',
            'view_inventory', 'view_reports', 'view_all_sales_reps_reports'
        ])->get());

        // Sales Rep
        $salesRep = Role::firstOrCreate(['name' => 'sales_rep']);
        $salesRep->syncPermissions(Permission::whereIn('name', [
            'view_products', 'view_customers', 'create_customers', 'edit_customers',
            'view_sales', 'create_sales', 'edit_sales', 'view_inventory',
            'collect_customer_payments', 'view_own_reports', 'view_payments', 'create_payments'
        ])->get());

        // Accountant
        $accountant = Role::firstOrCreate(['name' => 'accountant']);
        $accountant->syncPermissions(Permission::whereIn('name', [
            'view_sales', 'view_purchases', 'view_payments', 'create_payments',
            'view_expenses', 'create_expenses', 'view_reports',
            'view_financial_reports', 'export_data', 'pay_suppliers', 'view_all_sales_reps_reports'
        ])->get());

        // Warehouse Keeper
        $warehouse = Role::firstOrCreate(['name' => 'warehouse_keeper']);
        $warehouse->syncPermissions(Permission::whereIn('name', [
            'view_products', 'view_warehouses', 'view_inventory',
            'create_inventory', 'edit_inventory'
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
                'branch_id' => $branch->id,
                'is_active' => true,
            ]
        );

        $user->assignRole('super_admin');

        // Create default warehouse
        Warehouse::firstOrCreate(
            ['code' => 'WH-MAIN'],
            [
                'name' => 'المخزن الرئيسي',
                'branch_id' => $branch->id,
                'is_active' => true,
                'is_default' => true,
            ]
        );
    }

    private function createDefaultUnits(): void
    {
        $units = [
            ['name' => 'قطعة', 'code' => 'PCS', 'symbol' => 'قطعة'],
            ['name' => 'كيلوجرام', 'code' => 'KG', 'symbol' => 'كجم'],
            ['name' => 'جرام', 'code' => 'G', 'symbol' => 'جم'],
            ['name' => 'لتر', 'code' => 'L', 'symbol' => 'لتر'],
            ['name' => 'متر', 'code' => 'M', 'symbol' => 'م'],
            ['name' => 'علبة', 'code' => 'BOX', 'symbol' => 'علبة'],
            ['name' => 'كرتون', 'code' => 'CTN', 'symbol' => 'كرتون'],
            ['name' => 'طن', 'code' => 'TON', 'symbol' => 'طن'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['code' => $unit['code']], $unit);
        }

        // Set conversion factors
        $kg = Unit::where('code', 'KG')->first();
        $g = Unit::where('code', 'G')->first();
        if ($kg && $g) {
            $g->update(['base_unit_id' => $kg->id, 'conversion_factor' => 0.001]);
        }

        $ton = Unit::where('code', 'TON')->first();
        if ($kg && $ton) {
            $ton->update(['base_unit_id' => $kg->id, 'conversion_factor' => 1000]);
        }
    }

    private function createDefaultCategories(): void
    {
        $categories = [
            ['name' => 'الإلكترونيات', 'code' => 'ELEC'],
            ['name' => 'الأجهزة المنزلية', 'code' => 'HOME'],
            ['name' => 'الملابس', 'code' => 'CLOTH'],
            ['name' => 'المواد الغذائية', 'code' => 'FOOD'],
            ['name' => 'مستلزمات مكتبية', 'code' => 'OFFICE'],
            ['name' => 'أخرى', 'code' => 'OTHER'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['code' => $category['code']], $category);
        }
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
