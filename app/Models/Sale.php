<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\SalesRepScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes, Auditable, SalesRepScope;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_STATUS_UNPAID = 'unpaid';
    public const PAYMENT_STATUS_PARTIAL = 'partial';
    public const PAYMENT_STATUS_PAID = 'paid';
    public const PAYMENT_STATUS_OVERDUE = 'overdue';

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'branch_id',
        'warehouse_id',
        'sales_rep_id',
        'user_id',
        'invoice_date',
        'due_date',
        'payment_type',
        'status',
        'is_quotation',
        'quotation_number',
        'payment_status',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_amount',
        'shipping_amount',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'payment_method',
        'purchase_order_number',
        'shipping_address',
        'notes',
        'terms',
        'received_by',
        'signature',
    ];

    protected $casts = [
        'is_quotation' => 'boolean',
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function salesRep(): BelongsTo
    {
        return $this->belongsTo(SalesRep::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SaleReturn::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOfPaymentStatus($query, string $status)
    {
        return $query->where('payment_status', $status);
    }

    public function scopeOverdue($query)
    {
        return $query->where('payment_status', '!=', self::PAYMENT_STATUS_PAID)
            ->where('due_date', '<', now());
    }

    public function scopeInBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('invoice_date', [$startDate, $endDate]);
    }

    public function calculateTotals(): void
    {
        // Use sum of items' total (already includes per-item discounts and per-item tax)
        $this->subtotal = $this->items->sum('total');

        if ($this->discount_type === 'percentage') {
            $this->discount_amount = $this->subtotal * ($this->discount_value / 100);
        } else {
            $this->discount_amount = $this->discount_value;
        }

        $this->tax_amount = $this->items->sum('tax_amount');
        $this->total_amount = $this->subtotal - $this->discount_amount + $this->shipping_amount;
        $this->remaining_amount = $this->total_amount - $this->paid_amount;

        $this->updatePaymentStatus();
    }

    public function updatePaymentStatus(): void
    {
        if ($this->paid_amount >= $this->total_amount) {
            $this->payment_status = self::PAYMENT_STATUS_PAID;
        } elseif ($this->paid_amount > 0) {
            $this->payment_status = self::PAYMENT_STATUS_PARTIAL;
        } elseif ($this->due_date && $this->due_date->isPast()) {
            $this->payment_status = self::PAYMENT_STATUS_OVERDUE;
        } else {
            $this->payment_status = self::PAYMENT_STATUS_UNPAID;
        }
    }

    public function addPayment(float $amount): void
    {
        $this->paid_amount += $amount;
        $this->remaining_amount = $this->total_amount - $this->paid_amount;
        if ($this->remaining_amount < 0) {
            $this->remaining_amount = 0;
        }
        $this->updatePaymentStatus();
        $this->save();
    }

    public function getProfitAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return ($item->unit_price - $item->cost_price) * $item->quantity;
        });
    }

    public function isOverdue(): bool
    {
        return $this->payment_status !== self::PAYMENT_STATUS_PAID
            && $this->due_date
            && $this->due_date->isPast();
    }

    public static function generateInvoiceNumber(): string
    {
        // أرقام تسلسلية بسيطة (1, 2, 3, ...)
        if (feature_enabled('simple_invoice_numbers')) {
            $lastSale = self::withTrashed()
                ->orderByRaw('CAST(invoice_number AS UNSIGNED) DESC')
                ->first();

            if ($lastSale && is_numeric($lastSale->invoice_number)) {
                return (string) ((int) $lastSale->invoice_number + 1);
            }

            // Fallback: count all sales + 1
            return (string) (self::withTrashed()->count() + 1);
        }

        // صيغة التاريخ: YYYYMM0001
        $prefix = date('Ym');
        $lastSale = self::withTrashed()
            ->where('invoice_number', 'like', $prefix . '%')
            ->where('invoice_number', 'not like', '%-%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastSale) {
            $lastNumber = (int) substr($lastSale->invoice_number, -4);
            return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        }

        return $prefix . '0001';
    }
}
