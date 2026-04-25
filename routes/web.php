<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
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
use App\Http\Controllers\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;

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
    // Dashboard — Phase 7 (Admin\DashboardController replaces Phase 1 stub)
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Customers — moved to Phase 4 admin block (admin.customers.*).
    // Old top-level customer/withdraw-target/collect routes removed; the new
    // CustomerController lives under app/Http/Controllers/Admin/.

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

        // Phase 8.1 — Audit Logs (read-only)
        Route::middleware(['permission:settings.system'])
            ->get('audit-logs', [AdminAuditLogController::class, 'index'])
            ->name('audit-logs.index');
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

// ============================================
// Phase 4: Customer Management
// ============================================
// Resource is gated by either `customers.view-all` (admins/accountants) or
// `customers.view-own` (account managers); per-record visibility is then
// enforced by CustomerPolicy + the AccountManagerScope global query scope.
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::middleware('permission:customers.view-all|customers.view-own')->group(function () {
        Route::resource('customers', \App\Http\Controllers\Admin\CustomerController::class);
    });
});

// ============================================
// Phase 5: Sales (Quotes)
// ============================================
// Resource is gated at view-level by either `quotes.view-all` (admins/
// accountants) or `quotes.view-own` (account managers). Workflow endpoints
// (submit/approve/reject) are gated by QuotePolicy on top of the view
// permission, so an Account Manager can submit but cannot approve.
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::middleware('permission:quotes.view-all|quotes.view-own')->group(function () {
        Route::resource('quotes', \App\Http\Controllers\Admin\QuoteController::class);
        Route::post('quotes/{quote}/submit', [\App\Http\Controllers\Admin\QuoteController::class, 'submit'])
            ->name('quotes.submit');
        Route::post('quotes/{quote}/approve', [\App\Http\Controllers\Admin\QuoteController::class, 'approve'])
            ->name('quotes.approve');
        Route::post('quotes/{quote}/reject', [\App\Http\Controllers\Admin\QuoteController::class, 'reject'])
            ->name('quotes.reject');
    });
});

// ============================================
// Phase 5b: Invoices + ZATCA
// ============================================
// Resource is gated at view-level by either `invoices.view-all` (admins/
// accountants) or `invoices.view-own` (account managers). Action endpoints
// (issue / resend-zatca / convert / cancel) are gated by InvoicePolicy on
// top of the view permission. Cancellation goes through `destroy`.
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::middleware('permission:invoices.view-all|invoices.view-own')->group(function () {
        Route::resource('invoices', \App\Http\Controllers\Admin\InvoiceController::class);
        Route::post('quotes/{quote}/convert', [\App\Http\Controllers\Admin\InvoiceController::class, 'convertFromQuote'])
            ->name('quotes.convert');
        Route::post('invoices/{invoice}/issue', [\App\Http\Controllers\Admin\InvoiceController::class, 'issue'])
            ->name('invoices.issue');
        Route::post('invoices/{invoice}/resend-zatca', [\App\Http\Controllers\Admin\InvoiceController::class, 'resendToZatca'])
            ->name('invoices.resend-zatca');
        Route::get('invoices/{invoice}/pdf', [\App\Http\Controllers\Admin\InvoiceController::class, 'pdf'])
            ->name('invoices.pdf');
    });
});

// ============================================
// Phase 6: Treasury (Payments + Treasuries)
// ============================================
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('payments', \App\Http\Controllers\Admin\PaymentController::class)
        ->only(['index', 'create', 'store', 'show', 'destroy'])
        ->middleware([
            'index'   => 'permission:payments.view',
            'create'  => 'permission:payments.create',
            'store'   => 'permission:payments.create',
            'show'    => 'permission:payments.view',
            'destroy' => 'permission:payments.refund',
        ]);

    Route::resource('treasuries', \App\Http\Controllers\Admin\TreasuryController::class)
        ->middleware([
            'index'   => 'permission:payments.view',
            'create'  => 'permission:settings.system',
            'store'   => 'permission:settings.system',
            'edit'    => 'permission:settings.system',
            'update'  => 'permission:settings.system',
            'destroy' => 'permission:settings.system',
        ]);
});

// ============================================
// Phase 7: Reports (Financial + Operational)
// ============================================
// Financial reports gated by `reports.financial`; operational reports gated by
// `reports.operational`. CSV export reuses the same controller and is gated by
// either permission depending on report type (handled at route level).
Route::middleware(['auth'])->prefix('admin/reports')->name('admin.reports.')->group(function () {
    Route::middleware('permission:reports.financial')->group(function () {
        Route::get('revenue', [AdminReportController::class, 'revenue'])->name('revenue');
        Route::get('vat', [AdminReportController::class, 'vat'])->name('vat');
        Route::get('customer-debt', [AdminReportController::class, 'customerDebt'])->name('customer-debt');
    });

    Route::middleware('permission:reports.operational')->group(function () {
        Route::get('quotes-pending', [AdminReportController::class, 'pendingQuotes'])->name('quotes-pending');
        Route::get('zatca-failed', [AdminReportController::class, 'failedZatca'])->name('zatca-failed');
        Route::get('events-calendar', [AdminReportController::class, 'eventsCalendar'])->name('events-calendar');
    });

    // Export accepts both permissions; route param differentiates report type.
    Route::middleware('permission:reports.financial|reports.operational')
        ->get('export/{type}', [AdminReportController::class, 'export'])
        ->where('type', 'revenue|vat|customer-debt|quotes-pending|zatca-failed|events-calendar')
        ->name('export');
});
