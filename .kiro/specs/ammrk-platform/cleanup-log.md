# Phase 1 Cleanup Log

Branch: `ammrk-v2`
Date: 2026-04-25

## Spec Conflict Resolved

**Unit model**: `tasks.md` Task 1.1 says delete and recreate. User's Phase 1 instructions say **keep** Unit (will be replaced in Phase 3). Following the more recent user instruction — **Unit, UnitSeeder, units migration KEPT**. Updating `tasks.md` accordingly.

## Files To Delete

### Models (app/Models/)
- Product.php
- ProductPrice.php
- InventoryLevel.php
- StockMovement.php
- Warehouse.php
- Grade.php
- Category.php (per user: delete WITH inventory)
- Supplier.php
- SalesRep.php
- SalesRepInventory.php
- SalesRepStockMovement.php
- SalesRepTransaction.php
- CommissionWithdrawal.php
- SaleReturn.php
- SaleReturnItem.php
- PurchaseReturn.php
- PurchaseReturnItem.php
- Sale.php
- SaleItem.php
- Purchase.php
- PurchaseItem.php

### Controllers (app/Http/Controllers/)
- ProductController.php
- WarehouseController.php
- CategoryController.php
- SupplierController.php
- SalesRepController.php
- SalesRepAccountController.php
- SalesRepDashboardController.php
- SaleController.php
- PurchaseController.php
- QuotationController.php (sales quotations — old)
- PurchaseQuotationController.php
- PurchaseReturnController.php

### Middleware
- app/Http/Middleware/SalesRepAccessMiddleware.php

### Traits
- app/Traits/SalesRepScope.php (used by Customer, Payment)

### Console
- app/Console/Commands/FixDiscountTotals.php (references Sale/SaleItem/Purchase)

### Views (resources/views/)
- products/
- warehouses/
- categories/
- suppliers/
- sales-reps/
- sale-returns/ (if exists)
- sales/
- purchases/
- quotations/
- purchase-quotations/
- purchase-returns/
- sales-rep-dashboard/
- sales-rep-account/
- sales-rep-inventory/ (under admin/)
- sales-rep-treasury/ (under admin/)
- admin/ (entire dir — only contains sales-rep stuff)

### Migrations (database/migrations/)
All migrations creating or altering tables for deleted domains. Listed in detail in deletion script.

### Factories
- None directly (only UserFactory exists; safe).

## Files To Modify (Surviving Files With References)

### Models
- **User.php**: remove `sales()` relation, `salesRep()` relation, `isSalesRep()`, `getSalesRepIdAttribute()`
- **Customer.php**: remove `salesRep()`, `sales()`, `recalculateBalance()` (uses Sale), `getTotalPurchasesAttribute()` (uses Sale), comment out `payments()` payable_type ref to self stays. Drop `SalesRepScope` trait usage.
- **Payment.php**: remove `salesRep()`, `sale()`, `purchase()` belongsTo. Drop `SalesRepScope` trait usage.
- **Branch.php**: remove `warehouses()` relation
- **Employee.php**: remove `salesRep()` relation
- **Expense.php**: remove `salesRep()` relation

### Controllers (clean references; gut methods that depend on deleted models)
- DashboardController.php
- ReportController.php
- ZatcaController.php (will be rewired in Phase 5; gut Sale references for now)
- CustomerController.php
- PaymentController.php
- EmployeeTransactionController.php
- TreasuryController.php
- PartnerTransactionController.php
- ExpenseCategoryController.php
- ExpenseController.php

### Routes
- routes/web.php: remove imports + route definitions for all deleted controllers. Remove `sales-rep.*` group, `admin.*` group (sales-rep treasury/inventory), `feature:sales_rep_dashboard` group.

### Bootstrap
- bootstrap/app.php: remove `SalesRepAccessMiddleware` from `web(append:)` and from `alias[]`.

### Layouts
- resources/views/layouts/app.blade.php: remove sidebar items for products, sales, purchases, warehouses, suppliers, sales-reps, quotations, purchase-quotations, purchase-returns, categories. Remove the entire sales-rep portal block.

## NOT Touched (Phase 2 Or Later Cleanup)

