# المنصات والأنظمة

## نظرة عامة

النظام يعمل بكود موحد (Unified Codebase) مع **Feature Flags** للتحكم في الميزات المفعلة لكل منصة.
كل منصة لها **برنش خاص** و `SITE_PROFILE` في ملف `.env` يحدد الميزات المفعلة تلقائياً.

---

## الحالة الحالية (Live State)

> **آخر تحديث**: 2026-02-25

| البند | Rogence | Syramik | Demo Sibakuh |
|-------|---------|---------|--------------|
| **البرنش على السيرفر** | `rogence` | `syramik` | `demo-sibakuh` |
| **آخر commit** | `a0ab450` | `2a61fa1` | `f7d0477` |
| **SITE_PROFILE** | غير مضبوط | غير مضبوط | غير مضبوط |
| **Features table** | 0 rows | 0 rows | 28 rows (مفعلة) |
| **SiteFeatureSeeder** | غير موجود | غير موجود | غير موجود |

### ملاحظات عن الحالة الحالية

- **`main`** هو الكود الأساسي الموحد فيه كل الميزات (لا يُنشر مباشرة).
- **Rogence**: يشتغل على برنش `rogence` الخاص بيه.
- **Syramik**: يشتغل على برنش `syramik`.
- **Demo Sibakuh**: يشتغل على برنش `demo-sibakuh` وعنده features table مفعلة (28 feature) بما فيها `grades`, `tile_area`, `pallet_option`.

---

## المنصات

### 1. Rogence (روجينس)

| البند | القيمة |
|-------|--------|
| **الاسم** | Rogence |
| **البرنش** | `rogence` |
| **الدومين** | `rogence.newaves-systems.com` |
| **المسار على السيرفر** | `/var/www/rogence.newaves-systems.com` |
| **SITE_PROFILE** | `rogence` |
| **النشاط** | تجارة عامة |
| **أنواع العملاء** | تلاجة (fridge) / خاص (special) |

### 2. Syramik (سيراميك)

| البند | القيمة |
|-------|--------|
| **الاسم** | Syramik |
| **البرنش** | `syramik` |
| **الدومين** | `syramik.newaves-systems.com` |
| **المسار على السيرفر** | `/var/www/syramik.newaves-systems.com` |
| **SITE_PROFILE** | `syramik` |
| **النشاط** | تجارة سيراميك وبلاط |
| **أنواع العملاء** | تاجر (trader) / عادي (regular) |

### 3. Demo Sibakuh (ديمو سيباكوه)

| البند | القيمة |
|-------|--------|
| **الاسم** | Demo Sibakuh |
| **البرنش** | `demo-sibakuh` |
| **الدومين** | `demo-sibakuh.newaves-systems.com` |
| **المسار على السيرفر** | `/var/www/demo-sibakuh.newaves-systems.com` |
| **SITE_PROFILE** | `demo-sibakuh` |
| **النشاط** | ديمو / عرض |
| **الميزات الخاصة** | نفس ميزات Syramik (tile_area, grades, per_item_discount, auto_cash, color_palette, invoice_customization, simple_numbers: ON) |

---

## السيرفر

| البند | القيمة |
|-------|--------|
| **العنوان** | `root@rogence.newaves-systems.com` |
| **النشر** | `./deploy.sh [rogence\|syramik\|demo-sibakuh]` |

---

## البرنشات

| البرنش | المنصة | الوصف |
|--------|--------|-------|
| `main` | - | الكود الأساسي الموحد - فيه كل الميزات (للتطوير) |
| `rogence` | Rogence | البرنش الخاص بمنصة روجينس |
| `syramik` | Syramik | البرنش الخاص بمنصة سيراميك |
| `demo-sibakuh` | Demo Sibakuh | البرنش الخاص بالديمو |
| `features/odoo` | - | برنش تجريبي |

> **`main`** هو برنش التطوير الأساسي وفيه كل الميزات. كل منصة ليها برنش خاص بيسحب من `main` الحاجات اللي محتاجها.

---

## Feature Flags حسب المنصة

### الميزات الأساسية (مشتركة - مفعلة للكل)

