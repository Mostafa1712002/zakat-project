<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity',
        'reserved_quantity',
        'min_stock',
        'max_stock',
        'reorder_point',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'reserved_quantity' => 'decimal:3',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('quantity <= min_stock');
    }

    public function scopeOverStock($query)
    {
        return $query->whereNotNull('max_stock')
            ->whereRaw('quantity > max_stock');
    }

    public function scopeNeedsReorder($query)
    {
        return $query->whereNotNull('reorder_point')
            ->whereRaw('quantity <= reorder_point');
    }

    public function getAvailableQuantityAttribute(): float
    {
        return max(0, $this->quantity - $this->reserved_quantity);
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->min_stock;
    }

    public function isOverStock(): bool
    {
        return $this->max_stock && $this->quantity > $this->max_stock;
    }

    public function needsReorder(): bool
    {
        return $this->reorder_point && $this->quantity <= $this->reorder_point;
    }

    public function adjustQuantity(float $amount, bool $isReserved = false): void
    {
        if ($isReserved) {
            $this->increment('reserved_quantity', $amount);
        } else {
            $this->increment('quantity', $amount);
        }
    }

    public function reserve(float $quantity): bool
    {
        if ($this->available_quantity < $quantity) {
            return false;
        }

        $this->increment('reserved_quantity', $quantity);
        return true;
    }

    public function release(float $quantity): void
    {
        $this->decrement('reserved_quantity', min($quantity, $this->reserved_quantity));
    }
}
