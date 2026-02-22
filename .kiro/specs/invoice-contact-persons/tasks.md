# Tasks: Invoice Contact Persons Footer

## Phase 1: Deploy Fix

### Task 1.1: Fix deploy.sh
- [x] Add `git pull --rebase origin main` before `git push origin main`

**Outcome:** Deploy script pulls latest before pushing
**Dependencies:** None

---

## Phase 2: Settings Backend

### Task 2.1: Controller — Save Contacts
- [x] Add `contacts` array validation to `updateCompany()`
- [x] Filter empty rows and save as JSON to `invoice_contacts` setting
- [x] Exclude `contacts` from text settings loop

**Outcome:** Contacts saved as JSON in settings table
**Dependencies:** None

### Task 2.2: Controller — Load Contacts
- [x] Add `invoice_contacts` to `getCompanySettings()` cached array

**Outcome:** Contacts available in company settings view
**Dependencies:** Task 2.1

---

## Phase 3: Settings UI

### Task 3.1: Contact Management Section
- [x] Add "جهات اتصال الفاتورة" section to company.blade.php
- [x] Dynamic add/remove rows via JS (`addContact()`, `removeContact()`)
- [x] Pre-populate from existing `invoice_contacts` setting
- [x] Form fields: `contacts[index][name]`, `contacts[index][phone]`

**Outcome:** Admin can manage invoice contacts from settings
**Dependencies:** Task 2.2

---

## Phase 4: Invoice Display

### Task 4.1: Sales Invoice — Show + Print
- [x] Load `invoice_contacts` in `SaleController::show()`
- [x] Add contacts section below signatures in `sales/show.blade.php`
- [x] Add contacts in print-minimal layout
- [x] Add CSS (screen + print styles)

**Outcome:** Contacts visible on sales invoice screen and print
**Dependencies:** Task 2.1

### Task 4.2: Purchase Invoice — Show + Print
- [x] Load `invoice_contacts` in `PurchaseController::show()`
- [x] Add contacts section below signatures in `purchases/show.blade.php`
- [x] Add contacts in print-minimal layout
- [x] Add CSS (screen + print styles)

**Outcome:** Contacts visible on purchase invoice screen and print
**Dependencies:** Task 2.1

### Task 4.3: Sales Invoice — PDF
- [x] Load `invoice_contacts` in `SaleController::pdf()`
- [x] Add contacts section to `pdf/sale.blade.php` with table-cell layout
- [x] Add CSS for PDF rendering

**Outcome:** Contacts visible in sales PDF
**Dependencies:** Task 2.1

### Task 4.4: Purchase Invoice — PDF
- [x] Load `invoice_contacts` in `PurchaseController::pdf()`
- [x] Add contacts section to `pdf/purchase.blade.php` with table-cell layout
- [x] Add CSS for PDF rendering

**Outcome:** Contacts visible in purchase PDF
**Dependencies:** Task 2.1

---

## Progress Tracking

| Phase | Tasks | Completed | Status |
|-------|-------|-----------|--------|
| 1. Deploy Fix | 1 | 1 | Done |
| 2. Settings Backend | 2 | 2 | Done |
| 3. Settings UI | 1 | 1 | Done |
| 4. Invoice Display | 4 | 4 | Done |
| **Total** | **8** | **8** | **100%** |
