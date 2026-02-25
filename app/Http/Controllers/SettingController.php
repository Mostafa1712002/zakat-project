<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SettingController extends Controller
{
    /**
     * Display the settings dashboard.
     */
    public function index()
    {
        return view('settings.index');
    }

    /**
     * Display company settings.
     */
    public function company()
    {
        $settings = $this->getCompanySettings();

        return view('settings.company', compact('settings'));
    }

    /**
     * Update company settings.
     */
    public function updateCompany(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_name_en' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:50',
            'commercial_register' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'supervisor_phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'currency' => 'nullable|string|max:10',
            'currency_symbol' => 'nullable|string|max:10',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'invoice_prefix' => 'nullable|string|max:20',
            'invoice_footer' => 'nullable|string',
            'invoice_terms' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'stamp' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'contacts' => 'nullable|array',
            'contacts.*.name' => 'nullable|string|max:255',
            'contacts.*.phone' => 'nullable|string|max:20',
            // Color palette
            'color_palette' => 'nullable|string|in:cyan,blue,indigo,purple,rose,emerald,amber,slate',
            // Invoice customization
            'invoice_note' => 'nullable|string|max:1000',
            'show_customer_balance' => 'nullable|boolean',
            // Customer item types
            'customer_item_types' => 'nullable|array',
            'customer_item_types.*.value' => 'nullable|string|max:50',
            'customer_item_types.*.label' => 'nullable|string|max:100',
            // Treasury
            'treasury_opening_balance' => 'nullable|numeric|min:0',
        ]);

        // Save invoice contacts as JSON
        $contacts = $validated['contacts'] ?? [];
        // Filter out empty rows
        $contacts = array_values(array_filter($contacts, fn($c) => !empty($c['name']) && !empty($c['phone'])));
        $this->setSetting('invoice_contacts', json_encode($contacts, JSON_UNESCAPED_UNICODE));

        // Handle checkbox fields (not in $validated if unchecked)
        $this->setSetting('show_customer_balance', $request->boolean('show_customer_balance') ? '1' : '0');

        // Save customer item types as JSON
        $itemTypes = $request->input('customer_item_types', []);
        $itemTypes = array_values(array_filter($itemTypes, fn($t) => !empty($t['value']) && !empty($t['label'])));
        if (!empty($itemTypes)) {
            $this->setSetting('customer_item_types', json_encode($itemTypes, JSON_UNESCAPED_UNICODE));
            Cache::forget('customer_item_types');
        }

        // Exclude file fields and contacts from text settings
        $fileFields = ['logo', 'stamp', 'contacts', 'show_customer_balance', 'customer_item_types'];
        foreach ($validated as $key => $value) {
            if (!in_array($key, $fileFields)) {
                $this->setSetting($key, $value);
            }
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $oldLogo = $this->getSetting('company_logo');
            if ($oldLogo && \Storage::disk('public')->exists($oldLogo)) {
                \Storage::disk('public')->delete($oldLogo);
            }
            $logoPath = $request->file('logo')->store('settings', 'public');
            $this->setSetting('company_logo', $logoPath);
        }

        // Handle logo removal
        if ($request->boolean('remove_logo')) {
            $oldLogo = $this->getSetting('company_logo');
            if ($oldLogo && \Storage::disk('public')->exists($oldLogo)) {
                \Storage::disk('public')->delete($oldLogo);
            }
            $this->setSetting('company_logo', '');
        }

        // Handle stamp upload
        if ($request->hasFile('stamp')) {
            $oldStamp = $this->getSetting('company_stamp');
            if ($oldStamp && \Storage::disk('public')->exists($oldStamp)) {
                \Storage::disk('public')->delete($oldStamp);
            }
            $stampPath = $request->file('stamp')->store('settings', 'public');
            $this->setSetting('company_stamp', $stampPath);
        }

        // Handle stamp removal
        if ($request->boolean('remove_stamp')) {
            $oldStamp = $this->getSetting('company_stamp');
            if ($oldStamp && \Storage::disk('public')->exists($oldStamp)) {
                \Storage::disk('public')->delete($oldStamp);
            }
            $this->setSetting('company_stamp', '');
        }

        // Clear settings cache
        Cache::forget('company_settings');
        Cache::forget('sidebar_settings');

        return redirect()->route('settings.company')
            ->with('success', 'تم تحديث إعدادات الشركة بنجاح');
    }

    /**
     * Display users management.
     */
    public function users()
    {
        $users = User::with(['branch', 'roles'])
            ->latest()
            ->paginate(15);

        return view('settings.users', compact('users'));
    }

    /**
     * Show form to create a new user.
     */
    public function createUser()
    {
        $branches = \App\Models\Branch::where('is_active', true)->get();
        $roles = \Spatie\Permission\Models\Role::all();

        // الموظفين الذين لم يتم ربطهم بمستخدم بعد
        $availableEmployees = \App\Models\Employee::whereNull('user_id')
            ->where('is_active', true)
            ->get();

        return view('settings.users.create', compact('branches', 'roles', 'availableEmployees'));
    }

    /**
     * Store a new user.
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'branch_id' => 'nullable|exists:branches,id',
            'is_active' => 'boolean',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
            'employee_id' => 'nullable|exists:employees,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'branch_id' => $validated['branch_id'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if (!empty($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        // ربط المستخدم بالموظف إذا تم اختياره
        if (!empty($validated['employee_id'])) {
            \App\Models\Employee::where('id', $validated['employee_id'])
                ->update(['user_id' => $user->id]);
        }

        return redirect()->route('settings.users')
            ->with('success', 'تم إضافة المستخدم بنجاح');
    }

    /**
     * Show form to edit a user.
     */
    public function editUser(User $user)
    {
        $branches = \App\Models\Branch::where('is_active', true)->get();
        $roles = \Spatie\Permission\Models\Role::all();

        // الموظفين الذين لم يتم ربطهم بمستخدم بعد + الموظف الحالي للمستخدم
        $availableEmployees = \App\Models\Employee::where(function ($query) use ($user) {
            $query->whereNull('user_id')
                  ->orWhere('user_id', $user->id);
        })->where('is_active', true)->get();

        // الموظف الحالي للمستخدم
        $currentEmployee = \App\Models\Employee::where('user_id', $user->id)->first();

        return view('settings.users.edit', compact('user', 'branches', 'roles', 'availableEmployees', 'currentEmployee'));
    }

    /**
     * Update a user.
     */
    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'branch_id' => 'nullable|exists:branches,id',
            'is_active' => 'boolean',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
            'employee_id' => 'nullable|exists:employees,id',
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'branch_id' => $validated['branch_id'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];

        if (!empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);

        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        // إلغاء ربط الموظف القديم إن وجد
        \App\Models\Employee::where('user_id', $user->id)->update(['user_id' => null]);

        // ربط الموظف الجديد إن تم اختياره
        if (!empty($validated['employee_id'])) {
            \App\Models\Employee::where('id', $validated['employee_id'])
                ->update(['user_id' => $user->id]);
        }

        return redirect()->route('settings.users')
            ->with('success', 'تم تحديث بيانات المستخدم بنجاح');
    }

    /**
     * Delete a user.
     */
    public function destroyUser(User $user)
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return back()->with('error', 'لا يمكنك حذف حسابك الخاص');
        }

        // Check if user has sales
        if ($user->sales()->exists()) {
            return back()->with('error', 'لا يمكن حذف المستخدم لأنه لديه فواتير مسجلة');
        }

        $user->delete();

        return redirect()->route('settings.users')
            ->with('success', 'تم حذف المستخدم بنجاح');
    }

    /**
     * Display invoice settings.
     */
    public function invoices()
    {
        $settings = $this->getInvoiceSettings();

        return view('settings.invoices', compact('settings'));
    }

    /**
     * Update invoice settings.
     */
    public function updateInvoices(Request $request)
    {
        $validated = $request->validate([
            'invoice_prefix' => 'nullable|string|max:20',
            'invoice_start_number' => 'nullable|integer|min:1',
            'invoice_footer' => 'nullable|string',
            'invoice_terms' => 'nullable|string',
            'show_logo_on_invoice' => 'boolean',
            'show_customer_balance' => 'boolean',
            'show_item_discount' => 'boolean',
            'show_item_tax' => 'boolean',
            'default_payment_terms_days' => 'nullable|integer|min:0',
        ]);

        foreach ($validated as $key => $value) {
            $this->setSetting('invoice_' . $key, $value);
        }

        Cache::forget('invoice_settings');

        return redirect()->route('settings.invoices')
            ->with('success', 'تم تحديث إعدادات الفواتير بنجاح');
    }

    /**
     * Display roles management.
     */
    public function roles()
    {
        $roles = \Spatie\Permission\Models\Role::withCount('users', 'permissions')->get();

        return view('settings.roles.index', compact('roles'));
    }

    /**
     * Show form to create a new role.
     */
    public function createRole()
    {
        $permissions = \Spatie\Permission\Models\Permission::all()->groupBy(function ($permission) {
            return explode('_', $permission->name)[1] ?? 'other';
        });

        return view('settings.roles.create', compact('permissions'));
    }

    /**
     * Store a new role.
     */
    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = \Spatie\Permission\Models\Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('settings.roles')
            ->with('success', 'تم إضافة الدور بنجاح');
    }

    /**
     * Show form to edit a role.
     */
    public function editRole($roleId)
    {
        $role = \Spatie\Permission\Models\Role::findOrFail($roleId);
        $permissions = \Spatie\Permission\Models\Permission::all()->groupBy(function ($permission) {
            return explode('_', $permission->name)[1] ?? 'other';
        });
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('settings.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update a role.
     */
    public function updateRole(Request $request, $roleId)
    {
        $role = \Spatie\Permission\Models\Role::findOrFail($roleId);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'display_name' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->update([
            'name' => $validated['name'],
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('settings.roles')
            ->with('success', 'تم تحديث الدور بنجاح');
    }

    /**
     * Delete a role.
     */
    public function destroyRole($roleId)
    {
        $role = \Spatie\Permission\Models\Role::findOrFail($roleId);

        // Check if role has users
        if ($role->users()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف الدور لأنه مرتبط بمستخدمين');
        }

        $role->delete();

        return redirect()->route('settings.roles')
            ->with('success', 'تم حذف الدور بنجاح');
    }

    /**
     * Get company settings from database/cache.
     */
    protected function getCompanySettings(): array
    {
        return Cache::remember('company_settings', 3600, function () {
            return [
                'company_name' => $this->getSetting('company_name', ''),
                'company_name_en' => $this->getSetting('company_name_en', ''),
                'tax_number' => $this->getSetting('tax_number', ''),
                'commercial_register' => $this->getSetting('commercial_register', ''),
                'phone' => $this->getSetting('phone', ''),
                'supervisor_phone' => $this->getSetting('supervisor_phone', ''),
                'mobile' => $this->getSetting('mobile', ''),
                'fax' => $this->getSetting('fax', ''),
                'email' => $this->getSetting('email', ''),
                'website' => $this->getSetting('website', ''),
                'address' => $this->getSetting('address', ''),
                'city' => $this->getSetting('city', ''),
                'country' => $this->getSetting('country', 'المملكة العربية السعودية'),
                'postal_code' => $this->getSetting('postal_code', ''),
                'currency' => $this->getSetting('currency', 'SAR'),
                'currency_symbol' => $this->getSetting('currency_symbol', 'ر.س'),
                'tax_rate' => $this->getSetting('tax_rate', 15),
                'company_logo' => $this->getSetting('company_logo', ''),
                'company_stamp' => $this->getSetting('company_stamp', ''),
                'invoice_prefix' => $this->getSetting('invoice_prefix', 'INV-'),
                'invoice_footer' => $this->getSetting('invoice_footer', ''),
                'invoice_terms' => $this->getSetting('invoice_terms', ''),
                'invoice_contacts' => $this->getSetting('invoice_contacts', '[]'),
                'color_palette' => $this->getSetting('color_palette', 'cyan'),
                'invoice_note' => $this->getSetting('invoice_note', ''),
                'show_customer_balance' => $this->getSetting('show_customer_balance', '0'),
                'customer_item_types' => $this->getSetting('customer_item_types', ''),
                'treasury_opening_balance' => $this->getSetting('treasury_opening_balance', '0'),
            ];
        });
    }

    /**
     * Get invoice settings from database/cache.
     */
    protected function getInvoiceSettings(): array
    {
        return Cache::remember('invoice_settings', 3600, function () {
            return [
                'invoice_prefix' => $this->getSetting('invoice_invoice_prefix', 'INV-'),
                'invoice_start_number' => $this->getSetting('invoice_invoice_start_number', 1),
                'invoice_footer' => $this->getSetting('invoice_invoice_footer', ''),
                'invoice_terms' => $this->getSetting('invoice_invoice_terms', ''),
                'show_logo_on_invoice' => $this->getSetting('invoice_show_logo_on_invoice', true),
                'show_customer_balance' => $this->getSetting('invoice_show_customer_balance', true),
                'show_item_discount' => $this->getSetting('invoice_show_item_discount', true),
                'show_item_tax' => $this->getSetting('invoice_show_item_tax', true),
                'default_payment_terms_days' => $this->getSetting('invoice_default_payment_terms_days', 30),
            ];
        });
    }

    /**
     * Get a setting value from database.
     */
    protected function getSetting(string $key, $default = null)
    {
        // This assumes you have a settings table. If not, you can use config files or .env
        // For simplicity, we'll use database if available, otherwise return default
        try {
            $setting = \DB::table('settings')->where('key', $key)->first();
            return $setting ? $setting->value : $default;
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * Set a setting value in database.
     */
    protected function setSetting(string $key, $value): void
    {
        try {
            \DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now()]
            );
        } catch (\Exception $e) {
            // Log the error but don't throw - settings table might not exist
            \Log::warning("Could not save setting {$key}: " . $e->getMessage());
        }
    }

    /**
     * Display the data reset confirmation page.
     */
    public function resetData()
    {
        return view('settings.reset');
    }

    /**
     * Execute data reset - delete all transactional data, keep users/settings/master data.
     */
    public function confirmResetData(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'confirmation' => 'required|in:تصفير',
        ], [
            'password.required' => 'يجب إدخال كلمة المرور',
            'confirmation.required' => 'يجب كتابة كلمة "تصفير" للتأكيد',
            'confirmation.in' => 'يجب كتابة كلمة "تصفير" بالضبط للتأكيد',
        ]);

        if (!Hash::check($request->password, auth()->user()->password)) {
            return back()->withErrors(['password' => 'كلمة المرور غير صحيحة']);
        }

        try {
            DB::beginTransaction();

            // Tables to truncate (order matters for foreign keys)
            $tablesToTruncate = [
                'sale_return_items',
                'sale_returns',
                'purchase_return_items',
                'purchase_returns',
                'sale_items',
                'purchase_items',
                'stock_movements',
                'inventory_levels',
                'payments',
                'expenses',
                'commission_withdrawals',
                'sales_rep_inventory',
                'employee_transactions',
                'partner_transactions',
                'audit_logs',
                'sales',
                'purchases',
            ];

            // Disable foreign key checks for clean truncation
            $driver = DB::connection()->getDriverName();
            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            }

            foreach ($tablesToTruncate as $table) {
                try {
                    DB::table($table)->delete();
                } catch (\Exception $e) {
                    // Table might not exist, skip
                }
            }

            // Reset customer balances
            DB::table('customers')->update([
                'current_balance' => 0,
                'total_paid' => 0,
            ]);

            // Reset supplier balances
            try {
                DB::table('suppliers')->update(['total_paid' => 0]);
            } catch (\Exception $e) {}

            // Reset employee balances
            try {
                DB::table('employees')->update(['current_balance' => 0]);
            } catch (\Exception $e) {}

            // Reset partner balances
            try {
                DB::table('partners')->update(['current_balance' => 0]);
            } catch (\Exception $e) {}

            // Re-enable foreign key checks
            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            }

            DB::commit();

            return redirect()->route('settings.index')
                ->with('success', 'تم تصفير جميع البيانات بنجاح. يمكنك البدء من جديد.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'حدث خطأ أثناء التصفير: ' . $e->getMessage()]);
        }
    }
}
