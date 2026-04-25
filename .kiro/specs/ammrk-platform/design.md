# Design: AMMRK Platform

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│  AMMRK Platform (Laravel 11 + Livewire)                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Catalog Context        Customer Context       Identity Context │
│  ──────────────         ────────────────       ──────────────── │
│  ServiceType            Customer + ZATCA       User + Spatie    │
│  Service                ContactPerson          Role+Permission  │
│  Unit                   account_manager_id                      │
│         │                       │                       │       │
│         └──────────┬────────────┴──────────┬────────────┘       │
│                    ▼                       ▼                    │
│              Sales Context (Quote → Approval → Invoice)         │
│                              │                                  │
│              ┌───────────────┼───────────────┐                  │
│              ▼               ▼               ▼                  │
│     ZATCA Context     Treasury Context   Reports Context        │
│     (existing)        Payment+Receipt    Dashboard+Exports      │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

## Database Schema

### Identity & Access (Spatie Permission)

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    branch_id BIGINT UNSIGNED NULL,
    phone VARCHAR(20) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP, updated_at TIMESTAMP,
    INDEX idx_users_branch (branch_id),
    INDEX idx_users_active (is_active)
);

-- Spatie tables: roles, permissions, model_has_roles, model_has_permissions, role_has_permissions
```

### Catalog

```sql
CREATE TABLE service_types (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    icon VARCHAR(50) NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP, updated_at TIMESTAMP,
    INDEX idx_service_types_active (is_active, sort_order)
);

CREATE TABLE units (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP, updated_at TIMESTAMP
);

CREATE TABLE services (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    service_type_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    unit_id BIGINT UNSIGNED NOT NULL,
    description TEXT NULL,
    default_zatca_classification VARCHAR(20) DEFAULT 'S',  -- S=Standard, Z=Zero, E=Exempt
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP, updated_at TIMESTAMP, deleted_at TIMESTAMP NULL,
    FOREIGN KEY (service_type_id) REFERENCES service_types(id) ON DELETE RESTRICT,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE RESTRICT,
    INDEX idx_services_type (service_type_id, is_active)
);
```

### Customers

```sql
CREATE TABLE customers (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    type ENUM('company','government','individual') NOT NULL DEFAULT 'company',
    vat_number VARCHAR(15) NULL,
    cr_number VARCHAR(20) NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(255) NULL,
    is_tax_exempt BOOLEAN DEFAULT FALSE,
    account_manager_id BIGINT UNSIGNED NULL,
    branch_id BIGINT UNSIGNED NULL,

    -- ZATCA Address (REGA)
    street_name VARCHAR(127) NULL,
    building_number VARCHAR(4) NULL,
    secondary_number VARCHAR(4) NULL,
    district VARCHAR(127) NULL,
    city VARCHAR(127) NULL,
    postal_code VARCHAR(5) NULL,
    country_code CHAR(2) DEFAULT 'SA',

    notes TEXT NULL,
    created_at TIMESTAMP, updated_at TIMESTAMP, deleted_at TIMESTAMP NULL,
    FOREIGN KEY (account_manager_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    INDEX idx_customers_manager (account_manager_id),
    INDEX idx_customers_vat (vat_number),
    INDEX idx_customers_branch (branch_id)
);

CREATE TABLE customer_contacts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    customer_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    position VARCHAR(100) NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(255) NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP, updated_at TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_contacts_customer (customer_id)
);
```

### Sales (Quote → Invoice)

```sql
CREATE TABLE quotes (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    quote_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,

    event_name VARCHAR(255) NOT NULL,
    event_start_date DATE NULL,
    event_end_date DATE NULL,
    event_location VARCHAR(255) NULL,
    event_type VARCHAR(100) NULL,

    subtotal DECIMAL(15,2) DEFAULT 0,
    discount_total DECIMAL(15,2) DEFAULT 0,
    tax_total DECIMAL(15,2) DEFAULT 0,
    grand_total DECIMAL(15,2) DEFAULT 0,

    status ENUM('draft','submitted','approved','rejected','converted') DEFAULT 'draft',
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT NULL,
    valid_until DATE NULL,
    notes TEXT NULL,

    created_at TIMESTAMP, updated_at TIMESTAMP, deleted_at TIMESTAMP NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_quotes_customer (customer_id),
    INDEX idx_quotes_status (status),
    INDEX idx_quotes_creator (created_by)
);

