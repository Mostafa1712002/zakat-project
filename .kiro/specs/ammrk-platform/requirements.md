# Requirements: AMMRK Platform

## Overview
Transform the existing product-CRM into a services-CRM tailored for AMMRK (Saudi event management company). The platform manages event lifecycle from quote to ZATCA-cleared invoice to collection.

## User Roles

- **Super Admin**: Full access (immutable)
- **Admin**: Everything except system settings
- **Account Manager**: Scoped to assigned customers, creates quotes for approval
- **Accountant**: Invoices + treasury + financial reports

## User Stories (EARS Notation)

### Catalog

#### US-001: Manage Service Types
**As an** Admin
**I want to** define service types (exhibitions, conferences, etc.)
**So that** services can be categorized hierarchically

- WHEN admin creates a service type THE SYSTEM SHALL persist `name`, `icon`, `sort_order`, `is_active`
- WHEN admin marks a service type inactive THE SYSTEM SHALL hide it from new service creation but preserve existing references

#### US-002: Manage Services (Catalog)
**As an** Admin
**I want to** define services without fixed prices
**So that** prices can be set per invoice based on event size

- WHEN admin creates a service THE SYSTEM SHALL require `service_type_id`, `name`, `default_unit`
- THE SYSTEM SHALL NOT include a price field on services
- WHEN a service is referenced by an invoice item THE SYSTEM SHALL prevent its hard deletion

### Customer

#### US-010: ZATCA-Compliant Customer Records
**As an** Account Manager
**I want to** capture ZATCA-required customer data
**So that** invoices comply with Saudi e-invoicing regulations

- WHEN customer type = 'company' AND `is_tax_exempt` = false THE SYSTEM SHALL require `vat_number` (15 digits, starts and ends with 3)
- WHEN creating an invoice THE SYSTEM SHALL require customer to have valid REGA address (street, building, district, city, postal_code)
- WHILE customer is `is_tax_exempt` = true THE SYSTEM SHALL apply 0% tax to their invoices

#### US-011: Account Manager Scoping
**As an** Account Manager
**I want to** access only customers assigned to me
**So that** I focus on my book of business

- WHILE user has role 'Account Manager' THE SYSTEM SHALL filter customer lists by `account_manager_id = current_user.id`
- WHEN Account Manager creates a customer THE SYSTEM SHALL auto-set `account_manager_id` to themselves

### Quote → Invoice Workflow

#### US-020: Create Quote
**As an** Account Manager
**I want to** create quotes for events
**So that** customers receive priced proposals before invoicing

- WHEN creating a quote THE SYSTEM SHALL capture event_name, dates, location, type
- WHEN adding a quote item THE SYSTEM SHALL require manual unit_price entry (no auto-pricing)
- THE SYSTEM SHALL apply default_tax_rate (15%) to each item automatically
- WHEN customer is tax_exempt THE SYSTEM SHALL set tax_rate to 0 on items

#### US-021: Quote Approval Flow
**As an** Admin
**I want to** approve quotes before they become invoices
**So that** pricing is reviewed before commitment

- WHEN Account Manager submits a quote THE SYSTEM SHALL change status from 'draft' to 'submitted'
- WHEN Admin approves a quote THE SYSTEM SHALL change status to 'approved' and unlock conversion to invoice
- WHEN Admin rejects a quote THE SYSTEM SHALL require a `rejection_reason`
- WHILE quote status is 'submitted' THE SYSTEM SHALL prevent edits except by Admin

#### US-022: Convert Quote to Invoice
**As an** Admin
**I want to** convert approved quotes into invoices
**So that** ZATCA submission can begin

- WHEN converting an approved quote THE SYSTEM SHALL copy all line items to a new invoice
- THE SYSTEM SHALL set the invoice's `quote_id` reference
- THE SYSTEM SHALL change quote status to 'converted'

### Invoicing

#### US-030: Auto VAT 15%
**As a** user
**I want** VAT 15% to apply automatically
**So that** I never forget to charge tax (the historical bug)

- WHEN any invoice item is created THE SYSTEM SHALL set `tax_rate` = `settings.default_tax_rate` (15)
- WHEN customer is `is_tax_exempt` THE SYSTEM SHALL override to 0
- THE SYSTEM SHALL display tax as a separate line in totals

#### US-031: ZATCA-Ready Invoices
**As an** Accountant
**I want to** issue ZATCA-compliant invoices
**So that** the business meets phase-2 e-invoicing requirements

- WHEN an invoice is issued THE SYSTEM SHALL generate uuid, icv, pih, invoice_hash, qr_code, signed_xml
- WHEN ZATCA submission succeeds THE SYSTEM SHALL set `zatca_status` = 'cleared'
- IF ZATCA returns warnings THE SYSTEM SHALL persist them in `zatca_warnings` for audit
- IF ZATCA submission fails THE SYSTEM SHALL set `zatca_status` = 'failed' and queue retry

### Treasury

#### US-040: Record Payments
**As an** Accountant
**I want to** record customer payments
**So that** invoice balance and customer debt update

- WHEN a payment is recorded THE SYSTEM SHALL update invoice paid_amount and status
- WHEN paid_amount = grand_total THE SYSTEM SHALL set invoice status to 'paid'
- WHEN paid_amount < grand_total THE SYSTEM SHALL set status to 'paid_partial'

### Permissions

#### US-050: Dynamic Permission Editing
**As a** Super Admin
**I want to** edit role permissions in the UI
**So that** access can be tuned without code changes

- WHEN Super Admin edits a role THE SYSTEM SHALL update `role_has_permissions` immediately
- THE SYSTEM SHALL prevent editing the 'Super Admin' role's permissions

## Non-Functional Requirements

### NFR-001: Performance
- Invoice list page loads under 1 second for 10,000 invoices
- ZATCA submission completes under 5 seconds (sync)

### NFR-002: ZATCA Compliance
- All issued invoices must pass ZATCA Phase-2 validation
- Signed XML must be retained for 6 years (regulatory)

### NFR-003: Auditability
- All financial mutations (invoice issue, payment, refund) recorded in `audit_logs`

### NFR-004: Security
- ZATCA private key stored in `storage/zatca/` with `0600` permissions
- Spatie permission cache rebuilt on role change
- Account Manager cannot access another's customers via direct URL/API

### NFR-005: Data Integrity
- Fresh DB `ammrk_v2` (existing `ammrk_crm` archived, not migrated)
- All financial decimals = `decimal(15,2)`
- Customer VAT validated by regex: `/^3\d{13}3$/`

## Out of Scope (Phase 1)

- Multi-currency
- Recurring invoices
- E-signature workflows beyond ZATCA
- Inventory of physical items
- Supplier/purchase management
- Sales rep / commissions
