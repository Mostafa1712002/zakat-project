<?php

namespace App\Domain\Sales\Models;

use App\Domain\Catalog\Models\Service;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * QuoteItem — line item under a Quote.
 *
 * The `saving` hook auto-computes tax_amount and total so callers (forms,
 * calculator, actions) only need to set quantity / unit_price / discount /
 * tax_rate. Quote-level totals are summed by QuoteCalculator.
 *
 * Reference: .kiro/specs/ammrk-platform/design.md (Sales schema)
 *            .kiro/specs/ammrk-platform/tasks.md (Task 5.2)
 */
class QuoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_id',
        'service_id',
        'description',
        'quantity',
        'unit_price',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'total',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (QuoteItem $item) {
            $qty = (float) ($item->quantity ?? 1);
            $unit = (float) ($item->unit_price ?? 0);
            $discount = (float) ($item->discount_amount ?? 0);
            $rate = (float) ($item->tax_rate ?? 15);

            $taxable = max(0, ($qty * $unit) - $discount);
            $tax = round($taxable * ($rate / 100), 2);
            $total = round($taxable + $tax, 2);

            $item->tax_amount = $tax;
            $item->total = $total;
        });
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