CREATE TABLE quote_items (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    quote_id BIGINT UNSIGNED NOT NULL,
    service_id BIGINT UNSIGNED NOT NULL,
    description TEXT NULL,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
    unit_price DECIMAL(15,2) NOT NULL,
    discount_amount DECIMAL(15,2) DEFAULT 0,
    tax_rate DECIMAL(5,2) DEFAULT 15,
    tax_amount DECIMAL(15,2) DEFAULT 0,
    total DECIMAL(15,2) DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP, updated_at TIMESTAMP,
    FOREIGN KEY (quote_id) REFERENCES quotes(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT,
    INDEX idx_quote_items_quote (quote_id)
);

CREATE TABLE invoices (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    quote_id BIGINT UNSIGNED NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,

    event_name VARCHAR(255) NOT NULL,
    event_start_date DATE NULL,
    event_end_date DATE NULL,
    event_location VARCHAR(255) NULL,
    event_type VARCHAR(100) NULL,

    subtotal DECIMAL(15,2) DEFAULT 0,
    discount_total DECIMAL(15,2) DEFAULT 0,
    tax_total DECIMAL(15,2) DEFAULT 0,
    grand_total DECIMAL(15,2) DEFAULT 0,
    paid_amount DECIMAL(15,2) DEFAULT 0,

    status ENUM('draft','issued','paid_partial','paid','cancelled') DEFAULT 'draft',

    -- ZATCA fields
    uuid CHAR(36) NULL,
    icv BIGINT UNSIGNED NULL,
    pih VARCHAR(255) NULL,
    invoice_hash VARCHAR(255) NULL,
    qr_code TEXT NULL,
    signed_xml LONGTEXT NULL,
    zatca_status ENUM('pending','cleared','reported','failed') DEFAULT 'pending',
    zatca_uuid VARCHAR(100) NULL,
    zatca_warnings JSON NULL,
    zatca_submitted_at TIMESTAMP NULL,

    issued_at TIMESTAMP NULL,
    due_date DATE NULL,
    notes TEXT NULL,

    created_at TIMESTAMP, updated_at TIMESTAMP, deleted_at TIMESTAMP NULL,
    FOREIGN KEY (quote_id) REFERENCES quotes(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_invoices_customer (customer_id),
    INDEX idx_invoices_status (status),
    INDEX idx_invoices_zatca (zatca_status),
    INDEX idx_invoices_uuid (uuid)
);

CREATE TABLE invoice_items (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    invoice_id BIGINT UNSIGNED NOT NULL,
    service_id BIGINT UNSIGNED NOT NULL,
    description TEXT NULL,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
    unit_price DECIMAL(15,2) NOT NULL,
    discount_amount DECIMAL(15,2) DEFAULT 0,
    tax_rate DECIMAL(5,2) DEFAULT 15,
    tax_amount DECIMAL(15,2) DEFAULT 0,
    total DECIMAL(15,2) DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP, updated_at TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT,
    INDEX idx_invoice_items_invoice (invoice_id)
);
```

### Treasury

```sql
CREATE TABLE treasuries (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    branch_id BIGINT UNSIGNED NULL,
    type ENUM('cash','bank') DEFAULT 'cash',
    balance DECIMAL(15,2) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP, updated_at TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL
);

CREATE TABLE payments (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    payment_number VARCHAR(50) UNIQUE NOT NULL,
    invoice_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    treasury_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    method ENUM('cash','bank','transfer','check') DEFAULT 'cash',
    reference_number VARCHAR(100) NULL,
    payment_date DATE NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP, updated_at TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE RESTRICT,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT,
    FOREIGN KEY (treasury_id) REFERENCES treasuries(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_payments_invoice (invoice_id),
    INDEX idx_payments_customer (customer_id),
    INDEX idx_payments_date (payment_date)
);
```

### Settings

```sql
CREATE TABLE settings (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `key` VARCHAR(100) UNIQUE NOT NULL,
    value TEXT NULL,
    type ENUM('string','int','decimal','bool','json') DEFAULT 'string',
    `group` VARCHAR(50) DEFAULT 'general',
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP, updated_at TIMESTAMP
);

-- Seeded values:
-- default_tax_rate = 15
-- zatca_environment = production
-- invoice_number_prefix = INV-
-- quote_number_prefix = Q-
-- payment_number_prefix = PAY-
-- invoice_due_days = 30
-- quote_validity_days = 14
```

## Folder Structure (Laravel)

```
app/
├── Domain/
│   ├── Catalog/
│   │   ├── Models/{ServiceType,Service,Unit}.php
│   │   ├── Actions/{CreateService,UpdateService}.php
│   │   └── Policies/ServicePolicy.php
│   ├── Customer/
│   │   ├── Models/{Customer,CustomerContact}.php
│   │   ├── Actions/{CreateCustomer,AssignAccountManager}.php
│   │   ├── Validators/ZatcaCustomerValidator.php
│   │   └── Policies/CustomerPolicy.php
│   ├── Sales/
│   │   ├── Models/{Quote,QuoteItem,Invoice,InvoiceItem}.php
│   │   ├── Actions/{CreateQuote,SubmitQuote,ApproveQuote,ConvertQuoteToInvoice,IssueInvoice}.php
│   │   ├── Services/InvoiceCalculator.php
│   │   ├── Services/QuoteCalculator.php
│   │   └── Policies/{QuotePolicy,InvoicePolicy}.php
│   ├── Treasury/
│   │   ├── Models/{Treasury,Payment}.php
│   │   ├── Actions/{RecordPayment,RefundPayment}.php
│   │   └── Policies/PaymentPolicy.php
│   └── Zatca/
│       ├── Services/{ZatcaApiService,ZatcaSigningService,ZatcaXmlService,
│       │              ZatcaQrService,ZatcaHashService,ZatcaComplianceService}.php
│       └── Actions/SubmitInvoiceToZatca.php
├── Http/
│   ├── Controllers/Admin/
│   │   ├── ServiceTypeController.php
│   │   ├── ServiceController.php
│   │   ├── CustomerController.php
│   │   ├── QuoteController.php
│   │   ├── InvoiceController.php
│   │   ├── PaymentController.php
│   │   ├── RolePermissionController.php
│   │   └── SettingController.php
│   ├── Livewire/
│   │   ├── Quote/{Form,LineItems,SubmitButton}.php
│   │   ├── Invoice/{Form,LineItems,ZatcaStatus}.php
│   │   └── Customer/{ZatcaFields}.php
│   └── Middleware/{EnsureAccountManagerScope}.php
├── Console/Commands/{RetryZatcaFailedSubmissions}.php
└── Listeners/{InvoiceIssuedListener (queues ZATCA submission)}.php

database/
├── migrations/2026_*_create_*.php  (fresh migrations only)
└── seeders/{
    RolePermissionSeeder,
    DefaultUsersSeeder,
    SettingsSeeder,
    ServiceTypeSeeder,
    UnitSeeder
}.php

resources/views/admin/
├── service-types/{index,create,edit}.blade.php
├── services/{index,create,edit}.blade.php
├── customers/{index,show,create,edit}.blade.php
├── quotes/{index,show,create,edit}.blade.php
├── invoices/{index,show,create,edit,zatca-status}.blade.php
├── payments/{index,create}.blade.php
├── reports/{financial,operational,customer-debt}.blade.php
└── settings/{general,zatca,roles}.blade.php

routes/web.php  -- rewritten cleanly
```

## Key Sequence Diagrams

### Quote Approval → Invoice → ZATCA

```
Account Manager           Admin               System              ZATCA API
      │                     │                    │                    │
      │ Create Quote        │                    │                    │
      ├────────────────────────────────────────►│                    │
      │ Submit              │                    │                    │
      ├────────────────────────────────────────►│                    │
      │                     │ Notification       │                    │
      │                     │◄───────────────────┤                    │
      │                     │ Approve            │                    │
      │                     ├───────────────────►│                    │
      │                     │ Convert to Invoice │                    │
      │                     ├───────────────────►│                    │
      │                     │                    │ Build UBL XML      │
      │                     │                    │ Sign + Hash + QR   │
      │                     │                    │ Submit             │
      │                     │                    ├───────────────────►│
      │                     │                    │ cleared/warnings   │
      │                     │                    │◄───────────────────┤
      │                     │ Email PDF          │                    │
      │                     │◄───────────────────┤                    │
```

### Permission Edit (Dynamic)

```
Super Admin → /admin/roles → Edit Role → Toggle Permissions → Save
                                                                │
                                                                ▼
                                       Spatie syncPermissions()
                                                                │
                                                                ▼
                                       PermissionRegistrar::forgetCachedPermissions()
                                                                │
                                                                ▼
                                       Affected users get new permissions on next request
```

## Technology Stack

- **Backend**: Laravel 11, PHP 8.2
- **Frontend**: Livewire 3 + Alpine.js + Tailwind 3
- **DB**: MySQL 8 (`ammrk_v2` — fresh, replaces `ammrk_crm`)
- **Auth**: Laravel breeze + Spatie Permission
- **PDF**: Spatie Browsershot (Puppeteer)
- **ZATCA**: phpseclib3 + chillerlan/php-qrcode (already integrated)
- **Queue**: Laravel queue (database driver) for async ZATCA retries
- **Tests**: Pest (feature + unit)

## Migration Strategy

Since the user chose "Fresh Start":
1. New DB `ammrk_v2` created alongside `ammrk_crm`
2. New migrations under `database/migrations_v2/` (or fresh project)
3. Existing ZATCA cert/CSID copied from `ammrk_crm`'s setup (already in `storage/zatca/`)
4. `ammrk_crm` DB renamed to `ammrk_crm_archive` after cutover
5. nginx swaps to point to v2 deployment when ready

## Risk Mitigation

| Risk | Mitigation |
|------|------------|
| Breaking existing ammrk users | Run v2 in parallel folder, swap DNS only after UAT |
| ZATCA cert reuse | Copy `storage/zatca/` directly; CSID is tied to VAT, not DB |
| Account Manager scope leaks | Centralized scope via global query scope on Customer model |
| Dynamic permission cache stale | Spatie cache forget on role/permission save |
| VAT bug regression | Default value at DB level (`tax_rate DECIMAL(5,2) DEFAULT 15`) + InvoiceCalculator unit tests |
