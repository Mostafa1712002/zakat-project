# خطة تنفيذ نظام CRM الكامل

## المرحلة 1: البنية التحتية (Infrastructure)
- [ ] 1.1 إنشاء Layout مشترك للصفحات
- [ ] 1.2 إنشاء نظام التنقل (Routes)
- [ ] 1.3 إنشاء Controllers أساسية

## المرحلة 2: الأقسام والأصناف (Categories & Products)
- [ ] 2.1 صفحة الأقسام - عرض القائمة
- [ ] 2.2 صفحة الأقسام - إضافة/تعديل
- [ ] 2.3 صفحة الأصناف - عرض القائمة
- [ ] 2.4 صفحة الأصناف - إضافة/تعديل

## المرحلة 3: العملاء والمندوبين (Customers & Sales Reps)
- [ ] 3.1 صفحة العملاء - عرض القائمة
- [ ] 3.2 صفحة العملاء - إضافة/تعديل
- [ ] 3.3 صفحة المندوبين - عرض القائمة
- [ ] 3.4 صفحة المندوبين - إضافة/تعديل

## المرحلة 4: المخازن (Warehouses)
- [ ] 4.1 صفحة المخازن - عرض القائمة
- [ ] 4.2 صفحة المخازن - إضافة/تعديل
- [ ] 4.3 صفحة تحويل المخزون

## المرحلة 5: المبيعات والفواتير (Sales & Invoices)
- [ ] 5.1 صفحة المبيعات - عرض القائمة
- [ ] 5.2 صفحة إنشاء فاتورة بيع
- [ ] 5.3 صفحة عرض/طباعة الفاتورة
- [ ] 5.4 صفحة الفواتير الآجلة

## المرحلة 6: المشتريات (Purchases)
- [ ] 6.1 صفحة المشتريات - عرض القائمة
- [ ] 6.2 صفحة إنشاء فاتورة شراء
- [ ] 6.3 صفحة الموردين

## المرحلة 7: الموظفين والمصروفات (Employees & Expenses)
- [ ] 7.1 صفحة الموظفين - عرض القائمة
- [ ] 7.2 صفحة الموظفين - إضافة/تعديل
- [ ] 7.3 صفحة المصروفات - عرض القائمة
- [ ] 7.4 صفحة المصروفات - إضافة

## المرحلة 8: التقارير والأرباح (Reports & Profits)
- [ ] 8.1 صفحة الأرباح والخسائر
- [ ] 8.2 تقارير المبيعات
- [ ] 8.3 تقارير المخزون

## المرحلة 9: الإعدادات (Settings)
- [ ] 9.1 إعدادات الشركة
- [ ] 9.2 إعدادات المستخدمين
- [ ] 9.3 إعدادات النظام

## المرحلة 10: التكامل النهائي
- [ ] 10.1 ربط جميع الصفحات
- [ ] 10.2 إعادة تفعيل نظام المصادقة
- [ ] 10.3 اختبار شامل

---
## الملفات المطلوب إنشاؤها:

### Controllers:
- CategoryController
- ProductController
- CustomerController
- SalesRepController
- WarehouseController
- SaleController
- InvoiceController
- PurchaseController
- SupplierController
- EmployeeController
- ExpenseController
- ReportController
- SettingController

### Views (resources/views/):
```
layouts/
  app.blade.php          # Layout مشترك

categories/
  index.blade.php        # قائمة الأقسام
  create.blade.php       # إضافة قسم
  edit.blade.php         # تعديل قسم

products/
  index.blade.php        # قائمة الأصناف
  create.blade.php       # إضافة صنف
  edit.blade.php         # تعديل صنف

customers/
  index.blade.php        # قائمة العملاء
  create.blade.php       # إضافة عميل
  edit.blade.php         # تعديل عميل

sales-reps/
  index.blade.php        # قائمة المندوبين
  create.blade.php       # إضافة مندوب

warehouses/
  index.blade.php        # قائمة المخازن
  create.blade.php       # إضافة مخزن
  transfer.blade.php     # تحويل مخزون

sales/
  index.blade.php        # قائمة المبيعات
  create.blade.php       # فاتورة بيع جديدة

invoices/
  index.blade.php        # قائمة الفواتير
  show.blade.php         # عرض فاتورة
  print.blade.php        # طباعة فاتورة

purchases/
  index.blade.php        # قائمة المشتريات
  create.blade.php       # فاتورة شراء

suppliers/
  index.blade.php        # قائمة الموردين
  create.blade.php       # إضافة مورد

employees/
  index.blade.php        # قائمة الموظفين
  create.blade.php       # إضافة موظف

expenses/
  index.blade.php        # قائمة المصروفات
  create.blade.php       # إضافة مصروف

reports/
  profits.blade.php      # تقرير الأرباح
  sales.blade.php        # تقرير المبيعات
  inventory.blade.php    # تقرير المخزون

settings/
  index.blade.php        # الإعدادات العامة
  company.blade.php      # إعدادات الشركة
  users.blade.php        # إدارة المستخدمين
```

### Routes (routes/web.php):
- Resource routes لكل controller
- تجميع routes حسب الوظيفة

---
**تاريخ الإنشاء:** 2026-01-05
**الحالة:** قيد التنفيذ
