# Tasks: AMMRK Platform

## Phase 1: Cleanup (Delete Old Modules)

### Task 1.1: Remove Inventory Domain
- [x] Delete models: `Product`, `ProductPrice`, `InventoryLevel`, `StockMovement`, `Warehouse`, `Grade`, `Category` (Unit kept — will be replaced in Phase 3)
- [x] Delete controllers: `ProductController`, `WarehouseController`, `CategoryController`
- [x] Delete migrations referencing inventory tables
- [x] Delete views under `resources/views/products/`, `warehouses/`, `categories/`
- [x] Remove routes under `web.php` for inventory paths

**Outcome:** ✅ Inventory domain entirely removed (Unit retained per spec divergence — see cleanup-log.md)
**Dependencies:** None

### Task 1.2: Remove Suppliers Domain
- [x] Delete `Supplier` model + migration
- [x] Delete `SupplierController` + views
- [x] Delete supplier routes
- [x] Search & remove all `supplier_id` foreign key references (handled via migration deletion)

**Outcome:** ✅ Supplier domain entirely removed
**Dependencies:** None

### Task 1.3: Remove Sales Reps Domain
- [x] Delete models: `SalesRep`, `SalesRepInventory`, `SalesRepStockMovement`, `SalesRepTransaction`, `CommissionWithdrawal`
- [x] Delete `SalesRepController`, `SalesRepAccountController`, `SalesRepDashboardController`
- [x] Delete sales rep views and routes
- [x] Delete `SalesRepAccessMiddleware` and remove from `bootstrap/app.php` (Laravel 11 — no Kernel.php)
- [x] Delete sales rep migrations
- [x] Delete `App\Traits\SalesRepScope` (used by Customer, Payment)

**Outcome:** ✅ Sales Rep domain entirely removed
**Dependencies:** None

### Task 1.4: Remove Returns Domain
- [x] Delete `SaleReturn`, `SaleReturnItem`, `PurchaseReturn`, `PurchaseReturnItem`
- [x] Delete return controllers and views
- [x] Delete return migrations

**Outcome:** ✅ Returns domain removed
**Dependencies:** 1.1, 1.3

### Task 1.5: Remove Old Sales/Purchases Domain
- [x] Delete `Sale`, `SaleItem`, `Purchase`, `PurchaseItem` (will be replaced by Quote/Invoice in Phase 5)
- [x] Delete old sales/purchase controllers (`SaleController`, `PurchaseController`, `QuotationController`, `PurchaseQuotationController`)
- [x] Delete old sales/purchase views and routes
- [x] Delete old migrations
- [x] Stub surviving controllers (Dashboard, Report, Treasury, Zatca, Payment, Customer) — depended on Sale/Purchase
- [x] Strip surviving model relations (User.sales/salesRep, Customer.sales/salesRep, Payment.sale/purchase/salesRep, Branch.warehouses, Employee.salesRep, Expense.salesRep)
- [x] Clean sidebar navigation in `resources/views/layouts/app.blade.php`

**Outcome:** ✅ Old sales/purchase tables removed (replaced by new Quote/Invoice in Phase 5)
**Dependencies:** 1.4

---

## Phase 2: Foundation (Auth, Roles, Settings)

### Task 2.1: Database & Spatie Setup
- [x] Switched to SQLite for local dev (`DB_DATABASE=/home/mostafa/www/zakat-project/database/database.sqlite`)
- [x] Spatie Permission v6.24 already installed; published migration retained
- [x] Confirmed `users` schema matches design (id, name, email, password, branch_id, phone, is_active) via two existing migrations
- [x] Deleted stale `2026_01_29_122411_add_employee_role_and_permissions` migration (Phase 1 follow-up)
- [x] `php artisan migrate:fresh` runs cleanly

**Outcome:** ✅ SQLite DB, permissions tables, fresh schema ready
**Dependencies:** Phase 1 complete

### Task 2.2: Settings Module
- [x] Updated `settings` migration: id, key (unique), value (text), type (enum), group, is_public
- [x] `Setting` model with cached `Setting::get()` / `Setting::put()` (1-hour TTL per key)
- [x] `SettingsSeeder` with 7 defaults: default_tax_rate=15, prefixes (INV-/Q-/PAY-), invoice_due_days=30, quote_validity_days=14, zatca_environment=production
- [x] Admin UI: `/admin/settings/general` and `/admin/settings/zatca` (Blade + can:settings.system gate)

**Outcome:** ✅ Settings system functional with VAT 15% default
**Dependencies:** 2.1

### Task 2.3: Roles & Permissions Seeder
- [x] `RolePermissionSeeder`: 4 roles, 46 granular permissions
  - Super Admin (locked, all 46), Admin (44 — minus settings.system + roles.manage-permissions)
  - Account Manager (10 sales-side scoped permissions)
  - Accountant (13 invoicing/treasury/reports permissions)
