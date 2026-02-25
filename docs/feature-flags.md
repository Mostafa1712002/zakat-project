# Feature Flags - دليل التطوير

## كيف يعمل نظام Feature Flags

النظام يعتمد على جدول `features` في قاعدة البيانات مع **caching** لتقليل الـ queries.

### المكونات

```
Feature Model → feature_enabled() Helper → Blade Views / Controllers / Routes
                                         ↘ CheckFeatureMiddleware (Routes)
```

### استخدام في Blade Views

```blade
@if(feature_enabled('grade_system'))
    {{-- يظهر فقط عند تفعيل نظام الفرز --}}
    <th>الفرز</th>
@endif
```

### استخدام في Controllers

```php
if (feature_enabled('auto_cash_payment')) {
    // إنشاء تحصيل تلقائي
}

$grades = feature_enabled('grade_system')
    ? Grade::active()->ordered()->get()
    : collect();
```

### استخدام في Routes (Middleware)

```php
Route::middleware(['feature:customer_target'])->group(function () {
    Route::get('customers/{customer}/withdraw-target', ...);
    Route::post('customers/{customer}/withdraw-target', ...);
});
```

---

## إضافة Feature Flag جديد

### 1. أضف الميزة في FeatureSeeder

```php
// database/seeders/FeatureSeeder.php
[
    'name' => 'my_new_feature',
    'name_ar' => 'الميزة الجديدة',
    'description' => 'Description in English',
    'description_ar' => 'وصف بالعربي',
    'icon' => '🆕',
    'route_name' => null,
    'group' => 'site_features',
    'is_enabled' => false,
    'sort_order' => 9,
],
```

### 2. أضف الـ profile في SiteFeatureSeeder

```php
// database/seeders/SiteFeatureSeeder.php
'rogence' => [
    // ...existing...
    'my_new_feature' => false,
],
'syramik' => [
    // ...existing...
    'my_new_feature' => true,
],
```

### 3. استخدم في الكود

```php
// Controller
if (feature_enabled('my_new_feature')) {
    // logic
}

// Blade
@if(feature_enabled('my_new_feature'))
    <!-- UI -->
@endif

// Route protection
Route::middleware(['feature:my_new_feature'])->group(function () {
    // routes
});
```

### 4. شغل الـ Seeder

```bash
php artisan db:seed --class=FeatureSeeder --force
php artisan db:seed --class=SiteFeatureSeeder --force
```

---

## تفعيل/تعطيل من لوحة التحكم

الميزات يمكن تفعيلها/تعطيلها من **صفحة الإعدادات → الميزات** في لوحة التحكم.
التغيير يأخذ مفعول فوري بعد مسح الكاش.

---

## أنواع العملاء الديناميكية

```php
// الاستخدام
$types = customer_item_types();
// يرجع: [['value' => 'fridge', 'label' => 'تلاجة'], ...]

// في Blade
@foreach(customer_item_types() as $type)
    <option value="{{ $type['value'] }}">{{ $type['label'] }}</option>
@endforeach

// في Validation
$validTypes = collect(customer_item_types())->pluck('value')->toArray();
'item_type' => 'required|in:' . implode(',', $validTypes),
```

يتم تخزينها في جدول `settings` كـ JSON ويمكن تعديلها من إعدادات الشركة.

---

## الـ Caching

| Cache Key | المدة | الوصف |
|-----------|-------|-------|
| `feature_{name}` | 3600s | حالة كل ميزة |
| `customer_item_types` | 3600s | أنواع العملاء |
| `sidebar_settings` | 3600s | إعدادات الشريط الجانبي |
| `company_settings` | 3600s | إعدادات الشركة |

مسح الكاش بعد أي تغيير:
```bash
php artisan cache:clear
```

---

## قواعد مهمة

1. **لا تعتمد على القيمة الافتراضية**: دائماً أضف الميزة في `FeatureSeeder` أولاً
2. **nullable columns**: أي عمود مرتبط بـ feature flag لازم يكون `nullable` في الـ migration
3. **Backward compatible**: الكود لازم يشتغل سواء الميزة مفعلة أو معطلة
4. **Test both states**: اختبر الكود مع الميزة ON و OFF
5. **لا تنسى الـ profile**: لما تضيف feature جديد، حدد حالته لكل منصة في `SiteFeatureSeeder`
