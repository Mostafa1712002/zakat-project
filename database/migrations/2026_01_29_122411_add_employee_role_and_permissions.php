<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // إنشاء الصلاحيات للموظف
        $permissions = [
            // المبيعات
            'view_sales',
            'create_sales',
            'edit_sales',

            // المشتريات
            'view_purchases',
            'create_purchases',
            'edit_purchases',

            // الأصناف
            'view_products',
            'create_products',
            'edit_products',

            // المخازن
            'view_warehouses',
            'view_inventory',

            // التحويلات
            'create_transfers',
            'view_transfers',

            // العملاء
            'view_customers',
            'create_customers',
            'edit_customers',

            // الموردين
            'view_suppliers',
            'create_suppliers',
            'edit_suppliers',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // إنشاء دور الموظف
        $employeeRole = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);

        // تعيين الصلاحيات للدور
        $employeeRole->syncPermissions($permissions);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // حذف الدور
        $role = Role::where('name', 'employee')->first();
        if ($role) {
            $role->delete();
        }
    }
};
