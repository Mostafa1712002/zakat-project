# Design: Purchase Supplier-Product Filtering

## Root Cause Analysis

### Bug 1: Select2 Change Event
The supplier `<select>` uses native `addEventListener('change', ...)` but Select2's jQuery `.trigger('change')` may not fire native listeners consistently. Fix: use jQuery `.on('change', ...)` after Select2 init.

### Bug 2: Initial Product Dropdown
On page load, ALL 95 products populate the dropdown before any supplier is selected. Users can select wrong-supplier products. Fix: start with empty dropdown, require supplier selection first.

### Bug 3: Old Pivot Table Sync
`PurchaseController::store()` still calls `$supplier->products()->syncWithoutDetaching()` using the old M2M pivot table. This is dead code since we now use `supplier_id` FK on products. Fix: remove it.

## Files to Modify

| File | Change |
|------|--------|
| `resources/views/purchases/create.blade.php` | Fix JS: jQuery change handler, empty initial dropdown |
| `resources/views/purchases/edit.blade.php` | Same JS fixes for edit page |
| `app/Http/Controllers/PurchaseController.php` | Remove syncWithoutDetaching |
