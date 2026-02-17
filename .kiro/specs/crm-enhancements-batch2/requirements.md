# Requirements: CRM Enhancements Batch 2

## Overview
Multiple enhancements across transfer pages, sales invoices, customer/rep forms, treasury, purchases, and access control.

---

## US-001: Fix Sales Rep 403 Errors
**As a** sales rep
**I want to** navigate back to sales list and edit customers
**So that** I can manage my workflow without access errors

**Acceptance Criteria:**
- WHEN sales rep clicks "رجوع للمبيعات" on invoice show page THE SYSTEM SHALL allow access (not 403)
- WHEN sales rep clicks "تعديل" on a customer THE SYSTEM SHALL allow access to edit form
- WHEN sales rep submits customer edit form THE SYSTEM SHALL save changes

---

## US-002: Fix Treasury Withdrawal Error
**As an** admin
**I want to** withdraw sales rep treasury balance
**So that** cash is transferred to main treasury

**Acceptance Criteria:**
- WHEN admin clicks "سحب كامل الرصيد" THE SYSTEM SHALL process withdrawal without 500 error
- WHEN withdrawal succeeds THE SYSTEM SHALL record a Payment TYPE_RECEIVED in main treasury
- WHEN viewing treasury page THE SYSTEM SHALL show the withdrawal in collections

---

## US-003: Fix Customer Collection Balance
**As an** admin/sales rep
**I want to** see accurate customer balance in collection form
**So that** I collect the correct amount

**Acceptance Criteria:**
- WHEN viewing collection form THE SYSTEM SHALL display balance = sum of unpaid/partial invoices remaining_amount

---

## US-004: Customer Form Changes
**As an** admin
**I want to** manage customer item type and simplified form
**So that** customer data reflects actual business needs

**Acceptance Criteria:**
- WHEN creating/editing customer THE SYSTEM SHALL show "صنف العميل" dropdown (تلاجة/خاص)
- WHEN creating/editing customer THE SYSTEM SHALL show target as yearly (label change)
- WHEN creating/editing customer THE SYSTEM SHALL NOT show "نوع العميل" field (always retail)
- WHEN creating/editing customer THE SYSTEM SHALL NOT show "الإعدادات المالية" section

---

## US-005: Remove Type from Sales Rep
**As an** admin
**I want to** not see type (تلاجة/خاص) on sales rep forms
**So that** classification is only on customers

**Acceptance Criteria:**
- WHEN creating/editing sales rep THE SYSTEM SHALL NOT show type field
- WHEN viewing sales reps list THE SYSTEM SHALL NOT show type column

---

## US-006: Sales Rep Invoice Simplification
**As a** sales rep
**I want to** see a simplified invoice form
**So that** I only fill relevant fields

**Acceptance Criteria:**
- WHEN sales rep creates invoice THE SYSTEM SHALL hide branch, due date, warehouse, and sales rep fields
- WHEN invoice is saved THE SYSTEM SHALL auto-set branch and warehouse from rep defaults

---

## US-007: Min Price Validation
**As an** admin/sales rep
**I want to** be prevented from selling below minimum price
**So that** pricing policy is enforced

**Acceptance Criteria:**
- WHEN user enters price below min_selling_price THE SYSTEM SHALL show error and block submission
- WHEN user selects product THE SYSTEM SHALL show minimum price hint

---

## US-008: Multi-Item Warehouse Transfer
**As an** admin
**I want to** transfer multiple items between warehouses at once
**So that** I don't repeat the process for each item

**Acceptance Criteria:**
- WHEN admin selects source warehouse THE SYSTEM SHALL show only products with stock > 0
- WHEN admin adds items THE SYSTEM SHALL show available quantity for each
- WHEN admin submits THE SYSTEM SHALL transfer all items in one transaction
- WHEN searching products THE SYSTEM SHALL support search/filter (select2)

---

## US-009: Multi-Item Sales Rep Allocation
**As an** admin
**I want to** allocate multiple items to a sales rep at once
**So that** I don't repeat the process for each item

**Acceptance Criteria:**
- WHEN admin selects source warehouse THE SYSTEM SHALL show only products with stock > 0
- WHEN admin adds items THE SYSTEM SHALL show available quantity for each
- WHEN admin submits THE SYSTEM SHALL allocate all items in one transaction
- WHEN searching products THE SYSTEM SHALL support search/filter (select2)

---

## US-010: Invoice Print Redesign
**As a** sales rep/admin
**I want to** print a clean, one-page invoice
**So that** it looks professional for customers

**Acceptance Criteria:**
- WHEN printing THE SYSTEM SHALL show: logo + invoice number, supervisor phone + rep phone, customer info + date, items table, paid/remaining
- WHEN admin sets supervisor phone in settings THE SYSTEM SHALL use it in print
- WHEN printing THE SYSTEM SHALL fit on one page

---

## US-011: Filter Products by Supplier in Purchases
**As an** admin
**I want to** see only supplier's products when creating purchase
**So that** I pick the right products quickly

**Acceptance Criteria:**
- WHEN admin selects supplier THE SYSTEM SHALL show only that supplier's products
- WHEN new product is purchased from supplier THE SYSTEM SHALL remember the association
