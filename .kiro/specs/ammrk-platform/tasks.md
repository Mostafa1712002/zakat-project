# Tasks: AMMRK Platform

## Phase 1: Cleanup (Delete Old Modules)

### Task 1.1: Remove Inventory Domain
- [ ] Delete models: `Product`, `ProductPrice`, `InventoryLevel`, `StockMovement`, `Warehouse`, `Grade`, `Unit` (will recreate)
- [ ] Delete controllers: `ProductController`, `WarehouseController`, `InventoryController`, `StockMovementController`, `GradeController`
- [ ] Delete migrations referencing inventory tables
- [ ] Delete views under `resources/views/admin/products/`, `warehouses/`, `inventory/`
- [ ] Remove routes under `web.php` for inventory paths

**Outcome:** ✅ Inventory domain entirely removed
**Dependencies:** None

### Task 1.2: Remove Suppliers Domain
- [ ] Delete `Supplier` model + migration + factory
- [ ] Delete `SupplierController` + views
- [ ] Delete supplier routes
- [ ] Search & remove all `supplier_id` foreign key references

**Outcome:** ✅ Supplier domain entirely removed
**Dependencies:** None

### Task 1.3: Remove Sales Reps Domain
- [ ] Delete models: `SalesRep`, `SalesRepInventory`, `SalesRepStockMovement`, `SalesRepTransaction`, `CommissionWithdrawal`
- [ ] Delete `SalesRepController` + related controllers
- [ ] Delete sales rep views and routes
- [ ] Delete `SalesRepAccessMiddleware` and remove from kernel
- [ ] Delete sales rep migrations

**Outcome:** ✅ Sales Rep domain entirely removed
**Dependencies:** None

### Task 1.4: Remove Returns Domain
- [ ] Delete `SaleReturn`, `SaleReturnItem`, `PurchaseReturn`, `PurchaseReturnItem`
- [ ] Delete return controllers and views
- [ ] Delete return migrations

**Outcome:** ✅ Returns domain removed
**Dependencies:** 1.1, 1.3

### Task 1.5: Remove Old Sales/Purchases Domain
- [ ] Delete `Sale`, `SaleItem`, `Purchase`, `PurchaseItem` (will be replaced by Quote/Invoice)
- [ ] Delete old sales/purchase controllers
- [ ] Delete old sales/purchase views and routes
- [ ] Delete old migrations

**Outcome:** ✅ Old sales/purchase tables removed (replaced by new Quote/Invoice)
**Dependencies:** 1.4

---

## Phase 2: Foundation (Auth, Roles, Settings)

### Task 2.1: Database & Spatie Setup
- [ ] Create new DB `ammrk_v2`
- [ ] Update `.env` to point to `ammrk_v2`
- [ ] Run Spatie permission migrations (`php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`)
- [ ] Create `users` migration (drop `branch_id` if conflicts, ensure clean schema)

**Outcome:** ✅ Empty new DB with auth + permission tables
**Dependencies:** Phase 1 complete

### Task 2.2: Settings Module
- [ ] Create `settings` migration: id, key (unique), value, type, group, is_public
- [ ] Create `Setting` model with helpers: `Setting::get('key', $default)`, `Setting::put('key', $value, $type)`
- [ ] Create `SettingsSeeder` with all required settings (default_tax_rate=15, etc.)
- [ ] Create admin UI: `/admin/settings/general`, `/admin/settings/zatca`

**Outcome:** ✅ Settings system functional with VAT 15% default
**Dependencies:** 2.1

### Task 2.3: Roles & Permissions Seeder
- [ ] Create `RolePermissionSeeder`:
  - Roles: Super Admin, Admin, Account Manager, Accountant
  - Permissions (granular): customers.* | services.* | service-types.* | quotes.* | invoices.* | payments.* | reports.* | settings.* | users.*
  - Assign default permissions per role
- [ ] Run seeder and verify
- [ ] Create dynamic permission UI at `/admin/roles` (toggle permissions per role)
- [ ] Lock 'Super Admin' role from edit

**Outcome:** ✅ Dynamic role/permission management
**Dependencies:** 2.1

### Task 2.4: Default Users Seeder
- [ ] Create `DefaultUsersSeeder` with: super-admin@ammrk.com, admin@ammrk.com (password = Ammrk@2026)
- [ ] Run seeder

**Outcome:** ✅ Login works for super admin and admin
**Dependencies:** 2.3

---

## Phase 3: Catalog (Service Types & Services)

### Task 3.1: Migrations
- [ ] `service_types` migration
- [ ] `units` migration (recreated)
- [ ] `services` migration (no price field)

**Outcome:** ✅ Catalog tables exist
**Dependencies:** Phase 2 complete

### Task 3.2: Models & Seeders
- [ ] `app/Domain/Catalog/Models/{ServiceType,Service,Unit}.php`
- [ ] Relationships, fillable, casts
- [ ] `ServiceTypeSeeder` with 6 default types: Exhibitions, Conferences, Events, Tech Solutions, Hospitality, Media
- [ ] `UnitSeeder` with: يوم, فعالية, باقة, شهر, ساعة

