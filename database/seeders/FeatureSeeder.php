<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            // المبيعات
            [
                'name' => 'sales',
                'name_ar' => 'المبيعات',
                'description' => 'Sales management module',
                'description_ar' => 'إدارة عمليات البيع والفواتير',
                'icon' => '💰',
                'route_name' => 'sales.index',
                'group' => 'sales',
                'is_enabled' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'invoices',
                'name_ar' => 'الفواتير',
                'description' => 'Invoice management',
                'description_ar' => 'إدارة الفواتير وطباعتها',
                'icon' => '📄',
                'route_name' => 'invoices.index',
                'group' => 'sales',
                'is_enabled' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'purchases',
                'name_ar' => 'المشتريات',
                'description' => 'Purchase management',
                'description_ar' => 'إدارة المشتريات والموردين',
                'icon' => '🛒',
                'route_name' => 'purchases.index',
                'group' => 'sales',
                'is_enabled' => true,
                'sort_order' => 3,
            ],

            // المخزون
            [
                'name' => 'products',
                'name_ar' => 'الأصناف',
                'description' => 'Product management',
                'description_ar' => 'إدارة الأصناف والمنتجات',
                'icon' => '📦',
                'route_name' => 'products.index',
                'group' => 'inventory',
                'is_enabled' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'categories',
                'name_ar' => 'الأقسام',
                'description' => 'Category management',
                'description_ar' => 'إدارة أقسام المنتجات',
                'icon' => '🏷️',
                'route_name' => 'categories.index',
                'group' => 'inventory',
                'is_enabled' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'warehouses',
                'name_ar' => 'المخازن',
                'description' => 'Warehouse management',
                'description_ar' => 'إدارة المخازن والمستودعات',
                'icon' => '🏭',
                'route_name' => 'warehouses.index',
                'group' => 'inventory',
                'is_enabled' => true,
                'sort_order' => 3,
            ],

            // العلاقات
            [
                'name' => 'customers',
                'name_ar' => 'العملاء',
                'description' => 'Customer management',
                'description_ar' => 'إدارة بيانات العملاء',
                'icon' => '👥',
                'route_name' => 'customers.index',
                'group' => 'general',
                'is_enabled' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'suppliers',
                'name_ar' => 'الموردين',
                'description' => 'Supplier management',
                'description_ar' => 'إدارة بيانات الموردين',
                'icon' => '🏢',
                'route_name' => 'suppliers.index',
                'group' => 'general',
                'is_enabled' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'employees',
                'name_ar' => 'الموظفين',
                'description' => 'Employee management',
                'description_ar' => 'إدارة بيانات الموظفين',
                'icon' => '👨‍💻',
                'route_name' => 'employees.index',
                'group' => 'general',
                'is_enabled' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'sales_reps',
                'name_ar' => 'المندوبين',
                'description' => 'Sales representatives management',
                'description_ar' => 'إدارة بيانات مندوبي المبيعات',
                'icon' => '🧑‍💼',
                'route_name' => 'sales-reps.index',
                'group' => 'general',
                'is_enabled' => true,
                'sort_order' => 4,
            ],

            // المالية
            [
                'name' => 'expenses',
                'name_ar' => 'المصروفات',
                'description' => 'Expense management',
                'description_ar' => 'إدارة المصروفات والنفقات',
                'icon' => '💸',
                'route_name' => 'expenses.index',
                'group' => 'general',
                'is_enabled' => true,
                'sort_order' => 5,
            ],

            // التقارير
            [
                'name' => 'reports',
                'name_ar' => 'التقارير',
                'description' => 'Reports and analytics',
                'description_ar' => 'التقارير والإحصائيات',
                'icon' => '📈',
                'route_name' => 'reports.index',
                'group' => 'reports',
                'is_enabled' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'report_profits',
                'name_ar' => 'تقرير الأرباح',
                'description' => 'Profit reports',
                'description_ar' => 'تقارير الأرباح والخسائر',
                'icon' => '💵',
                'route_name' => 'reports.profits',
                'group' => 'reports',
                'is_enabled' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'report_inventory',
                'name_ar' => 'تقرير المخزون',
                'description' => 'Inventory reports',
                'description_ar' => 'تقارير حالة المخزون',
                'icon' => '📊',
                'route_name' => 'reports.inventory',
                'group' => 'reports',
                'is_enabled' => true,
                'sort_order' => 3,
            ],

            // الإعدادات
            [
                'name' => 'settings',
                'name_ar' => 'الإعدادات',
                'description' => 'System settings',
                'description_ar' => 'إعدادات النظام العامة',
                'icon' => '⚙️',
                'route_name' => 'settings.index',
                'group' => 'settings',
                'is_enabled' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'users',
                'name_ar' => 'المستخدمين',
                'description' => 'User management',
                'description_ar' => 'إدارة المستخدمين والصلاحيات',
                'icon' => '👤',
                'route_name' => 'settings.users',
                'group' => 'settings',
                'is_enabled' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($features as $feature) {
            Feature::updateOrCreate(
                ['name' => $feature['name']],
                $feature
            );
        }
    }
}
