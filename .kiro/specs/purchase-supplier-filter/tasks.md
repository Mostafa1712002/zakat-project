# Tasks: Purchase Supplier-Product Filtering

## Phase 1: Fix Purchase Create Page

### Task 1.1: Fix supplier change event and initial state
- [x] Change supplier change handler from native addEventListener to jQuery .on('change')
- [x] Start product dropdowns empty (no products until supplier selected)
- [x] Disable product select until supplier is chosen
- [x] Show helper text "اختر المورد أولاً" when no supplier selected

**Outcome:** Product dropdown only shows supplier's products
**Dependencies:** None

### Task 1.2: Fix purchase edit page
- [x] Edit page doesn't have product dropdowns - no fix needed

**Outcome:** N/A - edit page only edits header fields
**Dependencies:** Task 1.1

### Task 1.3: Remove old pivot table sync
- [x] Remove syncWithoutDetaching from PurchaseController::store()
- [x] Remove unused $products from PurchaseController::create()

**Outcome:** Clean data model, no old pivot table usage
**Dependencies:** None

## Phase 2: Testing

### Task 2.1: Verify on production
- [ ] Test: Select supplier → only their products appear
- [ ] Test: Change supplier → products update
- [ ] Test: Add new row → shows same supplier's products
- [ ] Test: Edit existing purchase → correct products shown

**Outcome:** Feature verified working
**Dependencies:** Tasks 1.1, 1.2, 1.3

---

## Progress Tracking

| Phase | Tasks | Completed | Status |
|-------|-------|-----------|--------|
| 1. Fix Pages | 3 | 3 | Done |
| 2. Testing | 1 | 0 | Pending Deploy |
| **Total** | **4** | **3** | **75%** |
