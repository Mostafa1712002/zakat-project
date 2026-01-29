<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesRep extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    // أنواع المندوبين
    const TYPE_FRIDGE = 'fridge';
    const TYPE_SPECIAL = 'special';

    const TYPES = [
        self::TYPE_FRIDGE => 'تلاجة',
        self::TYPE_SPECIAL => 'خاص',
    ];

    protected $fillable = [
        'user_id',
        'employee_id',
        'name',
        'code',
        'type',
        'phone',
        'email',
        'regions',
        'commission_rate',
        'commission_type',
        'sales_target',
        'treasury_balance',
        'branch_id',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'regions' => 'array',
        'commission_rate' => 'decimal:2',
        'sales_target' => 'decimal:2',
        'treasury_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * الحصول على اسم نوع المندوب
     */
    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function scopeInBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeInRegion($query, string $region)
    {
        return $query->whereJsonContains('regions', $region);
    }

    public function calculateCommission(float $saleAmount): float
    {
        if ($this->commission_type === 'percentage') {
            return $saleAmount * ($this->commission_rate / 100);
        }

        return $this->commission_rate;
    }

    public function getTotalSalesAttribute(): float
    {
        return $this->sales()->sum('total_amount');
    }

    public function getTargetAchievementAttribute(): float
    {
        if ($this->sales_target <= 0) {
            return 0;
        }

        return ($this->total_sales / $this->sales_target) * 100;
    }

    public function hasMetTarget(): bool
    {
        return $this->total_sales >= $this->sales_target;
    }

    /**
     * حساب مبيعات الشهر
     */
    public function getMonthlySalesAmount(?int $year = null, ?int $month = null): float
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;
        $start = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $end = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

        return $this->sales()
            ->whereBetween('invoice_date', [$start, $end])
            ->sum('total_amount');
    }

    /**
     * التحقق من تحقيق الهدف الشهري
     */
    public function hasMetMonthlyTarget(?int $year = null, ?int $month = null): bool
    {
        if ($this->sales_target <= 0) {
            return false;
        }
        return $this->getMonthlySalesAmount($year, $month) >= $this->sales_target;
    }

    /**
     * نسبة تحقيق الهدف الشهري
     */
    public function getMonthlyTargetAchievement(?int $year = null, ?int $month = null): float
    {
        if ($this->sales_target <= 0) {
            return 0;
        }
        return ($this->getMonthlySalesAmount($year, $month) / $this->sales_target) * 100;
    }

    /**
     * حساب عمولة الشهر (تُحسب فقط عند تحقيق الهدف)
     */
    public function calculateMonthlyCommission(?int $year = null, ?int $month = null): float
    {
        $monthlySales = $this->getMonthlySalesAmount($year, $month);

        // العمولة تُحسب فقط إذا تم تحقيق الهدف
        if ($this->sales_target > 0 && $monthlySales < $this->sales_target) {
            return 0;
        }

        if ($this->commission_type === 'percentage') {
            return $monthlySales * ($this->commission_rate / 100);
        }

        // عمولة ثابتة عند تحقيق الهدف
        return $this->commission_rate;
    }

    /**
     * الحصول على تفاصيل العمولة الشهرية
     */
    public function getMonthlyCommissionDetails(?int $year = null, ?int $month = null): array
    {
        $monthlySales = $this->getMonthlySalesAmount($year, $month);
        $hasMetTarget = $this->hasMetMonthlyTarget($year, $month);
        $targetAchievement = $this->getMonthlyTargetAchievement($year, $month);
        $commission = $this->calculateMonthlyCommission($year, $month);

        return [
            'monthly_sales' => $monthlySales,
            'sales_target' => $this->sales_target,
            'target_achievement' => $targetAchievement,
            'has_met_target' => $hasMetTarget,
            'commission_rate' => $this->commission_rate,
            'commission_type' => $this->commission_type,
            'commission_earned' => $commission,
            'remaining_to_target' => max(0, $this->sales_target - $monthlySales),
        ];
    }

    /**
     * المخازن المرتبطة بالمندوب
     */
    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'sales_rep_warehouse')
            ->withPivot('is_default')
            ->withTimestamps();
    }

    /**
     * الحصول على المخزن الافتراضي للمندوب
     */
    public function getDefaultWarehouseAttribute(): ?Warehouse
    {
        return $this->warehouses()->wherePivot('is_default', true)->first();
    }

    /**
     * التحصيلات التي قام بها المندوب
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * معاملات خزينة المندوب
     */
    public function treasuryTransactions(): HasMany
    {
        return $this->hasMany(SalesRepTransaction::class);
    }

    /**
     * مصروفات المندوب
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * إيداع مبلغ في الخزينة
     */
    public function deposit(float $amount, ?string $description = null, ?string $referenceType = null, ?int $referenceId = null): SalesRepTransaction
    {
        $newBalance = $this->treasury_balance + $amount;

        $transaction = $this->treasuryTransactions()->create([
            'type' => SalesRepTransaction::TYPE_DEPOSIT,
            'amount' => $amount,
            'balance_after' => $newBalance,
            'description' => $description ?? 'إيداع في الخزينة',
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'created_by' => auth()->id(),
        ]);

        $this->update(['treasury_balance' => $newBalance]);

        return $transaction;
    }

    /**
     * سحب مبلغ من الخزينة
     */
    public function withdraw(float $amount, ?string $description = null, ?string $referenceType = null, ?int $referenceId = null): SalesRepTransaction
    {
        $newBalance = $this->treasury_balance - $amount;

        $transaction = $this->treasuryTransactions()->create([
            'type' => SalesRepTransaction::TYPE_WITHDRAWAL,
            'amount' => $amount,
            'balance_after' => $newBalance,
            'description' => $description ?? 'سحب من الخزينة',
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'created_by' => auth()->id(),
        ]);

        $this->update(['treasury_balance' => $newBalance]);

        return $transaction;
    }

    /**
     * تسجيل مصروف من الخزينة
     */
    public function recordExpense(float $amount, ?string $description = null, ?int $expenseId = null): SalesRepTransaction
    {
        $newBalance = $this->treasury_balance - $amount;

        $transaction = $this->treasuryTransactions()->create([
            'type' => SalesRepTransaction::TYPE_EXPENSE,
            'amount' => $amount,
            'balance_after' => $newBalance,
            'description' => $description ?? 'مصروف',
            'reference_type' => 'expense',
            'reference_id' => $expenseId,
            'created_by' => auth()->id(),
        ]);

        $this->update(['treasury_balance' => $newBalance]);

        return $transaction;
    }

    /**
     * تسجيل تحصيل في الخزينة
     */
    public function recordCollection(float $amount, ?string $description = null, ?int $paymentId = null): SalesRepTransaction
    {
        $newBalance = $this->treasury_balance + $amount;

        $transaction = $this->treasuryTransactions()->create([
            'type' => SalesRepTransaction::TYPE_COLLECTION,
            'amount' => $amount,
            'balance_after' => $newBalance,
            'description' => $description ?? 'تحصيل من عميل',
            'reference_type' => 'payment',
            'reference_id' => $paymentId,
            'created_by' => auth()->id(),
        ]);

        $this->update(['treasury_balance' => $newBalance]);

        return $transaction;
    }

    /**
     * الحصول على المندوب المرتبط بالمستخدم الحالي
     */
    public static function forUser(?User $user): ?SalesRep
    {
        if (!$user) return null;
        return self::where('user_id', $user->id)->first();
    }

    /**
     * الحصول على المندوب للمستخدم الحالي
     */
    public static function current(): ?SalesRep
    {
        return self::forUser(auth()->user());
    }
}
