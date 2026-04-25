<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'name',
        'item_type',
        'code',
        'email',
        'phone',
        'mobile',
        'address',
        'city',
        'tax_number',
        'type',
        'price_tier',
        'credit_limit',
        'current_balance',
        'payment_terms_days',
        'fixed_discount',
        'target_amount',
        'target_discount_percentage',
        'target_paid_amount',
        'branch_id',
        'sales_rep_id',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'fixed_discount' => 'decimal:2',
        'target_amount' => 'decimal:2',
        'target_discount_percentage' => 'decimal:2',
        'target_paid_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'payable_id')
            ->where('payable_type', self::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOverCreditLimit($query)
    {
        return $query->whereRaw('current_balance > credit_limit')
            ->where('credit_limit', '>', 0);
    }

    public function scopeWithOutstandingBalance($query)
    {
        return $query->where('current_balance', '>', 0);
    }

    public function getAvailableCreditAttribute(): float
    {
        if ($this->credit_limit <= 0) {
            return 0;
        }

        return max(0, $this->credit_limit - $this->current_balance);
    }

    public function isOverCreditLimit(): bool
    {
        if ($this->credit_limit <= 0) {
            return false;
        }

        return $this->current_balance > $this->credit_limit;
    }

    public function canPurchase(float $amount): bool
    {
        if ($this->credit_limit <= 0) {
            return true;
        }

        return ($this->current_balance + $amount) <= $this->credit_limit;
    }

    public function updateBalance(float $amount): void
    {
        $this->increment('current_balance', $amount);
    }

    /**
     * إعادة حساب الرصيد المستحق من الفواتير الفعلية
     * NOTE: stub during Phase 1 cleanup; will be wired to invoices in Phase 5.
     */
    public function recalculateBalance(): void
    {
        $this->current_balance = 0;
        $this->save();
    }

    /**
     * إجمالي مشتريات العميل
     * NOTE: stub during Phase 1 cleanup; will be wired to invoices in Phase 5.
     */
    public function getTotalPurchasesAttribute(): float
    {
        return 0.0;
    }

    /**
     * هل حقق العميل التارجت؟
     */
    public function hasAchievedTarget(): bool
    {
        if ($this->target_amount <= 0) {
            return false;
        }
        return $this->total_purchases >= $this->target_amount;
    }

    /**
     * نسبة تحقيق التارجت
     */
    public function getTargetAchievementPercentageAttribute(): float
    {
        if ($this->target_amount <= 0) {
            return 0;
        }
        return min(100, ($this->total_purchases / $this->target_amount) * 100);
    }

    /**
     * المبلغ المتبقي لتحقيق التارجت
     */
    public function getRemainingToTargetAttribute(): float
    {
        if ($this->target_amount <= 0) {
            return 0;
        }
        return max(0, $this->target_amount - $this->total_purchases);
    }

    /**
     * قيمة خصم التارجت المستحقة
     */
    public function getTargetDiscountAmountAttribute(): float
    {
        if (!$this->hasAchievedTarget()) {
            return 0;
        }
        return ($this->total_purchases * $this->target_discount_percentage) / 100;
    }

    /**
     * قيمة خصم التارجت القابلة للسحب (بعد خصم ما تم صرفه)
     */
    public function getWithdrawableTargetAmountAttribute(): float
    {
        return max(0, $this->target_discount_amount - $this->target_paid_amount);
    }

    /**
     * تسجيل سحب تارجت
     */
    public function recordTargetWithdrawal(float $amount): void
    {
        $this->increment('target_paid_amount', $amount);
    }
}