- [x] Added `is_locked` column on roles table (separate migration)
- [x] Spatie middleware aliases registered (`role`, `permission`, `role_or_permission`) in `bootstrap/app.php`
- [x] Admin UI at `/admin/roles` (index) and `/admin/roles/{role}/edit` (checkbox grid grouped by domain)
- [x] Server-side guard: 403 if attempting to edit Super Admin or any locked role; cache forgotten on save

**Outcome:** ✅ Dynamic role/permission management
**Dependencies:** 2.1

### Task 2.4: Default Users Seeder
- [x] `DefaultUsersSeeder` creates super-admin@ammrk.com and admin@ammrk.com (password = Ammrk@2026)
- [x] `DatabaseSeeder` calls SettingsSeeder → RolePermissionSeeder → DefaultUsersSeeder (in order)
- [x] Branch + expense category/method seeds preserved for surviving Phase 1 domains
- [x] `php artisan db:seed` runs cleanly

**Outcome:** ✅ Login works for super admin and admin
**Dependencies:** 2.3

---

## Phase 3: Catalog (Service Types & Services)

### Task 3.1: Migrations
- [x] `service_types` migration (`2026_04_25_130000`)
- [x] `units` migration recreated (`2026_04_25_130001`) — old Phase 1 migration deleted
- [x] `services` migration (`2026_04_25_130002`) — no price field, soft deletes, FK restrict

**Outcome:** ✅ Catalog tables exist
**Dependencies:** Phase 2 complete

### Task 3.2: Models & Seeders
- [x] `app/Domain/Catalog/Models/{ServiceType,Service,Unit}.php` (old `app/Models/Unit.php` removed)
- [x] Relationships, fillable, casts (`ServiceType hasMany Service`; `Service belongsTo ServiceType + Unit`)
- [x] `ServiceTypeSeeder` with 6 default types (Arabic): المعارض, المؤتمرات, الفعاليات, الحلول التقنية, الضيافة, الإعلام
- [x] `UnitSeeder` recreated with: يوم, فعالية, باقة, شهر, ساعة (replaces 12 weight/length/volume units from Phase 1)
- [x] Wired both into `DatabaseSeeder` (after `DefaultUsersSeeder`)

**Outcome:** ✅ Catalog populated with default data
**Dependencies:** 3.1

### Task 3.3: Controllers & Routes
- [x] `Admin/ServiceTypeController` (resource: index/create/store/edit/update/destroy, `show` excluded)
- [x] `Admin/ServiceController` (resource)
- [x] `Admin/UnitController` (resource)
- [x] Added `units.{view,create,edit,delete}` permissions to `RolePermissionSeeder` (Super Admin/Admin auto-grant via existing logic)
- [x] Routes registered under `admin/` prefix with Spatie `permission:*.view` middleware on each resource group (18 routes total)

**Outcome:** ✅ CRUD endpoints functional
**Dependencies:** 3.2

### Task 3.4: Views & UI
- [x] `service-types/{index,create,edit}.blade.php` — table with search + active filter, plain text icon input
- [x] `services/{index,create,edit}.blade.php` — search + type filter + active filter, ZATCA classification select (S/Z/E)
- [x] `units/{index,create,edit}.blade.php` — basic table + filter
- [x] Spatie `@can` directives gate edit/delete/create buttons (mapped to policies)
- [x] Verified end-to-end via curl (login → POST `services/store` → service persisted)

**Outcome:** ✅ Catalog manageable via admin UI
**Dependencies:** 3.3

### Task 3.5: Policies
- [x] `ServiceTypePolicy`, `ServicePolicy`, `UnitPolicy` under `app/Domain/Catalog/Policies/`
- [x] Methods (`viewAny`, `view`, `create`, `update`, `delete`) mapped to Spatie permissions
- [x] Registered via `Gate::policy()` in `AppServiceProvider::boot()`

**Outcome:** ✅ Permission-protected catalog
**Dependencies:** 3.3

---

## Phase 4: Customer (with ZATCA fields)

### Task 4.1: Migration & Model
- [x] `customers` migration (REGA address fields, vat_number, account_manager_id, is_tax_exempt)
- [x] `customer_contacts` migration
- [x] `Customer`, `CustomerContact` models with relationships
- [x] Global query scope `AccountManagerScope` (filter by account_manager_id when role = Account Manager; bypassed for Admin/Super Admin/Accountant)

**Outcome:** ✅ Customer model with ZATCA support
**Dependencies:** Phase 2 complete

