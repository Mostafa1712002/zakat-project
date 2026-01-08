<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'product_name',
        'product_sku',
        'quantity',
        'unit_price',
        'cost_price',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'subtotal',
        'total',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function returnItems(): HasMany
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->quantity * $this->unit_price;

        if ($this->discount_type === 'percentage') {
            $this->discount_amount = $this->subtotal * ($this->discount_value / 100);
        } else {
            $this->discount_amount = $this->discount_value ?? 0;
        }

        $taxableAmount = $this->subtotal - $this->discount_amount;
        $this->tax_amount = $taxableAmount * ($this->tax_rate / 100);
        $this->total = $taxableAmount + $this->tax_amount;
    }

    public function getProfitAttribute(): float
    {
        return ($this->unit_price - $this->cost_price) * $this->quantity - $this->discount_amount;
    }

    public function getProfitMarginAttribute(): float
    {
        if ($this->cost_price <= 0) {
            return 0;
        }

        return (($this->unit_price - $this->cost_price) / $this->cost_price) * 100;
    }

    public function getReturnedQuantityAttribute(): float
    {
        return $this->returnItems()->sum('quantity');
    }

    public function getReturnableQuantityAttribute(): float
    {
        return $this->quantity - $this->returned_quantity;
    }
}
