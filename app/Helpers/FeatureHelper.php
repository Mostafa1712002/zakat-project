<?php

use App\Models\Feature;

if (!function_exists('feature_enabled')) {
    /**
     * التحقق من حالة ميزة معينة
     *
     * @param string $featureName اسم الميزة
     * @return bool
     */
    function feature_enabled(string $featureName): bool
    {
        return Feature::isEnabled($featureName);
    }
}

if (!function_exists('feature_disabled')) {
    /**
     * التحقق من أن الميزة معطلة
     *
     * @param string $featureName اسم الميزة
     * @return bool
     */
    function feature_disabled(string $featureName): bool
    {
        return !Feature::isEnabled($featureName);
    }
}

if (!function_exists('is_super_admin')) {
    /**
     * التحقق من أن المستخدم الحالي هو مسؤول أعلى
     *
     * @return bool
     */
    function is_super_admin(): bool
    {
        return auth()->check() && auth()->user()->isSuperAdmin();
    }
}

if (!function_exists('feature_name')) {
    /**
     * الحصول على اسم الميزة بالعربية
     *
     * @param string $featureName اسم الميزة
     * @param string $default الاسم الافتراضي
     * @return string
     */
    function feature_name(string $featureName, string $default = ''): string
    {
        $feature = \Illuminate\Support\Facades\Cache::remember("feature_data_{$featureName}", 3600, function () use ($featureName) {
            return Feature::where('name', $featureName)->first();
        });
        return $feature?->name_ar ?? $default;
    }
}

if (!function_exists('customer_item_types')) {
    /**
     * الحصول على أنواع العملاء من الإعدادات
     * كل موقع يحدد الأنواع بتاعته (rogence: fridge/special — syramik: trader/regular)
     *
     * @return array [['value' => 'fridge', 'label' => 'تلاجة'], ...]
     */
    function customer_item_types(): array
    {
        return \Illuminate\Support\Facades\Cache::remember('customer_item_types', 3600, function () {
            try {
                $json = \DB::table('settings')->where('key', 'customer_item_types')->value('value');
                if ($json) {
                    $types = json_decode($json, true);
                    if (is_array($types) && !empty($types)) {
                        return $types;
                    }
                }
            } catch (\Exception $e) {}

            // Default: rogence types
            return [
                ['value' => 'fridge', 'label' => 'تلاجة'],
                ['value' => 'special', 'label' => 'خاص'],
            ];
        });
    }
}

if (!function_exists('feature_icon')) {
    /**
     * الحصول على أيقونة الميزة
     *
     * @param string $featureName اسم الميزة
     * @param string $default الأيقونة الافتراضية
     * @return string
     */
    function feature_icon(string $featureName, string $default = '⚙️'): string
    {
        $feature = \Illuminate\Support\Facades\Cache::remember("feature_data_{$featureName}", 3600, function () use ($featureName) {
            return Feature::where('name', $featureName)->first();
        });
        return $feature?->icon ?? $default;
    }
}
