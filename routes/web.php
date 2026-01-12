<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SalesRepController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PurchaseController;
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

    // Sales Representatives (المندوبين)
    Route::resource('sales-reps', SalesRepController::class);

    // Warehouses (المخازن)
    Route::resource('warehouses', WarehouseController::class);
    Route::get('warehouses/transfer', [WarehouseController::class, 'showTransferForm'])->name('warehouses.transfer');
    Route::post('warehouses/transfer', [WarehouseController::class, 'transfer'])->name('warehouses.process-transfer');

    // Sales (المبيعات)
    Route::resource('sales', SaleController::class);

    // Invoices (الفواتير)
    Route::resource('invoices', InvoiceController::class);
    Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');

    // Purchases (المشتريات)
    Route::resource('purchases', PurchaseController::class);

    // Suppliers (الموردين)
    Route::resource('suppliers', SupplierController::class);

    // Employees (الموظفين)
    Route::resource('employees', EmployeeController::class);

    // Expenses (المصروفات)
    Route::resource('expenses', ExpenseController::class);
    Route::resource('expense-payment-methods', ExpensePaymentMethodController::class)
        ->except(['show']);

    // Reports (التقارير)
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/profits', [ReportController::class, 'profits'])->name('profits');
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');
        Route::get('/customers', [ReportController::class, 'customers'])->name('customers');
        Route::get('/stock-movements', [ReportController::class, 'stockMovements'])->name('stock-movements');
    });

    // Settings (الإعدادات)
    Route::prefix('settings')->name('settings.')->group(function () {
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
    });

    // Profile (الملف الشخصي)
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password');
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
