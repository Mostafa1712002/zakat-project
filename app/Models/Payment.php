<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\SalesRepScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes, Auditable, SalesRepScope;

    public const TYPE_RECEIVED = 'received';
    public const TYPE_PAID = 'paid';

    public const METHOD_CASH = 'cash';
    public const METHOD_BANK_TRANSFER = 'bank_transfer';
    public const METHOD_CHECK = 'check';
    public const METHOD_CARD = 'card';
    public const METHOD_OTHER = 'other';

    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_BOUNCED = 'bounced';

    protected $fillable = [
        'payment_number',
        'payable_type',
        'payable_id',
        'type',
        'amount',
        'method',
        'payment_date',
        'reference_number',
        'check_number',
        'check_date',
        'bank_name',
        'bank_account',
        'branch_id',
        'user_id',
        'sales_rep_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'check_date' => 'date',
    ];

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * المندوب الذي قام بالتحصيل
     */
    public function salesRep(): BelongsTo
    {
        return $this->belongsTo(SalesRep::class);
    }

    public function scopeReceived($query)
    {
        return $query->where('type', self::TYPE_RECEIVED);
    }

    public function scopePaid($query)
    {
        return $query->where('type', self::TYPE_PAID);
    }

    public function scopeOfMethod($query, string $method)
    {
        return $query->where('method', $method);
    }

    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    public function scopeInBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function isReceived(): bool
    {
        return $this->type === self::TYPE_RECEIVED;
    }

    public function isPaid(): bool
    {
        return $this->type === self::TYPE_PAID;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isBounced(): bool
    {
        return $this->status === self::STATUS_BOUNCED;
    }

    public function complete(): void
    {
        $this->status = self::STATUS_COMPLETED;
        $this->save();

        if ($this->payable && method_exists($this->payable, 'addPayment')) {
            $this->payable->addPayment($this->amount);
        }
    }

    public function cancel(): void
    {
        $this->status = self::STATUS_CANCELLED;
        $this->save();
    }

    public function markAsBounced(): void
    {
        $this->status = self::STATUS_BOUNCED;
        $this->save();

        if ($this->payable && method_exists($this->payable, 'addPayment')) {
            $this->payable->addPayment(-$this->amount);
        }
    }

    public static function generatePaymentNumber(string $type): string
    {
        $prefix = ($type === self::TYPE_RECEIVED ? 'RCV' : 'PAY') . '-' . date('Ym');
        $last = self::where('payment_number', 'like', $prefix . '%')
            ->orderBy('payment_number', 'desc')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->payment_number, -4);
            return $prefix . '-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        }

        return $prefix . '-0001';
    }
}
