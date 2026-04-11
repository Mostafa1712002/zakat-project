<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SalesRepController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\PurchaseQuotationController;
use App\Http\Controllers\SupplierController;
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
use App\Http\Controllers\SalesRepDashboardController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\EmployeeTransactionController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\PartnerTransactionController;
use App\Http\Controllers\ProfitDistributionController;
use App\Http\Controllers\TreasuryController;
use App\Http\Controllers\SalesRepAccountController;
use App\Http\Controllers\BranchController;

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

    // Categories (الأقسام)
    Route::resource('categories', CategoryController::class);

    // Products (الأصناف)
    Route::resource('products', ProductController::class);

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

    // Sales Representatives (المندوبين) - Admin Only
    Route::middleware(['admin_only'])->group(function () {
        Route::resource('sales-reps', SalesRepController::class);

        // خزينة المندوب
        Route::get('sales-reps/{salesRep}/treasury', [SalesRepController::class, 'treasuryStatement'])->name('sales-reps.treasury');
        Route::get('sales-reps/{salesRep}/deposit', [SalesRepController::class, 'showDepositForm'])->name('sales-reps.deposit.form');
        Route::post('sales-reps/{salesRep}/deposit', [SalesRepController::class, 'deposit'])->name('sales-reps.deposit');
        Route::get('sales-reps/{salesRep}/withdraw', [SalesRepController::class, 'showWithdrawForm'])->name('sales-reps.withdraw.form');
        Route::post('sales-reps/{salesRep}/withdraw', [SalesRepController::class, 'withdraw'])->name('sales-reps.withdraw');

        // سحب عمولة المندوب
        Route::get('sales-reps/{salesRep}/withdraw-commission', [SalesRepController::class, 'showWithdrawCommission'])->name('sales-reps.withdraw-commission.form');
        Route::post('sales-reps/{salesRep}/withdraw-commission', [SalesRepController::class, 'withdrawCommission'])->name('sales-reps.withdraw-commission.store');
    });

    // Branches (الفروع)
    Route::resource('branches', BranchController::class)->except(['show']);

    // Warehouses (المخازن)
    Route::get('warehouses/transfer', [WarehouseController::class, 'showTransferForm'])->name('warehouses.transfer');
    Route::post('warehouses/transfer', [WarehouseController::class, 'transfer'])->name('warehouses.process-transfer');
    Route::get('warehouses/{warehouse}/products-with-stock', [WarehouseController::class, 'productsWithStock'])->name('warehouses.products-with-stock');
    Route::resource('warehouses', WarehouseController::class);

    // Sales (المبيعات)
    Route::get('sales/get-stock', [SaleController::class, 'getStock'])->name('sales.get-stock');
    Route::get('sales/{sale}/pdf', [SaleController::class, 'pdf'])->name('sales.pdf');
    Route::post('sales/{sale}/confirm', [SaleController::class, 'confirm'])->name('sales.confirm');
    Route::post('sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');
    Route::get('sales/{sale}/zatca-xml', [SaleController::class, 'downloadXml'])->name('sales.zatca.xml');
    Route::resource('sales', SaleController::class);

    // Sales Quotations (تسعيرات المبيعات)
    Route::get('quotations/{sale}/pdf', [QuotationController::class, 'pdf'])->name('quotations.pdf');
    Route::post('quotations/{sale}/confirm', [QuotationController::class, 'confirm'])->name('quotations.confirm');
    Route::resource('quotations', QuotationController::class);

    // Purchases (المشتريات)
    Route::get('purchases/{purchase}/pdf', [PurchaseController::class, 'pdf'])->name('purchases.pdf');
    Route::post('purchases/{purchase}/confirm', [PurchaseController::class, 'confirm'])->name('purchases.confirm');
    Route::resource('purchases', PurchaseController::class);

    // Purchase Quotations (تسعيرات المشتريات)
    Route::get('purchase-quotations/{purchase}/pdf', [PurchaseQuotationController::class, 'pdf'])->name('purchase-quotations.pdf');
    Route::post('purchase-quotations/{purchase}/confirm', [PurchaseQuotationController::class, 'confirm'])->name('purchase-quotations.confirm');
    Route::resource('purchase-quotations', PurchaseQuotationController::class);

    // Purchase Returns (مرتجعات المشتريات)
    Route::resource('purchase-returns', PurchaseReturnController::class)->except(['edit', 'update']);

    // Suppliers (الموردين)
    Route::get('suppliers/{supplier}/products', [SupplierController::class, 'products'])->name('suppliers.products');
    Route::resource('suppliers', SupplierController::class);
    Route::middleware(['feature:payments'])->group(function () {
        Route::get('suppliers/{supplier}/pay', [PaymentController::class, 'showPayToSupplier'])->name('suppliers.pay.form');
        Route::post('suppliers/{supplier}/pay', [PaymentController::class, 'payToSupplier'])->name('suppliers.pay');
    });

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
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');
        Route::get('/customers', [ReportController::class, 'customers'])->name('customers');
        Route::get('/stock-movements', [ReportController::class, 'stockMovements'])->name('stock-movements');

        // Profits Report - Admin Only
        Route::middleware(['admin_only'])->group(function () {
            Route::get('/profits', [ReportController::class, 'profits'])->name('profits');
        });

        Route::middleware(['feature:report_sales_reps'])->group(function () {
            Route::get('/sales-reps', [ReportController::class, 'salesRepsPerformance'])->name('sales-reps');
            Route::get('/sales-reps/{salesRep}', [ReportController::class, 'salesRepDetail'])->name('sales-rep-detail');
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

    // Sales Rep Dashboard (لوحة تحكم المندوب)
    Route::middleware(['feature:sales_rep_dashboard'])->prefix('my-dashboard')->name('sales-rep.')->group(function () {
        Route::get('/', [SalesRepDashboardController::class, 'index'])->name('dashboard');
        Route::get('/customers', [SalesRepDashboardController::class, 'customers'])->name('customers');
        Route::get('/sales', [SalesRepDashboardController::class, 'sales'])->name('sales');
        Route::get('/collections', [SalesRepDashboardController::class, 'collections'])->name('collections');
        Route::get('/reports', [SalesRepDashboardController::class, 'reports'])->name('reports');

        // حساب المندوب - خزينتي، مصروفاتي، مخزني
        Route::get('/treasury', [SalesRepAccountController::class, 'myTreasury'])->name('treasury');
        Route::get('/expenses', [SalesRepAccountController::class, 'myExpenses'])->name('expenses');
        Route::post('/expenses', [SalesRepAccountController::class, 'storeExpense'])->name('expenses.store');
        Route::get('/inventory', [SalesRepAccountController::class, 'myInventory'])->name('inventory');
    });

    // Admin - إدارة خزينات ومخازن المندوبين
    Route::middleware(['admin_only'])->prefix('admin')->name('admin.')->group(function () {
        // خزينات المندوبين
        Route::get('sales-rep-treasury', [SalesRepAccountController::class, 'treasuryIndex'])->name('sales-rep-treasury.index');
        Route::get('sales-rep-treasury/{salesRep}', [SalesRepAccountController::class, 'treasuryShow'])->name('sales-rep-treasury.show');
        Route::post('sales-rep-treasury/{salesRep}/withdraw', [SalesRepAccountController::class, 'withdrawToMain'])->name('sales-rep-treasury.withdraw');

        // مخازن المندوبين
        Route::get('sales-rep-inventory', [SalesRepAccountController::class, 'inventoryIndex'])->name('sales-rep-inventory.index');
        Route::get('sales-rep-inventory/{salesRep}', [SalesRepAccountController::class, 'inventoryShow'])->name('sales-rep-inventory.show');
        Route::post('sales-rep-inventory/{salesRep}/allocate', [SalesRepAccountController::class, 'allocateStock'])->name('sales-rep-inventory.allocate');
        Route::post('sales-rep-inventory/{salesRep}/return', [SalesRepAccountController::class, 'returnStock'])->name('sales-rep-inventory.return');
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
