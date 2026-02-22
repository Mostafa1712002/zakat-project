# Requirements: Invoice Contact Persons Footer

## Overview
Add manageable contact persons (name + phone) that appear at the bottom of every sales and purchase invoice. Contacts are configured from the company settings page and stored as JSON in the existing settings table.

## User Stories

### US-001: Manage Invoice Contacts in Settings
**As a** system administrator
**I want to** add, edit, and remove contact persons from the company settings page
**So that** I can control which contacts appear on printed invoices

**Acceptance Criteria:**
- WHEN the admin visits Settings → Company THE SYSTEM SHALL display a "جهات اتصال الفاتورة" section
- WHEN the admin clicks "+ إضافة جهة اتصال" THE SYSTEM SHALL add a new row with name and phone fields
- WHEN the admin clicks "✕" on a contact row THE SYSTEM SHALL remove that row (minimum 1 row remains)
- WHEN the admin saves the form THE SYSTEM SHALL store contacts as JSON in `invoice_contacts` setting key
- WHEN the admin revisits the page THE SYSTEM SHALL pre-populate existing contacts from settings

### US-002: Display Contacts on Sales Invoice
**As a** user viewing a sales invoice
**I want to** see contact persons at the bottom of the invoice
**So that** the customer knows who to contact

**Acceptance Criteria:**
- WHEN a sales invoice is viewed on screen THE SYSTEM SHALL display contacts below the signatures section
- WHEN a sales invoice is printed THE SYSTEM SHALL include contacts in the print layout
- WHEN a sales invoice PDF is generated THE SYSTEM SHALL include contacts in the PDF
- WHEN no contacts are configured THE SYSTEM SHALL not display the contacts section

### US-003: Display Contacts on Purchase Invoice
**As a** user viewing a purchase invoice
**I want to** see contact persons at the bottom of the invoice
**So that** the supplier knows who to contact

**Acceptance Criteria:**
- WHEN a purchase invoice is viewed on screen THE SYSTEM SHALL display contacts below the signatures section
- WHEN a purchase invoice is printed THE SYSTEM SHALL include contacts in the print layout
- WHEN a purchase invoice PDF is generated THE SYSTEM SHALL include contacts in the PDF
- WHEN no contacts are configured THE SYSTEM SHALL not display the contacts section

### US-004: Fix Deploy Script
**As a** developer
**I want to** pull latest changes before pushing during deployment
**So that** deployment doesn't fail due to diverged branches

**Acceptance Criteria:**
- WHEN deploy.sh runs THE SYSTEM SHALL execute `git pull --rebase origin main` before `git push origin main`

## Non-Functional Requirements

### NFR-001: Data Storage
- Contacts stored as JSON array in existing `settings` table (key: `invoice_contacts`)
- No database migration required
- Format: `[{"name":"...","phone":"..."},...]`

### NFR-002: PDF Compatibility
- PDF layout uses `display: table-cell` for wkhtmltopdf compatibility
- No flexbox in PDF templates
