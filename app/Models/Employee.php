<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'user_id',
        'name',
        'employee_code',
        'email',
        'phone',
        'address',
        'national_id',
        'job_title',
        'department',
        'branch_id',
        'hire_date',
        'termination_date',
        'salary',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'termination_date' => 'date',
        'salary' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function salesRep(): HasOne
    {
        return $this->hasOne(SalesRep::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(EmployeeTransaction::class);
    }

    public function getTotalSalariesPaidAttribute(): float
    {
        return $this->transactions()
            ->where('type', EmployeeTransaction::TYPE_SALARY)
            ->sum('amount');
    }

    public function getTotalAdvancesAttribute(): float
    {
        return $this->transactions()
            ->where('type', EmployeeTransaction::TYPE_ADVANCE)
            ->sum('amount');
    }

    public function getBalanceAttribute(): float
    {
        $credits = $this->transactions()
            ->whereIn('type', [EmployeeTransaction::TYPE_SALARY, EmployeeTransaction::TYPE_BONUS])
            ->sum('amount');
        $debits = $this->transactions()
            ->whereIn('type', [EmployeeTransaction::TYPE_ADVANCE, EmployeeTransaction::TYPE_DEDUCTION])
            ->sum('amount');
        return $credits - $debits;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeInDepartment($query, string $department)
    {
        return $query->where('department', $department);
    }

    public function isTerminated(): bool
    {
        return !is_null($this->termination_date);
    }

    public function getYearsOfServiceAttribute(): float
    {
        if (!$this->hire_date) {
            return 0;
        }

        $endDate = $this->termination_date ?? now();
        return $this->hire_date->diffInYears($endDate);
    }
}
