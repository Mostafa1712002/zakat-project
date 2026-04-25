<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * AMMRK platform roles and permissions seed.
 *
 * Reference: .kiro/specs/ammrk-platform/requirements.md (User Roles)
 *            .kiro/specs/ammrk-platform/tasks.md (Task 2.3)
 */
class RolePermissionSeeder extends Seeder
{
    /**
     * Granular permission catalog.
     *
     * @var array<string, list<string>>
     */
    protected array $permissions = [
        'customers' => ['view-own', 'view-all', 'create', 'edit', 'delete'],
        'service-types' => ['view', 'create', 'edit', 'delete'],
        'services' => ['view', 'create', 'edit', 'delete'],
        'quotes' => ['view-own', 'view-all', 'create', 'edit-own', 'edit-any', 'submit', 'approve', 'reject', 'convert'],
        'invoices' => ['view-own', 'view-all', 'create', 'edit', 'issue', 'send-zatca', 'cancel'],
        'payments' => ['view', 'create', 'refund'],
        'reports' => ['financial', 'operational'],
        'settings' => ['system', 'zatca'],
        'users' => ['view', 'create', 'edit', 'delete', 'manage-permissions'],
        'roles' => ['view', 'create', 'edit', 'delete', 'manage-permissions'],
    ];

    public function run(): void
    {
        // Reset cached roles and permissions before seeding
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $allPermissionNames = $this->createPermissions();

        // Super Admin — every permission, locked from edit
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions($allPermissionNames);
        $superAdmin->is_locked = true;
        $superAdmin->save();

        // Admin — everything except settings.system and roles.manage-permissions
        $adminPermissions = array_values(array_diff(
            $allPermissionNames,
            ['settings.system', 'roles.manage-permissions']
        ));
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->syncPermissions($adminPermissions);

        // Account Manager — scoped sales-side permissions
        $accountManagerPermissions = [
            'customers.view-own', 'customers.create', 'customers.edit',
            'quotes.view-own', 'quotes.create', 'quotes.edit-own', 'quotes.submit',
            'invoices.view-own',
            'payments.view',
            'reports.operational',
        ];
        $accountManager = Role::firstOrCreate(['name' => 'Account Manager', 'guard_name' => 'web']);
        $accountManager->syncPermissions($accountManagerPermissions);

        // Accountant — invoicing + treasury + financial reports
        $accountantPermissions = [
            'customers.view-all',
            'quotes.view-all',
            'invoices.view-all', 'invoices.create', 'invoices.edit',
            'invoices.issue', 'invoices.send-zatca', 'invoices.cancel',
            'payments.view', 'payments.create', 'payments.refund',
            'reports.financial', 'reports.operational',
        ];
        $accountant = Role::firstOrCreate(['name' => 'Accountant', 'guard_name' => 'web']);
        $accountant->syncPermissions($accountantPermissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Create all granular permissions and return their names.
     *
     * @return list<string>
     */
    protected function createPermissions(): array
    {
        $names = [];

        foreach ($this->permissions as $domain => $actions) {
            foreach ($actions as $action) {
                $name = "{$domain}.{$action}";
                Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
                $names[] = $name;
            }
        }

        return $names;
    }
}
