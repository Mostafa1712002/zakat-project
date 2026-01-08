<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
        ]);

        // Store settings
        foreach ($validated as $key => $value) {
            $this->setSetting($key, $value);
        }

        // Handle logo upload if present
        if ($request->hasFile('logo')) {
            $request->validate(['logo' => 'image|mimes:jpeg,png,jpg,gif|max:2048']);
            $logoPath = $request->file('logo')->store('settings', 'public');
            $this->setSetting('company_logo', $logoPath);
        }

        // Clear settings cache
        Cache::forget('company_settings');

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

        return view('settings.users.create', compact('branches', 'roles'));
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

        return view('settings.users.edit', compact('user', 'branches', 'roles'));
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
                'invoice_prefix' => $this->getSetting('invoice_prefix', 'INV-'),
                'invoice_footer' => $this->getSetting('invoice_footer', ''),
                'invoice_terms' => $this->getSetting('invoice_terms', ''),
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
}
