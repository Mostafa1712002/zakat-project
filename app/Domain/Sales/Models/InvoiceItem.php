<?php

namespace App\Domain\Sales\Models;

use App\Domain\Catalog\Models\Service;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * InvoiceItem — line item under an Invoice.
 *
 * Boot saving hook auto-computes tax_amount + total (mirrors QuoteItem).
 *
 * Exposes `subtotal` and `product_name` accessors so the legacy
 * ZatcaXmlService (Sale-shaped) can iterate over `$invoice->items` without
 * modification.
 *
 * Reference: .kiro/specs/ammrk-platform/design.md (Sales schema — invoice_items)
 *            .kiro/specs/ammrk-platform/tasks.md  (Task 5.5)
 */
class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
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
        static::saving(function (InvoiceItem $item) {
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

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** Pre-discount, pre-tax extension (qty * unit_price). */
    public function getSubtotalAttribute(): float
    {
        return (float) (($this->quantity ?? 0) * ($this->unit_price ?? 0));
    }

    /** Legacy XML service reads `product_name` on each line. */
    public function getProductNameAttribute(): string
    {
        return (string) ($this->service?->name ?? $this->description ?? '');
    }
}
