<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartnerTransaction extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'partner_id',
        'transaction_number',
        'type',
        'amount',
        'transaction_date',
        'period',
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

    public const TYPE_WITHDRAWAL = 'withdrawal';
    public const TYPE_PROFIT_SHARE = 'profit_share';
    public const TYPE_INVESTMENT = 'investment';
    public const TYPE_RETURN = 'return';

    public const TYPES = [
        self::TYPE_WITHDRAWAL => 'سحب أرباح',
        self::TYPE_PROFIT_SHARE => 'توزيع أرباح',
        self::TYPE_INVESTMENT => 'إضافة رأس مال',
        self::TYPE_RETURN => 'إرجاع رأس مال',
    ];

    public const PAYMENT_METHODS = [
        'cash' => 'نقدي',
        'bank_transfer' => 'تحويل بنكي',
        'check' => 'شيك',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeWithdrawals($query)
    {
        return $query->where('type', self::TYPE_WITHDRAWAL);
    }

    public function scopeProfitShares($query)
    {
        return $query->where('type', self::TYPE_PROFIT_SHARE);
    }

    public function scopeForPartner($query, int $partnerId)
    {
        return $query->where('partner_id', $partnerId);
    }

    public function scopeForPeriod($query, string $period)
    {
        return $query->where('period', $period);
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
        return in_array($this->type, [self::TYPE_INVESTMENT]);
    }

    public function isDebit(): bool
    {
        return in_array($this->type, [self::TYPE_WITHDRAWAL, self::TYPE_PROFIT_SHARE, self::TYPE_RETURN]);
    }

    public static function generateTransactionNumber(): string
    {
        $prefix = 'PTR-TRX-';
        $lastTransaction = static::withTrashed()->latest('id')->first();
        $nextNumber = $lastTransaction ? $lastTransaction->id + 1 : 1;
        return $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