**Outcome:** ✅ Catalog populated with default data
**Dependencies:** 3.1

### Task 3.3: Controllers & Routes
- [ ] `Admin/ServiceTypeController` (resource: index/create/store/edit/update/destroy)
- [ ] `Admin/ServiceController` (resource)
- [ ] `Admin/UnitController` (resource)
- [ ] Routes registered in `web.php` under admin middleware + permission middleware

**Outcome:** ✅ CRUD endpoints functional
**Dependencies:** 3.2

### Task 3.4: Views & UI
- [ ] `service-types/index` (table with toggle active, sort_order drag)
- [ ] `service-types/{create,edit}` (icon picker, name)
- [ ] `services/index` (table grouped by service type)
- [ ] `services/{create,edit}` (service type select, name, unit, description, classification)
- [ ] Apply Spatie `@can` directives

**Outcome:** ✅ Catalog manageable via admin UI
**Dependencies:** 3.3

### Task 3.5: Policies
- [ ] `ServicePolicy`, `ServiceTypePolicy`, `UnitPolicy` mapped to permissions

**Outcome:** ✅ Permission-protected catalog
**Dependencies:** 3.3

---

## Phase 4: Customer (with ZATCA fields)

### Task 4.1: Migration & Model
- [ ] `customers` migration (REGA address fields, vat_number, account_manager_id, is_tax_exempt)
- [ ] `customer_contacts` migration
- [ ] `Customer`, `CustomerContact` models with relationships
- [ ] Global query scope `AccountManagerScope` (filter by account_manager_id when role = Account Manager)

**Outcome:** ✅ Customer model with ZATCA support
**Dependencies:** Phase 2 complete

### Task 4.2: Validators
- [ ] `ZatcaCustomerValidator` (VAT regex /^3\d{13}3$/, REGA address completeness)
- [ ] `CustomerStoreRequest`, `CustomerUpdateRequest` (form requests with conditional VAT validation)

**Outcome:** ✅ Validation enforces ZATCA rules
**Dependencies:** 4.1

### Task 4.3: Controllers & Views
- [ ] `Admin/CustomerController` (resource + show)
- [ ] Views: `index` (filtered by manager scope), `show` (tabs: details, contacts, quotes, invoices, payments)
- [ ] `create/edit` views with REGA fields, type selector, manager assignment
- [ ] Livewire `Customer/ZatcaFields` component for dynamic validation

**Outcome:** ✅ Customers manageable with ZATCA data
**Dependencies:** 4.2

### Task 4.4: Policies & Scope Tests
- [ ] `CustomerPolicy` (Account Manager can only access own customers)
- [ ] Pest test: Account Manager cannot view another's customer (403)

**Outcome:** ✅ Customer scope tested
**Dependencies:** 4.3

---

## Phase 5: Sales (Quote → Invoice)

### Task 5.1: Migrations
- [ ] `quotes`, `quote_items` migrations
- [ ] `invoices`, `invoice_items` migrations (with all ZATCA fields)

**Outcome:** ✅ Sales tables exist
**Dependencies:** Phase 3, Phase 4 complete

### Task 5.2: Models & Calculators
- [ ] `Quote`, `QuoteItem`, `Invoice`, `InvoiceItem` models
- [ ] `QuoteCalculator` service: applies default_tax_rate from settings, handles is_tax_exempt
- [ ] `InvoiceCalculator` service: same logic + immutable once issued
- [ ] Numbering: `QuoteNumberGenerator`, `InvoiceNumberGenerator` (Q-2026-0001 format)

**Outcome:** ✅ Models with auto VAT calculation
**Dependencies:** 5.1

### Task 5.3: Quote Workflow Actions
- [ ] `CreateQuote` action
- [ ] `SubmitQuote` action (draft → submitted)
- [ ] `ApproveQuote` action (submitted → approved)
- [ ] `RejectQuote` action (requires reason)
- [ ] `ConvertQuoteToInvoice` action (approved → converted, creates invoice)

**Outcome:** ✅ Quote workflow operational
**Dependencies:** 5.2

### Task 5.4: Quote UI
- [ ] `Admin/QuoteController` + Livewire form
- [ ] `quotes/index` (status filter, columns: number, customer, event, total, status)
- [ ] `quotes/show` with workflow buttons (submit/approve/reject/convert)
- [ ] `quotes/create` Livewire form: customer select → event details → line items (service, qty, price)

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
| 1. Cleanup | 5 | 0 | Not Started |
| 2. Foundation | 4 | 0 | Not Started |
| 3. Catalog | 5 | 0 | Not Started |
| 4. Customer | 4 | 0 | Not Started |
| 5. Sales | 8 | 0 | Not Started |
| 6. Treasury | 3 | 0 | Not Started |
| 7. Reports | 3 | 0 | Not Started |
| 8. Polish | 3 | 0 | Not Started |
| **Total** | **35** | **0** | **0%** |
