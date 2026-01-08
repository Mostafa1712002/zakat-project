# CRM Map (Laravel)

خطة تنفيذ نظام CRM لسلسلة فروع تشمل المبيعات والمشتريات والفواتير والمخازن والموظفين والأصناف والأقسام والأرباح والمصروفات والعملاء والمندوبين والإدارة.

## الهدف
بناء نظام CRM عملي وقابل للتوسع يدعم أكثر من أدمن وأكثر من فرع، مع لوحة تحكم وإحصائيات كاملة وتقارير تشغيلية ومالية.

## الوحدات الأساسية

### 1) العملاء
- ملف عميل كامل (تصنيف، حد ائتماني، ملاحظات).
- سجل تعاملات العميل (فواتير، مرتجعات، مدفوعات).
- تتبع المدفوع والمتبقي لكل فاتورة بيع للعميل.
- تقرير العملاء المتأخرين والعملاء الأعلى شراءً.

### 2) الموردين
- ملف مورد كامل (تصنيف، شروط دفع، ملاحظات).
- سجل تعاملات المورد (فواتير شراء، مدفوعات، مرتجعات).
- تتبع المدفوع والمتبقي لكل فاتورة شراء للمورد.

### 3) المندوبين
- ربط المندوبين بالمناطق والعملاء.
- أهداف مبيعات وعمولات.
- تقارير أداء المندوبين.

### 4) المبيعات
- فواتير بيع (نقد/آجل).
- خصومات وعروض.
- خصم على مستوى الفاتورة أو على مستوى الصنف (نسبة أو مبلغ ثابت).
- تسجيل الدفعات: المدفوع والمتبقي وحالة السداد.
- مرتجعات المبيعات.
- تقارير حسب الفرع/المندوب/الصنف/القسم.

### 5) المشتريات
- موردون وأوامر شراء.
- فواتير شراء واستحقاقات.
- خصم على مستوى الفاتورة أو على مستوى الصنف (نسبة أو مبلغ ثابت).
- تسجيل الدفعات للمورد: المدفوع والمتبقي وحالة السداد.
- مرتجعات المشتريات.
- ربط تلقائي بالمخزون.

### 6) الفواتير
- توحيد نماذج الفواتير (بيع/شراء).
- طرق دفع متعددة.
- تصدير PDF/Excel.
- إظهار في الفاتورة الخارجية: الأصناف والكميات والأسعار والخصم (نسبة/مبلغ) لكل صنف وإجمالي الخصم.
- إظهار المدفوع والمتبقي وطريقة الدفع وحالة السداد.
- إظهار بيانات العميل في فواتير البيع، وبيانات المورد في فواتير الشراء.
- إبراز الأصناف المخصومة مع نوع الخصم (نسبة/مبلغ).
- إظهار الضريبة إن وجدت، رقم أمر الشراء، اسم المندوب، وحقول الاستلام/التوقيع.

### 7) المخازن
- أكثر من مخزن لكل فرع.
- تحويلات بين المخازن.
- جرد وتنبيهات حد أدنى.

### 8) الأصناف
- إدارة SKU/باركود/وحدات متعددة.
- أسعار بيع متعددة.
- تتبع تكلفة وهامش ربح.

### 9) أقسام الأصناف
- أقسام رئيسية وفرعية.
- تقارير حسب القسم.

### 10) الموظفين
- بيانات الموظف وربط بالدور والفرع.
- حضور/انصراف (اختياري).

### 11) المصروفات
- تصنيفات مصروفات.
- ربط المصروف بفرع/مشروع.
- تقارير شهرية/سنوية.

### 12) الأرباح
- صافي الربح = المبيعات - (تكلفة البضاعة + المصروفات).
- أرباح حسب الفرع/المخزن/الصنف.

### 13) الإدارة والصلاحيات
- أكثر من أدمن.
- صلاحيات دقيقة لكل قسم.
- سجل تدقيق كامل للتعديلات.

### 14) لوحة التحكم والإحصائيات
- مبيعات/مشتريات/أرباح يومية وشهرية.
- حركة مخزون وتنبيهات نقص.
- أفضل العملاء/الأصناف.

## خريطة البيانات (نماذج أساسية)
- users, roles, permissions
- branches, warehouses
- customers, suppliers, sales_reps
- products, categories, units, price_tiers
- sales (customer_id, discount_type/value/amount, paid_amount, remaining_amount), sales_items (item_discount_type/value/amount)
- purchases (supplier_id, discount_type/value/amount, paid_amount, remaining_amount), purchase_items (item_discount_type/value/amount)
- invoices, payments (payable_type, payable_id, amount, method, paid_at)
- stock_movements, inventory_levels
- expenses, expense_categories
- audit_logs

## الخطة على شكل مهام
### المرحلة 0: التأسيس والبنية ✅ (COMPLETED)
- [x] إنشاء الإعدادات الأساسية للمشروع (ENV/DB/Locales).
- [x] تفعيل نظام المستخدمين والصلاحيات والأدوار (Admins متعددة).
- [x] إعداد الفروع والسلاسل وربط المستخدمين بالفروع.
- [x] إنشاء سجل تدقيق (Audit Logs).

### المرحلة 1: البيانات الأساسية (Master Data) ✅ (COMPLETED)
- [x] أقسام الأصناف (رئيسي/فرعي) مع CRUD.
- [x] الأصناف (SKU/Barcode/وحدات/أسعار متعددة).
- [x] العملاء (تصنيفات، حد ائتماني، ملاحظات).
- [x] الموردين (تصنيفات، شروط دفع، ملاحظات).
- [x] الموظفين والمندوبين وربطهم بالفروع/المناطق.