- **app/Models/Feature.php** + **database/seeders/FeatureSeeder.php** + SiteFeatureSeeder.php: likely seed `sales`, `products`, `sales_reps`, `purchases` feature flags. Feature model stays per user spec ("will be repurposed"). Seeded data is DB-only and DB will be rebuilt fresh — leave files now, flag as Phase 2 cleanup item.
- **app/Helpers/FeatureHelper.php**: passes through feature names; no code refs to deleted models.

## Verification Plan

1. `composer dump-autoload`
2. `php artisan config:clear && route:clear && view:clear`
3. `php artisan route:list` — must boot routes without error
4. `grep -rE "route\('(products|sales|purchases|suppliers|warehouses|sales-reps|quotations|purchase-quotations|purchase-returns|categories|sale-returns|sales-rep-)" resources/views/` — should be zero hits
5. `git status` to confirm changes

## Route Count Before

**241 routes** (baseline before Phase 1 cleanup)

## Route Count After

**124 routes** (after Phase 1 cleanup — 117 routes removed, ~49% reduction)

## Verification Results

- ✅ `composer dump-autoload` — 6807 classes
- ✅ `php artisan config:clear && route:clear && view:clear` — all clean
- ✅ `php artisan route:list` — boots cleanly, 124 routes
- ✅ `php artisan view:cache` — all blade templates compile
- ✅ `php artisan about` — Laravel 12.44.0 boots
- ✅ `php -l` on every file in `app/` — no syntax errors
- ✅ `grep -rE "route\('(products|sales|purchases|suppliers|...)\." resources/views/` — zero hits

## Unexpected Findings

1. **Unit migration**: `tasks.md` planned to delete Unit; user prompt said keep. Resolved by keeping Unit + UnitSeeder + units migration.
2. **Phase 1 directory mismatch**: User's prompt said `/home/mostafa/www/zakat-project/` but the `claude.md` `Working directory` was `/home/mostafa/www/crm`. The crm path doesn't exist; zakat-project is correct. Confirmed via `git status` showing `ammrk-v2` branch.
3. **Laravel 11/12 — no `app/Http/Kernel.php`**: Middleware registration is in `bootstrap/app.php`. Cleaned both `web(append:)` and `alias[]`.
4. **Sidebar layout**: Had 132 lines of role-specific menus referencing 9+ deleted route groups. Replaced with a slimmed nav focused on customer/treasury/zatca/settings (the surviving domains).
5. **`add_employee_role_and_permissions` migration**: Inserts permission strings like `view_products`, `view_warehouses`, `view_suppliers`. Originally left as-is for Phase 2. **DELETED at start of Phase 2** because the migration would seed legacy "employee" role + stale permission strings before `RolePermissionSeeder` runs, polluting `roles` + `permissions` tables on `migrate:fresh`. The new RolePermissionSeeder fully replaces it. (File: `database/migrations/2026_01_29_122411_add_employee_role_and_permissions.php`)
6. **`SettingController::resetData`**: Hardcodes table names like `sale_items`, `sales`, `purchases` to truncate. Each is wrapped in try/catch — non-existent tables are silently skipped. Safe for fresh DB.
7. **Customer migration `sales_rep_id` column**: Still in customers table schema as plain `unsignedBigInteger` (no FK constraint). Phase 4 redoes customers — leaving column to avoid altering until DB rebuild.
8. **Feature seeder data**: `database/seeders/FeatureSeeder.php` and `SiteFeatureSeeder.php` likely seed feature flags for deleted domains (`sales`, `products`, `sales_reps`, etc.). Not deleted — flagged as Phase 2 follow-up since Feature model is repurposed and DB is being rebuilt.

## Phase 2 Environment Note

**Local default `php` (8.5) is missing `pdo_sqlite`.** Use `php8.2 artisan ...` for every command (migrate, db:seed, route:list, tinker, serve). Installing `php8.5-sqlite3` requires sudo not available in this session. Phase 2 + later phases run via `php8.2`.

## Migrations Deleted (35 files)

