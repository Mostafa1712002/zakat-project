# Tasks: CRM Enhancements Batch 2

## Phase 1: Quick Fixes (403 errors & bugs)

### Task 1.1: Fix Sales Rep 403 Errors
- [x] Add `sales.index`, `customers.edit`, `customers.update` to SalesRepAccessMiddleware allowedRoutes
- [x] Deploy and verify

**Outcome:** Sales rep can navigate back to sales list and edit customers
**Dependencies:** None

### Task 1.2: Fix Treasury Withdrawal Error
- [x] Fix `withdrawToMain()` - use null coalescing for notes, add branch_id to Payment
- [x] Add try-catch with meaningful error messages
- [x] Deploy and verify withdrawal works

**Outcome:** Admin can withdraw sales rep treasury balance without 500 error
**Dependencies:** None

### Task 1.3: Fix Treasury Withdrawal Recording
- [x] Verify Payment is created with correct fields (payment_date, status, branch_id)
- [x] Test that treasury page shows the payment in collections
- [x] Fix any date range or filtering issues

**Outcome:** Withdrawal appears in admin treasury page
**Dependencies:** Task 1.2

### Task 1.4: Fix Customer Collection Balance
- [x] Calculate balance from unpaid invoices sum(remaining_amount) instead of stored field
- [x] Update collect-from-customer view to show calculated balance
- [x] Pass calculated balance from PaymentController

**Outcome:** Collection form shows accurate balance from actual unpaid invoices
**Dependencies:** None

---

## Phase 2: Form Simplifications

### Task 2.1: Customer Form Changes
- [x] Create migration: add `item_type` column to customers table
- [x] Add `item_type` to Customer model fillable
- [x] Add item_type dropdown (تلاجة/خاص) to create/edit forms
- [x] Remove "نوع العميل" dropdown from create/edit forms
- [x] Remove "الإعدادات المالية" section from create/edit forms
- [x] Rename target label to "التارجت السنوي"
- [x] Update CustomerController validation (hardcode type='retail', add item_type)
- [x] Run migration and deploy

**Outcome:** Customer form simplified with item type field
**Dependencies:** None

### Task 2.2: Remove Type from Sales Rep
- [x] Create migration: make `type` nullable in sales_reps table
- [x] Remove type dropdown from create.blade.php
- [x] Remove type dropdown from edit.blade.php
- [x] Remove type column/badge from index.blade.php
- [x] Make type nullable in SalesRepController validation
- [x] Run migration and deploy

**Outcome:** Sales rep type (تلاجة/خاص) removed from all forms
**Dependencies:** None

### Task 2.3: Sales Rep Invoice Form Simplification
- [x] Wrap branch, due_date, warehouse, sales_rep fields in `@unless(auth()->user()->isSalesRep())`
- [x] Add hidden inputs with rep defaults (branch_id, warehouse_id)
- [x] Test invoice creation as sales rep

**Outcome:** Sales rep sees simplified invoice form
**Dependencies:** None

---

## Phase 3: Validation & Print

### Task 3.1: Min Price Validation in Sales Invoice
- [x] Add min price display hint when product is selected
- [x] Add JS validation on price input (red border if below min)
- [x] Block form submission if any price < min_selling_price
- [x] Show error message with product name and min price

**Outcome:** Users cannot sell below minimum price
**Dependencies:** None

### Task 3.2: Invoice Print Redesign
- [x] Add supervisor_phone setting to company settings page
- [x] Redesign print layout in sales/show.blade.php
- [x] Include: logo, invoice #, supervisor phone, rep phone, customer, date, items, paid/remaining
- [x] Optimize CSS to fit one page
- [x] Test print output

**Outcome:** Clean one-page invoice print
**Dependencies:** None

---

## Phase 4: Multi-Item Transfers

### Task 4.1: Multi-Item Warehouse Transfer
- [x] Add AJAX endpoint: `warehouses/{warehouse}/products-with-stock`
- [x] Redesign transfer.blade.php with multi-item form
- [x] Filter products by source warehouse
- [x] Show available quantity per product
- [x] Update WarehouseController::transfer() for array of items
- [x] Deploy and test

**Outcome:** Admin can transfer multiple items between warehouses at once
**Dependencies:** None

### Task 4.2: Multi-Item Sales Rep Allocation
- [x] Redesign allocation form in admin/sales-rep-inventory/show.blade.php
- [x] Use same AJAX endpoint from Task 4.1
- [x] Filter products by selected warehouse
- [x] Show available quantity per product
- [x] Update SalesRepAccountController::allocateStock() for array of items
- [x] Deploy and test

**Outcome:** Admin can allocate multiple items to sales rep at once
**Dependencies:** Task 4.1

---

## Phase 5: Supplier Products

### Task 5.1: Filter Products by Supplier in Purchases
- [x] Create migration: product_supplier pivot table
- [x] Add BelongsToMany relationships to Supplier and Product models
- [x] Auto-sync products to suppliers in PurchaseController::store()
- [x] Add AJAX endpoint: suppliers/{supplier}/products
- [x] Update purchases/create.blade.php to filter products by supplier
- [x] Deploy and test

**Outcome:** Purchase form shows only supplier's products
**Dependencies:** None

---

## Progress Tracking

| Phase | Tasks | Completed | Status |
|-------|-------|-----------|--------|
| 1. Quick Fixes | 4 | 4 | Done |
| 2. Form Simplifications | 3 | 3 | Done |
| 3. Validation & Print | 2 | 2 | Done |
| 4. Multi-Item Transfers | 2 | 2 | Done |
| 5. Supplier Products | 1 | 1 | Done |
| **Total** | **12** | **12** | **100%** |
