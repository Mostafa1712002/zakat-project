# Tasks: Inventory & Treasury Fixes

## Phase 1: إصلاح عرض مصروفات المندوب

### Task 1.1: فحص وإصلاح جدول المصروفات
- [x] فحص `SalesRepAccountController@myExpenses`
- [x] فحص view `sales-rep-account/expenses.blade.php`
- [x] إضافة `sales_rep_id` إلى fillable في Expense model
- [x] اختبار العرض

**Outcome:** المصروفات تظهر في الجدول ✅
**Dependencies:** None

---

## Phase 2: خصم المخزون عند البيع

### Task 2.1: إضافة زر تأكيد الفاتورة
- [x] فحص `SaleController@confirm` - الكود موجود وصحيح
- [x] إضافة زر "تأكيد الفاتورة" في `sales/show.blade.php`
- [x] الفاتورة تبقى مسودة حتى يتم تأكيدها

**Outcome:** المخزون يُخصم عند تأكيد الفاتورة ✅
**Dependencies:** None

---

## Phase 3: تسجيل سحب خزينة المندوب

### Task 3.1: إضافة تسجيل في خزينة الشركة
- [x] تعديل `SalesRepAccountController@withdrawToMain`
- [x] إضافة تسجيل Payment كـ TYPE_RECEIVED
- [x] إضافة use Payment

**Outcome:** المبلغ المسحوب يظهر في خزينة الأدمن ✅
**Dependencies:** None

---

## Phase 4: منع سحب العمولة المكررة

### Task 4.1: إضافة تتبع سحب العمولة
- [x] إنشاء migration لجدول `commission_withdrawals`
- [x] إنشاء model `CommissionWithdrawal`
- [x] تعديل `SalesRepController@showWithdrawCommission` للفحص
- [x] تعديل `SalesRepController@withdrawCommission` للتسجيل
- [x] تعديل view لإظهار "تم السحب" بدلاً من زر السحب

**Outcome:** زر السحب يعمل مرة واحدة فقط ✅
**Dependencies:** None

---

## Progress Tracking

| Phase | Tasks | Completed | Status |
|-------|-------|-----------|--------|
| 1. مصروفات المندوب | 1 | 1 | ✅ Done |
| 2. خصم المخزون | 1 | 1 | ✅ Done |
| 3. خزينة الأدمن | 1 | 1 | ✅ Done |
| 4. سحب العمولة | 1 | 1 | ✅ Done |
| **Total** | **4** | **4** | **100%** |
