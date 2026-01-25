<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeTransaction extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'employee_id',
        'transaction_number',
        'type',
        'amount',
        'transaction_date',
        'month_year',
        'payment_method',
        'reference_number',
        'description',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public const TYPE_SALARY = 'salary';
    public const TYPE_ADVANCE = 'advance';
    public const TYPE_BONUS = 'bonus';
    public const TYPE_DEDUCTION = 'deduction';

    public const TYPES = [
        self::TYPE_SALARY => 'مرتب',
        self::TYPE_ADVANCE => 'سلفة',
        self::TYPE_BONUS => 'مكافأة',
        self::TYPE_DEDUCTION => 'خصم',
    ];

    public const PAYMENT_METHODS = [
        'cash' => 'نقدي',
        'bank_transfer' => 'تحويل بنكي',
        'check' => 'شيك',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeSalaries($query)
    {
        return $query->where('type', self::TYPE_SALARY);
    }

    public function scopeAdvances($query)
    {
        return $query->where('type', self::TYPE_ADVANCE);
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForMonth($query, string $monthYear)
    {
        return $query->where('month_year', $monthYear);
    }

    public function scopeInDateRange($query, $from, $to)
    {
        if ($from) {
            $query->whereDate('transaction_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('transaction_date', '<=', $to);
        }
        return $query;
    }

    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getPaymentMethodNameAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }

    public function isCredit(): bool
    {
        return in_array($this->type, [self::TYPE_SALARY, self::TYPE_BONUS]);
    }

    public function isDebit(): bool
    {
        return in_array($this->type, [self::TYPE_ADVANCE, self::TYPE_DEDUCTION]);
    }

    public static function generateTransactionNumber(): string
    {
        $prefix = 'EMP-TRX-';
        $lastTransaction = static::withTrashed()->latest('id')->first();
        $nextNumber = $lastTransaction ? $lastTransaction->id + 1 : 1;
        return $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
