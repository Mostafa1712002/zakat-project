<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesRepTransaction extends Model
{
    use HasFactory;

    const TYPE_DEPOSIT = 'deposit';       // إيداع في الخزينة
    const TYPE_WITHDRAWAL = 'withdrawal'; // سحب من الخزينة
    const TYPE_EXPENSE = 'expense';       // مصروف
    const TYPE_COLLECTION = 'collection'; // تحصيل من عميل

    const TYPES = [
        self::TYPE_DEPOSIT => 'إيداع',
        self::TYPE_WITHDRAWAL => 'سحب',
        self::TYPE_EXPENSE => 'مصروف',
        self::TYPE_COLLECTION => 'تحصيل',
    ];

    protected $fillable = [
        'sales_rep_id',
        'type',
        'amount',
        'balance_after',
        'description',
        'reference_type',
        'reference_id',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function salesRep(): BelongsTo
    {
        return $this->belongsTo(SalesRep::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * الحصول على اسم النوع
     */
    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * هل هي معاملة إضافة (تزيد الرصيد)
     */
    public function isCredit(): bool
    {
        return in_array($this->type, [self::TYPE_DEPOSIT, self::TYPE_COLLECTION]);
    }

    /**
     * هل هي معاملة خصم (تنقص الرصيد)
     */
    public function isDebit(): bool
    {
        return in_array($this->type, [self::TYPE_WITHDRAWAL, self::TYPE_EXPENSE]);
    }

    /**
     * الحصول على المرجع (Payment أو Expense)
     */
    public function getReference()
    {
        if (!$this->reference_type || !$this->reference_id) {
            return null;
        }

        return match ($this->reference_type) {
            'payment' => Payment::find($this->reference_id),
            'expense' => Expense::find($this->reference_id),
            default => null,
        };
    }
}
