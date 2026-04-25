<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpensePaymentMethodController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\UxAnalysisController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\EmployeeTransactionController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\PartnerTransactionController;
use App\Http\Controllers\ProfitDistributionController;
use App\Http\Controllers\TreasuryController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;

// Dynamic favicon from company logo
Route::get('/favicon.ico', function () {
    try {
        $logo = \DB::table('settings')->where('key', 'company_logo')->value('value');
        if ($logo) {
            $path = storage_path('app/public/' . $logo);
            if (file_exists($path)) {
                $mime = mime_content_type($path);
                return response()->file($path, ['Content-Type' => $mime, 'Cache-Control' => 'public, max-age=86400']);
            }
        }
    } catch (\Exception $e) {}
    $fallback = public_path('logo.png');
    if (file_exists($fallback)) {
        return response()->file($fallback, ['Content-Type' => 'image/png', 'Cache-Control' => 'public, max-age=86400']);
    }
    abort(404);
});

// Authentication Routes
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login')->middleware('guest');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('guest');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout')->middleware('auth');

// Protected Routes (require authentication)
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Customers (العملاء)
    Route::resource('customers', CustomerController::class);
    Route::middleware(['feature:customer_target'])->group(function () {
        Route::get('customers/{customer}/withdraw-target', [CustomerController::class, 'showWithdrawTarget'])->name('customers.withdraw-target.form');
        Route::post('customers/{customer}/withdraw-target', [CustomerController::class, 'withdrawTarget'])->name('customers.withdraw-target.store');
    });
    Route::middleware(['feature:payments'])->group(function () {
        Route::get('customers/{customer}/collect', [PaymentController::class, 'showCollectFromCustomer'])->name('customers.collect.form');
        Route::post('customers/{customer}/collect', [PaymentController::class, 'collectFromCustomer'])->name('customers.collect');
    });

    // Branches (الفروع)
    Route::resource('branches', BranchController::class)->except(['show']);

    // =====================================================
    // Admin Only Routes - Restricted from Employee Access
    // =====================================================
    Route::middleware(['admin_only'])->group(function () {
        // Employees Management (إدارة الموظفين)
        Route::resource('employees', EmployeeController::class);

        // Employee Transactions (مرتبات وسحوبات الموظفين)
        Route::get('employee-transactions/employee/{employee}', [EmployeeTransactionController::class, 'employeeHistory'])->name('employee-transactions.employee-history');
        Route::resource('employee-transactions', EmployeeTransactionController::class);

        // Partners (الشركاء)
        Route::resource('partners', PartnerController::class);

        // Partner Transactions (معاملات الشركاء)
        Route::get('partner-transactions/partner/{partner}', [PartnerTransactionController::class, 'partnerHistory'])->name('partner-transactions.partner-history');
        Route::resource('partner-transactions', PartnerTransactionController::class);

        // Profit Distribution (توزيع الأرباح)
        Route::get('profit-distribution', [ProfitDistributionController::class, 'index'])->name('profit-distribution.index');
        Route::get('profit-distribution/create', [ProfitDistributionController::class, 'create'])->name('profit-distribution.create');
        Route::post('profit-distribution', [ProfitDistributionController::class, 'store'])->name('profit-distribution.store');
        Route::post('profit-distribution/preview', [ProfitDistributionController::class, 'preview'])->name('profit-distribution.preview');
        Route::get('profit-distribution/{period}', [ProfitDistributionController::class, 'show'])->name('profit-distribution.show');
    });

    // Expenses (المصروفات)
    Route::resource('expenses', ExpenseController::class);
    Route::resource('expense-categories', ExpenseCategoryController::class)->except(['show']);
    Route::resource('expense-payment-methods', ExpensePaymentMethodController::class)
        ->except(['show']);

    // Treasury (الخزنة) - Admin Only
    Route::middleware(['admin_only'])->group(function () {
        Route::get('treasury', [TreasuryController::class, 'index'])->name('treasury.index');
    });

    // Payments (التحصيلات والمدفوعات)
    Route::middleware(['feature:payments'])->group(function () {
        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    });

    // Reports (التقارير)
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/customers', [ReportController::class, 'customers'])->name('customers');

        // Profits Report - Admin Only
        Route::middleware(['admin_only'])->group(function () {
            Route::get('/profits', [ReportController::class, 'profits'])->name('profits');
        });
    });

    // Settings (الإعدادات) - Admin Only
    Route::middleware(['admin_only'])->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::get('/company', [SettingController::class, 'company'])->name('company');
        Route::post('/company', [SettingController::class, 'updateCompany'])->name('company.update');
        Route::get('/users', [SettingController::class, 'users'])->name('users');
        Route::get('/users/create', [SettingController::class, 'createUser'])->name('users.create');
        Route::post('/users', [SettingController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{user}/edit', [SettingController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [SettingController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [SettingController::class, 'destroyUser'])->name('users.destroy');
        Route::get('/invoices', [SettingController::class, 'invoices'])->name('invoices');
        Route::post('/invoices', [SettingController::class, 'updateInvoices'])->name('invoices.update');

        // Roles Management
        Route::get('/roles', [SettingController::class, 'roles'])->name('roles');
        Route::get('/roles/create', [SettingController::class, 'createRole'])->name('roles.create');
        Route::post('/roles', [SettingController::class, 'storeRole'])->name('roles.store');
        Route::get('/roles/{role}/edit', [SettingController::class, 'editRole'])->name('roles.edit');
        Route::put('/roles/{role}', [SettingController::class, 'updateRole'])->name('roles.update');
        Route::delete('/roles/{role}', [SettingController::class, 'destroyRole'])->name('roles.destroy');

        // Data Reset (التصفير)
        Route::get('/reset', [SettingController::class, 'resetData'])->name('reset');
        Route::post('/reset', [SettingController::class, 'confirmResetData'])->name('reset.confirm');
    });

    // Profile (الملف الشخصي)
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password');
    });

    // Employee Dashboard (لوحة تحكم الموظف)
    Route::middleware(['feature:employee_dashboard'])->prefix('employee-portal')->name('employee.')->group(function () {
        Route::get('/', [EmployeeDashboardController::class, 'index'])->name('dashboard');
        Route::get('/statement', [EmployeeDashboardController::class, 'statement'])->name('statement');
        Route::get('/reports', [EmployeeDashboardController::class, 'reports'])->name('reports');
        Route::get('/profile', [EmployeeDashboardController::class, 'profile'])->name('profile');
    });

    // Portfolio (معرض الأعمال)
    Route::get('/portfolio', [PortfolioController::class, 'index'])->name('portfolio.index');

    // UX Analysis (تحليل تجربة المستخدم)
    Route::get('/ux-analysis', [UxAnalysisController::class, 'index'])->name('ux-analysis.index');
    Route::get('/ux-analysis/{module}', [UxAnalysisController::class, 'show'])->name('ux-analysis.show');

    // ZATCA Dashboard (لوحة متابعة الفوترة الإلكترونية)
    Route::get('zatca/dashboard', [App\Http\Controllers\ZatcaController::class, 'dashboard'])->name('zatca.dashboard');

    // ====================================================
    // AMMRK Admin Module (Phase 2 — Foundation)
    // ====================================================
    Route::prefix('admin')->name('admin.')->group(function () {
        // Settings — system + zatca settings (system permission gates the system tab)
        Route::middleware(['can:settings.system'])->prefix('settings')->name('settings.')->group(function () {
            Route::get('/general', [AdminSettingsController::class, 'general'])->name('general');
            Route::put('/general', [AdminSettingsController::class, 'updateGeneral'])->name('general.update');
            Route::get('/zatca', [AdminSettingsController::class, 'zatca'])->name('zatca');
            Route::put('/zatca', [AdminSettingsController::class, 'updateZatca'])->name('zatca.update');
        });

        // Roles & Permissions
        Route::middleware(['can:roles.manage-permissions'])->prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [AdminRoleController::class, 'index'])->name('index');
            Route::get('/{role}/edit', [AdminRoleController::class, 'edit'])->name('edit');
            Route::put('/{role}', [AdminRoleController::class, 'update'])->name('update');
        });
    });

    // Features Management (إدارة المميزات) - Super Admin Only
    Route::middleware(['super_admin'])->prefix('features')->name('features.')->group(function () {
        Route::get('/', [FeatureController::class, 'index'])->name('index');
        Route::get('/create', [FeatureController::class, 'create'])->name('create');
        Route::post('/', [FeatureController::class, 'store'])->name('store');
        Route::get('/{feature}/edit', [FeatureController::class, 'edit'])->name('edit');
        Route::put('/{feature}', [FeatureController::class, 'update'])->name('update');
        Route::delete('/{feature}', [FeatureController::class, 'destroy'])->name('destroy');
        Route::post('/{feature}/toggle', [FeatureController::class, 'toggle'])->name('toggle');
        Route::post('/enable-all', [FeatureController::class, 'enableAll'])->name('enable-all');
        Route::post('/disable-all', [FeatureController::class, 'disableAll'])->name('disable-all');
    });
});

// ============================================
// Phase 3: Catalog (Service Types, Services, Units)
// ============================================
// Each resource is gated at view-level via Spatie permission middleware;
// finer-grained create/edit/delete checks are enforced by the catalog
// Policies (Gate) and `@can` directives in the views. New `units.*`
// permissions added in RolePermissionSeeder for this phase.
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::middleware('permission:service-types.view')->group(function () {
        Route::resource('service-types', \App\Http\Controllers\Admin\ServiceTypeController::class)
            ->except('show');
    });

    Route::middleware('permission:services.view')->group(function () {
        Route::resource('services', \App\Http\Controllers\Admin\ServiceController::class)
            ->except('show');
    });

    Route::middleware('permission:units.view')->group(function () {
        Route::resource('units', \App\Http\Controllers\Admin\UnitController::class)
            ->except('show');
    });
});