- 1× pre-2026 (`2024_01_25_000001_add_invoice_links_to_payments`)
- 14× 2026_01_05 (categories, products, product_prices, sales_reps, suppliers, warehouses, inventory_levels, stock_movements, purchases, sales, purchase_items, sale_items, sale_returns, purchase_returns)
- 4× 2026_01_15/29 (sales_rep_warehouse, sales_rep_id_to_payments, type_to_sales_reps, sales_rep_treasury_and_expenses)
- 4× 2026_02_02/05/17 (sales_rep_inventory, commission_withdrawals, type_nullable_sales_reps, product_supplier)
- 5× 2026_02_18/22 (merge_categories_into_suppliers, fix_sales_rep_treasury_balances, fix_customer_balance_double_counting, category_id_to_suppliers, recalculate_customer_balances, supplier_id_to_products, fix_sales_remaining_amount)
- 2× 2026_02_25 (warehouse_branch_id_nullable, branch_id_nullable_in_purchases_and_sales)
- 1× 2026_02_26 (unify_branch_schemas — references products/sale_items/purchase_items)
- 3× 2026_04 (zatca_fields_to_sales, missing_invoice_links_to_payments, zatca_phase2_fields_to_sales)

Surviving migrations: 25 (users, cache, jobs, permissions, branches, audit_logs, branch_to_users, units, customers, employees, expense_categories, expenses, payments, expense_payment_methods, features, is_super_admin, employee_transactions, partners, partner_transactions, employee_partner_to_expenses, employee_role_and_permissions, target_fields_to_customers, item_type_to_customers, settings).

## Phase 4 Customer Module Notes

- **Old `App\Models\Customer` removed** — replaced by `App\Domain\Customer\Models\Customer`. References in `DashboardController` and `ReportController` repointed to the new namespace; `PaymentController::collectFromCustomer` and `showCollectFromCustomer` removed entirely (will be rebuilt in Phase 6).
- **Old customer migrations deleted**: `2026_01_05_182913_create_customers_table.php`, `2026_02_02_100000_add_target_fields_to_customers_table.php`, `2026_02_17_100610_add_item_type_to_customers_table.php`. Replaced by `2026_04_25_140000_create_customers_table.php` and `2026_04_25_140001_create_customer_contacts_table.php` matching the spec.
- **Old views removed**: `resources/views/customers/` directory deleted. New views at `resources/views/admin/customers/`.
- **Old routes removed**: top-level `Route::resource('customers', ...)`, `customers/{customer}/withdraw-target`, `customers/{customer}/collect`. New resource lives under `admin/customers` per Phase 4 spec.
- **Sidebar updated**: `route('customers.index')` -> `route('admin.customers.index')` in `resources/views/layouts/app.blade.php`.
- **`reports/customers.blade.php` left as-is** — it references stats variables not provided by the controller stub. Will be rewritten in Phase 7. Route still resolves but rendering will fail if hit.
- **Livewire ZatcaFields component skipped** — Alpine.js x-show in `_form.blade.php` handles dynamic field toggling for type/is_tax_exempt without adding a Livewire dependency.
- **Pest test for scope skipped** — verified manually via tinker (Admin sees 2, Account Manager sees 1, policy denies cross-AM access). Per the explicit instruction to skip complex Pest tests, this remains as a TODO for Phase 8 QA.

## Phase 8 Notes (2026-04-25)

- **Audit log events** wired for `invoice.issued`, `quote.approved`, `quote.rejected` only. `payment.recorded` and `payment.refunded` already write `AuditLog` rows inline inside `RecordPayment`/`RefundPayment` (Phase 6). Routing payments through the new listener would create duplicate rows, and the constraint says "do not modify Phase 1-7 work" — so events for payments are intentionally NOT dispatched. The audit-log index page renders inline + listener-written rows uniformly.
- **No EventServiceProvider** added. Laravel 11 doesn't ship one by default; using `Event::listen(...)` calls in `AppServiceProvider::boot()` instead, which is simpler and avoids touching `bootstrap/providers.php`.
- **ZATCA local skip**: `IssueInvoice` requires a ZATCA private key + base64 cert which are not present locally (they live on the prod server). The action's existing internal `try/catch` (lines ~113-119) swallows the `RuntimeException('ZATCA signing credentials are not configured')` and flips the invoice to `zatca_status='failed'` while still moving `status='issued'`. Smoke test asserts `status='issued'` and accepts any `zatca_status`. Production server has real cert and produces a signed XML.
- **Smoke test 13/13 pass** locally (SQLite). Output: `php8.2 tests/Smoke/full_flow_test.php` — all steps pass, audit_logs row counts verified (1× invoice.issued, 2× payment.recorded, 1× quote.approved).
