<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesRepInventory extends Model
{
    protected $table = 'sales_rep_inventory';

    protected $fillable = [
        'sales_rep_id',
        'product_id',
        'quantity',
        'reserved_quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'reserved_quantity' => 'decimal:2',
    ];

    public function salesRep(): BelongsTo
    {
        return $this->belongsTo(SalesRep::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * الكمية المتاحة للبيع
     */
    public function getAvailableQuantityAttribute(): float
    {
        return max(0, $this->quantity - $this->reserved_quantity);
    }

    /**
     * حجز كمية للفاتورة
     */
    public function reserve(float $quantity): bool
    {
        if ($quantity > $this->available_quantity) {
            return false;
        }

        $this->increment('reserved_quantity', $quantity);
        return true;
    }

    /**
     * إلغاء الحجز
     */
    public function release(float $quantity): void
    {
        $this->decrement('reserved_quantity', min($quantity, $this->reserved_quantity));
    }

    /**
     * خصم من المخزون (عند تأكيد البيع)
     */
    public function deduct(float $quantity): bool
    {
        if ($quantity > $this->quantity) {
            return false;
        }

        $this->decrement('quantity', $quantity);
        $this->decrement('reserved_quantity', min($quantity, $this->reserved_quantity));
        return true;
    }

    /**
     * إضافة للمخزون
     */
    public function add(float $quantity): void
    {
        $this->increment('quantity', $quantity);
    }

    /**
     * التحقق من توفر الكمية
     */
    public function hasStock(float $quantity): bool
    {
        return $this->available_quantity >= $quantity;
    }
}