| الميزة | الوصف |
|--------|-------|
| `sales` | المبيعات - إدارة عمليات البيع والفواتير |
| `invoices` | الفواتير - إدارة الفواتير وطباعتها |
| `purchases` | المشتريات - إدارة المشتريات والموردين |
| `products` | الأصناف - إدارة الأصناف والمنتجات |
| `categories` | الأقسام - إدارة أقسام المنتجات |
| `warehouses` | المخازن - إدارة المخازن والمستودعات |
| `customers` | العملاء - إدارة بيانات العملاء |
| `suppliers` | الموردين - إدارة بيانات الموردين |
| `employees` | الموظفين - إدارة بيانات الموظفين |
| `sales_reps` | المندوبين - إدارة مندوبي المبيعات |
| `sales_rep_dashboard` | لوحة تحكم المندوب |
| `employee_dashboard` | لوحة تحكم الموظف |
| `expenses` | المصروفات - إدارة المصروفات والنفقات |
| `payments` | التحصيلات والمدفوعات |
| `payment_instapay` | الدفع عبر انستا باي |
| `payment_vodafone_cash` | الدفع عبر فودافون كاش |
| `reports` | التقارير والإحصائيات |
| `report_profits` | تقرير الأرباح |
| `report_inventory` | تقرير المخزون |
| `report_sales_reps` | تقارير المندوبين |
| `sales_rep_data_isolation` | عزل بيانات المندوب |
| `sales_rep_multi_warehouse` | تعدد مخازن المندوب |
| `sales_rep_collect_payments` | تحصيل المندوب |
| `settings` | الإعدادات |
| `users` | المستخدمين |

### ميزات خاصة بالموقع (Site Features)

| الميزة | الوصف | Rogence | Syramik | Demo Sibakuh |
|--------|-------|:-------:|:-------:|:------------:|
| `customer_target` | نظام تارجت العملاء | ON | OFF | OFF |
| `tile_area_tracking` | تتبع المساحة/البلاط | OFF | ON | ON |
| `grade_system` | نظام الفرز | OFF | ON | ON |
| `per_item_discount` | خصم على مستوى الصنف | OFF | ON | ON |
| `auto_cash_payment` | تحصيل نقدي تلقائي | OFF | ON | ON |
| `color_palette` | ألوان مخصصة | OFF | ON | ON |
| `invoice_customization` | تخصيص الفواتير | OFF | ON | ON |
| `simple_invoice_numbers` | أرقام فواتير تسلسلية | OFF | ON | ON |

---

## تفاصيل كل ميزة خاصة

### `customer_target` - نظام تارجت العملاء
- **مفعل في**: Rogence
- **الوصف**: نظام يحدد تارجت (هدف مبيعات) لكل عميل. عند تحقيق العميل للتارجت يحصل على خصم محدد.
- **الحقول**: `target_amount`, `target_discount_percentage`, `target_paid_amount`
- **الشاشات**: صفحة العملاء (progress bar)، سحب التارجت
- **المسارات المحمية**: `customers/{id}/withdraw-target`

### `tile_area_tracking` - تتبع المساحة
- **مفعل في**: Syramik
- **الوصف**: حساب المساحة الإجمالية لكل صنف في الفاتورة بناءً على `area_per_unit` للمنتج.
- **الحقول**: `products.area_per_unit`, `products.tiles_per_box`, `products.tile_area`, `sale_items.total_area`
- **الشاشات**: فاتورة المبيعات (إنشاء/تعديل/عرض/PDF)، إنشاء/تعديل المنتج

### `grade_system` - نظام الفرز
- **مفعل في**: Syramik
- **الوصف**: تصنيف جودة الأصناف (أول، تاني، ثالث، إلخ). يظهر dropdown في فاتورة المبيعات لاختيار الفرز لكل صنف.
- **الحقول**: جدول `grades` (name, name_ar, code)، `sale_items.grade_id`
- **الشاشات**: فاتورة المبيعات (إنشاء/تعديل/عرض/PDF)

### `per_item_discount` - خصم على مستوى الصنف
- **مفعل في**: Syramik
- **الوصف**: إمكانية إضافة خصم مبلغ لكل صنف على حدة في الفاتورة (بالإضافة للخصم العام).
- **الحقول**: `sale_items.discount_amount` (يظهر كعمود مرئي)
- **الشاشات**: فاتورة المبيعات (إنشاء/تعديل/عرض/PDF)

### `auto_cash_payment` - تحصيل نقدي تلقائي
- **مفعل في**: Syramik
- **الوصف**: عند إنشاء فاتورة نقدية يتم إنشاء تحصيل تلقائي بالمبلغ الكامل.
- **الشاشات**: SaleController::store()

### `color_palette` - ألوان مخصصة
- **مفعل في**: Syramik
- **الوصف**: تخصيص ألوان الموقع (اللون الأساسي، الداكن، الفاتح) من صفحة إعدادات الشركة.
- **الحقول**: `color_primary`, `color_primary_dark`, `color_primary_light` في settings
- **الشاشات**: إعدادات الشركة (color pickers)، Layout CSS variables
- **القيم الافتراضية**: سماوي (`#0891b2`, `#0e7490`, `#06b6d4`)

### `invoice_customization` - تخصيص الفواتير
- **مفعل في**: Syramik
- **الوصف**: إضافة ملاحظة وفوتر للفاتورة المطبوعة، وعرض رصيد العميل.
- **الحقول**: `invoice_note`, `invoice_footer`, `show_customer_balance` في settings
- **الشاشات**: إعدادات الشركة، فاتورة PDF

