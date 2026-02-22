# Tasks: Bugfixes Batch 1

## Task 1: Supervisor Phone in Settings
- [x] Verify field exists in settings/company.blade.php
- [x] Verify controller saves and retrieves it
- [x] Verify it shows on sales invoice print

**Outcome:** Already implemented. Field at /settings/company, dynamic on invoices.

---

## Task 2: Filter Purchase Products by Supplier
- [x] Verify fetch endpoint /suppliers/{id}/products works
- [x] Fix: when supplier has no linked products, fallback to showing all products

**Fix:** `purchases/create.blade.php` - changed `supplierProducts = products` to `supplierProducts = products.length > 0 ? products : null` so empty results fall back to all products.

---

## Task 3: Fix "Undefined array key due_date" in Sales Create
- [x] Identify root cause: due_date field hidden for sales reps via @unless
- [x] Fix store() method line 226: `$validated['due_date']` → `$validated['due_date'] ?? null`
- [x] Fix update() method line 505: same fix

**Fix:** `SaleController.php` lines 226 and 505 - added `?? null` fallback.

---

## Task 4: Treasury Withdrawal Not Reflecting in Dashboard
- [x] Analyze profit formula: monthlySales - monthlyPurchases - monthlyExpenses
- [x] Add cash-based "الرصيد النقدي" card to dashboard
- [x] Include collections + cash sales - expenses - supplier payments

**Fix:** Added `monthly_cash_balance` to DashboardController and new stat card in dashboard.blade.php. This includes rep treasury withdrawals (via Payment records) in the calculation.

---

## Task 5: Sidebar Dynamic Name/Logo + Remove Dead Settings Cards
- [x] Make sidebar logo and name dynamic from settings DB
- [x] Add Cache::forget('sidebar_settings') on settings update
- [x] Remove "إعدادات عامة" card (dead link href="#")
- [x] Remove "الفروع" card (no controller/route exists)

---

## Progress Tracking

| Task | Status |
|------|--------|
| 1. Supervisor Phone | Done (already existed) |
| 2. Purchase Product Filter | Done |
| 3. Sales due_date Error | Done |
| 4. Treasury Dashboard | Done |
| 5. Sidebar + Settings Cards | Done |
| **Total** | **5/5 - 100%** |
