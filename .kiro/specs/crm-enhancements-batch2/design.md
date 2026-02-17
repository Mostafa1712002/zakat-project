# Design: CRM Enhancements Batch 2

## Architecture Overview

All changes follow existing Laravel MVC patterns. No new packages needed except CDN for Select2 in transfer pages.

---

## Task 1: Fix 403 Errors (Middleware)

**File:** `app/Http/Middleware/SalesRepAccessMiddleware.php`

Add to `$allowedRoutes`:
```php
'sales.index',       // عرض قائمة المبيعات
'customers.edit',    // تعديل عميل
'customers.update',  // حفظ تعديل عميل
```

---

## Task 2: Fix Treasury Withdrawal

**File:** `app/Http/Controllers/SalesRepAccountController.php`

Issue: The form sends POST without `amount` or `notes` fields. When validation has `nullable` fields not present in request, Laravel may not include them in `$validated`.

Fix: Use null coalescing on `$validated` access and add `branch_id` to Payment:
```php
$notes = $validated['notes'] ?? null;
// ...
Payment::create([
    // ... existing fields
    'branch_id' => $salesRep->branch_id,
    'notes' => 'سحب من خزينة المندوب: ' . $salesRep->name . ($notes ? ' - ' . $notes : ''),
]);
```

---

## Task 3: Customer Collection Balance

**File:** `resources/views/payments/collect-from-customer.blade.php`

Replace `$customer->current_balance` display with calculated sum:
```php
$calculatedBalance = $unpaidSales->sum('remaining_amount');
```
Pass from controller or calculate in view.

---

## Task 4: Customer Form Changes

### Migration: `add_item_type_to_customers_table`
```sql
ALTER TABLE customers ADD COLUMN item_type VARCHAR(20) NULLABLE AFTER name;
```

### Files to modify:
- `app/Models/Customer.php` - Add `item_type` to fillable
- `app/Http/Controllers/CustomerController.php` - Add validation, hardcode type='retail'
- `resources/views/customers/create.blade.php` - Add item_type dropdown, remove type dropdown, remove financial section, rename target label
- `resources/views/customers/edit.blade.php` - Same changes

---

## Task 5: Remove Type from Sales Rep

### Migration: `make_type_nullable_in_sales_reps_table`
```sql
ALTER TABLE sales_reps MODIFY type VARCHAR(255) NULL;
```

### Files to modify:
- `resources/views/sales-reps/create.blade.php` - Remove type dropdown
- `resources/views/sales-reps/edit.blade.php` - Remove type dropdown
- `resources/views/sales-reps/index.blade.php` - Remove type column
- `app/Http/Controllers/SalesRepController.php` - Make type nullable in validation

---

## Task 6: Sales Rep Invoice Simplification

**File:** `resources/views/sales/create.blade.php`

Wrap fields in `@unless(auth()->user()->isSalesRep())`:
- Branch dropdown (lines 111-134)
- Due date (lines 49-52)
- Warehouse dropdown (lines 64-73)
- Sales rep dropdown (lines 124-134)

For sales rep, auto-set via hidden inputs from controller data.

---

## Task 7: Min Price Validation

**File:** `resources/views/sales/create.blade.php`

JavaScript changes in the existing script block:
- On product select: show min price hint below price input
- On price input change: validate against `productsData[id].min_price`
- On form submit: block if any row has price < min_price
- CSS: `.min-price-error` class (red text, red border)

---

## Task 8: Multi-Item Warehouse Transfer

### New AJAX Route:
```php
Route::get('warehouses/{warehouse}/products-with-stock', [WarehouseController::class, 'productsWithStock']);
```

### Controller: `WarehouseController`
- `productsWithStock($warehouse)` - Returns JSON of products with stock in warehouse
- `transfer()` - Accept array of items instead of single item

### View: `resources/views/warehouses/transfer.blade.php`
- Complete redesign with dynamic item rows
- Source/destination warehouse selectors at top
- On source warehouse change: fetch products via AJAX
- Select2 on product dropdowns
- Show available qty badge per product

---

## Task 9: Multi-Item Sales Rep Allocation

### Controller: `SalesRepAccountController`
- `allocateStock()` - Accept array of items
- Reuse `productsWithStock` AJAX endpoint from Task 8

### View: `resources/views/admin/sales-rep-inventory/show.blade.php`
- Redesign allocation form with multi-item table
- Warehouse selector, dynamic product rows with Select2
- Show available qty from selected warehouse

---

## Task 10: Invoice Print Redesign

### Settings:
- Add `supervisor_phone` to company settings form
- `resources/views/settings/company.blade.php` - Add field

### View: `resources/views/sales/show.blade.php`
- Simplify print CSS for one-page layout
- Show: logo, invoice #, supervisor phone, rep phone, customer, date, items, totals

---

## Task 11: Filter Products by Supplier

### Migration: `create_product_supplier_table`
```sql
CREATE TABLE product_supplier (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    product_id BIGINT REFERENCES products(id),
    supplier_id BIGINT REFERENCES suppliers(id),
    UNIQUE(product_id, supplier_id)
);
```

### Models:
- `Supplier.php` - Add `products()` BelongsToMany
- `Product.php` - Add `suppliers()` BelongsToMany

### Auto-sync: In `PurchaseController::store()`, sync product-supplier after purchase

### AJAX Route:
```php
Route::get('suppliers/{supplier}/products', [SupplierController::class, 'products']);
```

### View: `resources/views/purchases/create.blade.php`
- On supplier select: fetch products via AJAX, filter dropdown
