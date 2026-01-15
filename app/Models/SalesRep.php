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

    protected $fillable = [
        'user_id',
        'employee_id',
        'name',
        'code',
        'phone',
        'email',
        'regions',
        'commission_rate',
        'commission_type',
        'sales_target',
        'branch_id',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'regions' => 'array',
        'commission_rate' => 'decimal:2',
        'sales_target' => 'decimal:2',
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