### المرحلة 2: المخازن والمخزون ✅ (COMPLETED)
- [x] إنشاء المخازن وربطها بالفروع.
- [x] حركة المخزون (إضافة/صرف/تحويل).
- [x] جرد وتنبيهات حد أدنى وحد أعلى.

### المرحلة 3: المبيعات ✅ (COMPLETED)
- [x] إنشاء فواتير بيع (نقد/آجل).
- [x] خصم على الفاتورة أو الصنف (نسبة/مبلغ).
- [x] تسجيل المدفوع والمتبقي وحالة السداد.
- [x] ربط الفاتورة بالعميل والمندوب والفرع والمخزن.
- [x] مرتجعات المبيعات.

### المرحلة 4: المشتريات ✅ (COMPLETED)
- [x] أوامر شراء وفواتير شراء.
- [x] خصم على الفاتورة أو الصنف (نسبة/مبلغ).
- [x] تسجيل المدفوع والمتبقي وحالة السداد للمورد.
- [x] ربط الفاتورة بالمورد والفرع والمخزن.
- [x] مرتجعات المشتريات.

### المرحلة 5: الدفعات والفواتير الخارجية ✅ (COMPLETED - DB Layer)
- [x] إدارة الدفعات (Payments) متعددة لكل فاتورة.
- [x] إظهار تفاصيل الفاتورة الخارجية: بيانات العميل/المورد، الأصناف، الكميات، الأسعار، الخصم (نسبة/مبلغ) لكل صنف وإجمالي الخصم.
- [x] إظهار المدفوع والمتبقي وطريقة الدفع وحالة السداد.
- [x] إضافة حقول الفاتورة الخارجية: الضريبة، رقم أمر الشراء، اسم المندوب، الاستلام/التوقيع.
- [ ] تصدير PDF/Excel (UI Layer).

### المرحلة 6: المصروفات والأرباح ✅ (COMPLETED - DB Layer)
- [x] تسجيل المصروفات وتصنيفاتها وربطها بالفرع.
- [x] احتساب الأرباح (المبيعات - التكاليف - المصروفات).
- [ ] تقارير أرباح حسب الفرع/الصنف/الفترة (UI Layer).

### المرحلة 7: لوحة التحكم والتقارير (PENDING - UI Layer)
- [ ] لوحة تحكم بإحصائيات (مبيعات/مشتريات/أرباح/مخزون).
- [ ] تقارير العملاء المتأخرين والموردين المتأخرين.
- [ ] تقارير أفضل العملاء/الأصناف/المندوبين.

### المرحلة 8: التحقق والجودة (PENDING)
- [ ] اختبار سيناريوهات كاملة (بيع/شراء/مرتجع/خصم/دفعات).
- [x] صلاحيات الوصول لكل قسم.
- [x] تحسين الأداء والفهارس الأساسية.

## متطلبات غير وظيفية
- أمان وصلاحيات دقيقة.
- نسخ احتياطي واسترجاع.
- أداء جيد مع بيانات كبيرة.
- سجل تدقيق قابل للبحث.

## أفكار تكامل لاحقة
- إشعارات SMS/WhatsApp/Email.
- تكامل مع أجهزة الباركود والطابعات.
- واجهات API لتطبيق موبايل.

---

## Implementation Summary (2026-01-05)

### Completed Database Layer
- **27 migrations** successfully created and tested
- **24 Eloquent models** with relationships and business logic
- **81 permissions** and **6 roles** (super_admin, admin, branch_manager, sales_rep, accountant, warehouse_keeper)
- **Comprehensive seeder** with default data (branch, warehouse, units, categories, admin user)

### Models Created
| Module | Models |
|--------|--------|
| Core | User, Branch, AuditLog |
| Products | Category, Unit, Product, ProductPrice |
| Parties | Customer, Supplier, Employee, SalesRep |
| Inventory | Warehouse, InventoryLevel, StockMovement |
| Sales | Sale, SaleItem, SaleReturn, SaleReturnItem |
| Purchases | Purchase, PurchaseItem, PurchaseReturn, PurchaseReturnItem |
| Finance | Payment, Expense, ExpenseCategory |

### Enhancements Added
1. **Auditable Trait** - Auto-logging for all model changes (create/update/delete)
2. **Invoice Number Generation** - Auto-increment with prefix (INV-YYYYMM-0001)
3. **Credit Limit Tracking** - Customer credit validation with `canPurchase()` method
4. **Unit Conversion** - Base unit relationships with conversion factors
5. **Multi-Price Tiers** - Product prices with date validity and quantity thresholds
6. **Stock Reservation** - Reserve quantity for pending orders
7. **Overdue Detection** - Automatic payment status updates
8. **Hierarchical Categories** - Parent/child relationships with full path attribute
9. **Polymorphic Payments** - Single payment system for sales and purchases
10. **Expense Approval Workflow** - Pending → Approved → Paid status flow

### Default Admin Credentials
- **Email**: admin@crm.test
- **Password**: password

### Commands to Setup
```bash
composer install
php artisan migrate
php artisan db:seed
```

### Remaining Work (UI Layer)
- Controllers and Routes
- Livewire/Blade views
- PDF/Excel export functionality
- Dashboard widgets
- Report generation
- API endpoints (optional)