### `simple_invoice_numbers` - أرقام فواتير تسلسلية
- **مفعل في**: Syramik
- **الوصف**: أرقام فواتير بسيطة (1, 2, 3, ...) بدلاً من الصيغة `YYYYMM0001`.
- **الشاشات**: Sale::generateInvoiceNumber()

---

## النشر (Deployment)

### الأوامر

```bash
# نشر على منصة واحدة
./deploy.sh rogence
./deploy.sh syramik
./deploy.sh demo-sibakuh

# نشر على كل المنصات دفعة واحدة
./deploy.sh all

# اكتشاف تلقائي من البرنش الحالي
git checkout syramik && ./deploy.sh
```

> **ملاحظة**: `main` ليس له deploy target. لازم تحدد المنصة أو تكون على برنش المنصة.

### آلية النشر (deploy.sh)

السكريبت بيعمل الخطوات دي تلقائياً:

1. `git push origin <branch>` — رفع التغييرات للـ remote
2. `git fetch origin <branch>` — جلب آخر التحديثات على السيرفر
3. `git reset --hard origin/<branch>` — مزامنة السيرفر مع الـ remote (يتجنب مشكلة divergent branches)
4. `php artisan migrate --force` — تشغيل الـ migrations
5. `php artisan db:seed --class=FeatureSeeder --force` — تحديث الـ features
6. مسح الكاش (cache, config, view, route)

### لماذا `fetch + reset --hard` بدل `pull`؟

لو حصل أي تعديل يدوي على السيرفر (حتى لو بسيط) الـ `git pull` بيفشل بخطأ:
```
fatal: Need to specify how to reconcile divergent branches.
```
استخدام `fetch + reset --hard` بيتجاوز المشكلة دي لأنه بيفرض الكود اللي على الـ remote بدون محاولة دمج.

### هيكل البرنشات

```
main (الكود الأساسي - كل الميزات - لا يُنشر مباشرة)
 ├── rogence      → rogence.newaves-systems.com
 ├── syramik      → syramik.newaves-systems.com
 └── demo-sibakuh → demo-sibakuh.newaves-systems.com
```

**قاعدة مهمة**: كل منصة ليها برنش مستقل. **لا تعمل merge بين البرنشات**. لنقل إصلاح لكل المنصات:
```bash
# 1. اعمل الإصلاح على main
git checkout main
# ... عدّل الكود ...
git commit -m "Fix something"

# 2. انقل الإصلاح لكل منصة
git checkout rogence && git cherry-pick <commit-hash>
git checkout syramik && git cherry-pick <commit-hash>
git checkout demo-sibakuh && git cherry-pick <commit-hash>

# 3. انشر الكل
git checkout main && ./deploy.sh all
```

### أول نشر بعد الانتقال (مرة واحدة لكل سيرفر):
1. أضف `SITE_PROFILE=<name>` في ملف `.env` على السيرفر
2. شغل `php artisan db:seed --class=FeatureSeeder --force`
3. شغل `php artisan db:seed --class=SiteFeatureSeeder --force`

### إعداد منصة جديدة:
1. أنشئ برنش جديد: `git checkout -b <branch-name>`
2. أضف الموقع في `deploy.sh` (arrays: `SITES` و `BRANCH_TO_SITE`)
3. أضف `SITE_PROFILE=<name>` في ملف `.env` على السيرفر
4. أضف الـ profile الجديد في `SiteFeatureSeeder.php`
5. شغل `php artisan db:seed --class=FeatureSeeder --force`
6. شغل `php artisan db:seed --class=SiteFeatureSeeder --force`

---

## أنواع العملاء (Customer Item Types)

أنواع العملاء ديناميكية ويتم تحديدها من **إعدادات الشركة** لكل موقع.

| المنصة | الأنواع الافتراضية |
|--------|-------------------|
| Rogence | تلاجة (fridge)، خاص (special) |
| Syramik | تاجر (trader)، عادي (regular) |
| Demo Sibakuh | حسب الإعدادات |

يتم تخزينها كـ JSON في جدول `settings` بالمفتاح `customer_item_types`.
يمكن تعديلها من صفحة إعدادات الشركة (قسم "أنواع العملاء").

---

## الملفات المهمة

| الملف | الوظيفة |
|-------|---------|
| `app/Helpers/FeatureHelper.php` | دوال `feature_enabled()`, `customer_item_types()` |
| `app/Models/Feature.php` | نموذج الميزة مع caching |
| `app/Http/Middleware/CheckFeatureMiddleware.php` | Middleware لحماية المسارات |
| `database/seeders/FeatureSeeder.php` | كل الميزات وبياناتها |
| `database/seeders/SiteFeatureSeeder.php` | تفعيل/تعطيل الميزات حسب الـ profile |
| `deploy.sh` | سكريبت النشر الموحد |
