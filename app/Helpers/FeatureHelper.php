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
