<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait SalesRepScope
 *
 * يوفر نطاق للتصفية حسب المندوب
 * المدراء والأدمن يرون كل البيانات
 * المندوبين يرون بياناتهم فقط
 */
trait SalesRepScope
{
    /**
     * نطاق للتصفية حسب المندوب
     *
     * @param Builder $query
     * @param int|null $salesRepId معرف المندوب (اختياري - إذا لم يُحدد يستخدم المندوب الحالي)
     * @return Builder
     */
    public function scopeForSalesRep(Builder $query, ?int $salesRepId = null): Builder
    {
        $user = auth()->user();

        // في حالة عدم وجود مستخدم مسجل الدخول
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        // السوبر أدمن والأدمن ومدير الفرع يرون كل البيانات
        if ($user->isSuperAdmin() || $user->hasRole(['admin', 'branch_manager', 'accountant'])) {
            return $query;
        }

        // الحصول على معرف المندوب
        $salesRepId = $salesRepId ?? $user->salesRep?->id;

        // إذا كان المستخدم مندوب مبيعات
        if ($salesRepId) {
            return $query->where($this->getSalesRepColumn(), $salesRepId);
        }

        // في حالة عدم وجود صلاحية - لا يرى شيء
        return $query->whereRaw('1 = 0');
    }

    /**
     * الحصول على اسم العمود للمندوب
     * يمكن تخصيصه في الموديل إذا كان اسم العمود مختلف
     *
     * @return string
     */
    protected function getSalesRepColumn(): string
    {
        return property_exists($this, 'salesRepColumn')
            ? $this->salesRepColumn
            : 'sales_rep_id';
    }

    /**
     * نطاق للتصفية حسب المندوب مع تضمين البيانات الغير مرتبطة بمندوب
     *
     * @param Builder $query
     * @param int|null $salesRepId
     * @return Builder
     */
    public function scopeForSalesRepOrNull(Builder $query, ?int $salesRepId = null): Builder
    {
        $user = auth()->user();

        // في حالة عدم وجود مستخدم مسجل الدخول
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        // السوبر أدمن والأدمن ومدير الفرع يرون كل البيانات
        if ($user->isSuperAdmin() || $user->hasRole(['admin', 'branch_manager', 'accountant'])) {
            return $query;
        }

        // الحصول على معرف المندوب
        $salesRepId = $salesRepId ?? $user->salesRep?->id;

        // إذا كان المستخدم مندوب مبيعات
        if ($salesRepId) {
            $column = $this->getSalesRepColumn();
            return $query->where(function ($q) use ($column, $salesRepId) {
                $q->where($column, $salesRepId)
                  ->orWhereNull($column);
            });
        }

        // في حالة عدم وجود صلاحية - لا يرى شيء
        return $query->whereRaw('1 = 0');
    }

    /**
     * التحقق من أن المستخدم الحالي يمكنه الوصول لهذا السجل
     *
     * @return bool
     */
    public function canCurrentUserAccess(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        // السوبر أدمن والأدمن يرون كل شيء
        if ($user->isSuperAdmin() || $user->hasRole(['admin', 'branch_manager', 'accountant'])) {
            return true;
        }

        // المندوب يرى بياناته فقط
        $salesRepId = $user->salesRep?->id;
        $column = $this->getSalesRepColumn();

        return $salesRepId && (int) $this->{$column} === (int) $salesRepId;
    }
}