### Task 4.2: Validators
- [x] `ZatcaCustomerValidator::isReadyForInvoicing()` (VAT regex `/^3\d{13}3$/`, REGA address completeness)
- [x] `CustomerStoreRequest`, `CustomerUpdateRequest` (form requests with conditional VAT validation via `Rule::requiredIf`)

**Outcome:** ✅ Validation enforces ZATCA rules
**Dependencies:** 4.1

### Task 4.3: Controllers & Views
- [x] `Admin/CustomerController` (resource: index, create, store, show, edit, update, destroy)
- [x] Views: `index` (filtered by manager scope), `show` (Alpine tabs: details, contacts, invoices, payments)
- [x] `create/edit` views with REGA fields, type selector, manager assignment, Alpine.js dynamic show/hide on type+is_tax_exempt
- [x] Manager dropdown sourced from `User::role('Account Manager')`; force `account_manager_id = auth()->id()` for AMs without elevated roles

**Outcome:** ✅ Customers manageable with ZATCA data
**Dependencies:** 4.2

### Task 4.4: Policies & Scope Tests
- [x] `CustomerPolicy` mapping to Spatie permissions; Account Manager limited to own records via view-own + ownership check
- [x] Registered via `Gate::policy()` in `AppServiceProvider::boot()`
- [x] Verified end-to-end via tinker (admin sees 2, account manager sees 1, policy denies cross-AM access)

**Outcome:** ✅ Customer scope verified
**Dependencies:** 4.3

---

## Phase 5: Sales (Quote → Invoice)

### Task 5.1: Migrations
- [x] `quotes`, `quote_items` migrations (`2026_04_25_150000`, `2026_04_25_150001`) — `tax_rate DEFAULT 15` at DB level
- [ ] `invoices`, `invoice_items` migrations (with all ZATCA fields) — Phase 5b

**Outcome:** ✅ Quote tables exist (Invoice tables deferred to Phase 5b)
**Dependencies:** Phase 3, Phase 4 complete

### Task 5.2: Models & Calculators
- [x] `Quote`, `QuoteItem` models under `app/Domain/Sales/Models/` (Invoice models = Phase 5b)
- [x] `QuoteCalculator` service: applies `default_tax_rate` from settings, zeroes rate when `customer.is_tax_exempt`
- [ ] `InvoiceCalculator` service — Phase 5b
- [x] `QuoteNumberGenerator` (Q-2026-0001 format, year-scoped sequence). `InvoiceNumberGenerator` = Phase 5b

**Outcome:** ✅ Quote models with auto VAT calculation (item `saving` hook + calculator aggregation)
**Dependencies:** 5.1

### Task 5.3: Quote Workflow Actions
- [x] `CreateQuote` action (DB transaction: header → items → recalculate)
- [x] `SubmitQuote` action (draft → submitted, throws if not draft)
- [x] `ApproveQuote` action (submitted → approved, sets `approved_by`/`approved_at`)
- [x] `RejectQuote` action (requires reason, submitted → rejected)
- [x] `ConvertQuoteToInvoice` — stub class throwing `LogicException` (Phase 5b implements it)

**Outcome:** ✅ Quote workflow operational (conversion to invoice = Phase 5b)
**Dependencies:** 5.2

### Task 5.4: Quote UI
- [x] `Admin/QuoteController` (resource + submit/approve/reject endpoints)
- [x] `QuotePolicy` registered in `AppServiceProvider` (viewAny/view/create/update/delete + submit/approve/reject)
- [x] Routes block under `admin/quotes` gated by `quotes.view-all|quotes.view-own` (10 routes)
- [x] `quotes/index` Blade — status + customer filters, search by number/event, status badges
- [x] `quotes/show` — read-only details + workflow buttons gated by `@can`
- [x] `quotes/create` + `quotes/edit` — Alpine.js dynamic line items with live totals, default tax_rate=15
- [x] Smoke test via tinker: Q-2026-0001 created, 2×100 → 200 subtotal / 30 tax / 230 grand, draft→submitted→approved

**Outcome:** ✅ Quote creation/management UI
**Dependencies:** 5.3

### Task 5.5: Invoice Issuance
- [ ] `IssueInvoice` action: validates customer ZATCA data, generates UUID/ICV/PIH/hash/QR/signed XML
- [ ] Wire up existing ZATCA services (`Domain/Zatca/Services/*`) in `IssueInvoice`
- [ ] Persist `signed_xml`, `qr_code` to invoice
- [ ] Queue `SubmitInvoiceToZatca` job

**Outcome:** ✅ Invoices generate ZATCA-ready artifacts
**Dependencies:** 5.4

### Task 5.6: ZATCA Submission Job
- [ ] `SubmitInvoiceToZatca` queued job: calls ZATCA API, updates `zatca_status`, persists warnings
- [ ] Retry logic: 3 attempts with backoff
- [ ] `RetryZatcaFailedSubmissions` artisan command (manual trigger)

