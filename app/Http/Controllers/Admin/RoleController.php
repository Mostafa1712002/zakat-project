<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Admin Role management.
 *
 * Reference: .kiro/specs/ammrk-platform/requirements.md (US-050)
 *            .kiro/specs/ammrk-platform/tasks.md (Task 2.3)
 */
class RoleController extends Controller
{
    /**
     * List all roles with their permission counts.
     */
    public function index(): View
    {
        $roles = Role::withCount('permissions')->orderBy('id')->get();

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Edit a role's permissions.
     */
    public function edit(Role $role): View|RedirectResponse
    {
        if ($this->isLocked($role)) {
            return redirect()
                ->route('admin.roles.index')
                ->with('error', 'لا يمكن تعديل صلاحيات هذا الدور (مقفل من النظام)');
        }

        $permissions = Permission::orderBy('name')->get()->groupBy(function ($permission) {
            // Group by domain prefix before the first dot
            return explode('.', $permission->name, 2)[0] ?? 'general';
        });

        $assigned = $role->permissions->pluck('name')->all();

        return view('admin.roles.edit', compact('role', 'permissions', 'assigned'));
    }

    /**
     * Sync a role's permissions.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        if ($this->isLocked($role)) {
            abort(403, 'لا يمكن تعديل صلاحيات هذا الدور');
        }

        $validated = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'تم تحديث صلاحيات الدور');
    }

    /**
     * Determine if a role is locked from edits.
     */
    protected function isLocked(Role $role): bool
    {
        return $role->name === 'Super Admin' || (bool) ($role->is_locked ?? false);
    }
}
