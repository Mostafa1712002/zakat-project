<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'product_id',
        'product_name',
        'product_sku',
        'quantity',
        'received_quantity',
        'unit_cost',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'subtotal',
        'total',
        'expiry_date',
        'batch_number',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'received_quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function returnItems(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->quantity * $this->unit_cost;

        if ($this->discount_type === 'percentage') {
            $this->discount_amount = $this->subtotal * ($this->discount_value / 100);
        } else {
            $this->discount_amount = $this->discount_value ?? 0;
        }

        $taxableAmount = $this->subtotal - $this->discount_amount;
        $this->tax_amount = $taxableAmount * ($this->tax_rate / 100);
        $this->total = $taxableAmount + $this->tax_amount;
    }

    public function getPendingQuantityAttribute(): float
    {
        return $this->quantity - $this->received_quantity;
    }

    public function getReturnedQuantityAttribute(): float
    {
        return $this->returnItems()->sum('quantity');
    }

    public function getReturnableQuantityAttribute(): float
    {
        return $this->received_quantity - $this->returned_quantity;
    }

    public function isFullyReceived(): bool
    {
        return $this->received_quantity >= $this->quantity;
    }
}