**Outcome:** ✅ Async ZATCA submission
**Dependencies:** 5.5

### Task 5.7: Invoice UI
- [ ] `Admin/InvoiceController` + Livewire form
- [ ] `invoices/index` (status filter, ZATCA status filter)
- [ ] `invoices/show` (header, items, totals, ZATCA panel with status/QR/warnings)
- [ ] `invoices/{id}/pdf` (Browsershot PDF with QR code)
- [ ] Cannot edit issued invoices (UI + policy enforcement)

**Outcome:** ✅ Invoice issuance and viewing
**Dependencies:** 5.6

### Task 5.8: Tests
- [ ] Pest tests: VAT 15% default applied, tax-exempt customer = 0%, quote workflow transitions, invoice immutability after issued

**Outcome:** ✅ Sales logic tested
**Dependencies:** 5.7

---

## Phase 6: Treasury (Payments)

### Task 6.1: Migrations & Models
- [ ] `treasuries`, `payments` migrations
- [ ] `Treasury`, `Payment` models

**Outcome:** ✅ Treasury tables exist
**Dependencies:** Phase 5 complete

### Task 6.2: RecordPayment Action
- [ ] Validates: amount <= remaining balance
- [ ] Updates invoice `paid_amount` and `status` (paid_partial/paid)
- [ ] Updates treasury balance
- [ ] Audit log entry

**Outcome:** ✅ Payment recording with side effects
**Dependencies:** 6.1

### Task 6.3: UI
- [ ] `Admin/PaymentController` + form
- [ ] `payments/index` (filter by date range, customer, invoice)
- [ ] `payments/create` (invoice select, amount, method, treasury, date)
- [ ] Receipt PDF download

**Outcome:** ✅ Payment management UI
**Dependencies:** 6.2

---

## Phase 7: Reports & Dashboard

### Task 7.1: Dashboard
- [ ] KPIs: monthly revenue, outstanding debt, paid this month, upcoming events
- [ ] Charts: revenue trend (12 months), invoice status breakdown
- [ ] Recent activity feed (last 10 invoices/payments)

**Outcome:** ✅ Operational dashboard
**Dependencies:** Phase 6 complete

### Task 7.2: Financial Reports
- [ ] Revenue report (date range, by service type, by customer)
- [ ] VAT report (for ZATCA filing — collected vs cleared)
- [ ] Customer debt aging report (0-30, 31-60, 61-90, 90+)
- [ ] Export to Excel + PDF

**Outcome:** ✅ Financial reporting suite
**Dependencies:** 7.1

### Task 7.3: Operational Reports
- [ ] Quotes pending approval
- [ ] Failed ZATCA submissions
- [ ] Active events calendar (timeline view)

**Outcome:** ✅ Operational reporting
**Dependencies:** 7.1

---

## Phase 8: Polish & Deployment

### Task 8.1: Audit Log Wiring
- [ ] Listeners on Invoice issued, Payment recorded, Quote approved/rejected
- [ ] `audit_logs` index page

**Outcome:** ✅ Mutations audit trail
**Dependencies:** Phase 7 complete

### Task 8.2: Testing & QA
- [ ] Run Pest suite: target >70% coverage on Domain layer
- [ ] Manual UAT: full happy-path quote → invoice → payment → ZATCA cleared
- [ ] Test all role gates (4 roles × 5 modules)

**Outcome:** ✅ Production-ready
**Dependencies:** 8.1

### Task 8.3: Deployment
- [ ] Deploy to `/var/www/ammrk.newaves-systems.com` (already setup)
- [ ] Switch `.env` to `ammrk_v2` DB
- [ ] Run migrations + seeders on production
- [ ] Smoke test: login, create customer, quote, invoice, ZATCA submission
- [ ] Update deploy.sh to track `ammrk` branch in zakat-project repo

**Outcome:** ✅ Live on ammrk.newaves-systems.com
**Dependencies:** 8.2

---

## Progress Tracking

| Phase | Tasks | Completed | Status |
|-------|-------|-----------|--------|
| 1. Cleanup | 5 | 5 | ✅ Complete |
| 2. Foundation | 4 | 4 | ✅ Complete |
| 3. Catalog | 5 | 5 | ✅ Complete |
| 4. Customer | 4 | 4 | ✅ Complete |
| 5. Sales | 8 | 4 | 🔄 Phase 5a Complete (Quote workflow) |
| 6. Treasury | 3 | 0 | Not Started |
| 7. Reports | 3 | 0 | Not Started |
| 8. Polish | 3 | 0 | Not Started |
| **Total** | **35** | **22** | **63%** |
