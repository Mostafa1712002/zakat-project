<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesRepStockMovement extends Model
{
    public const TYPE_IN = 'in';           // استلام من المخزن
    public const TYPE_OUT = 'out';         // إرجاع للمخزن
    public const TYPE_SALE = 'sale';       // بيع
    public const TYPE_RETURN = 'return';   // مرتجع من عميل
    public const TYPE_ADJUSTMENT = 'adjustment'; // تسوية

    protected $fillable = [
        'sales_rep_id',
        'product_id',
        'type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'warehouse_id',
        'sale_id',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'quantity_before' => 'decimal:2',
        'quantity_after' => 'decimal:2',
    ];

    public function salesRep(): BelongsTo
    {
        return $this->belongsTo(SalesRep::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * اسم نوع الحركة
     */
    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            self::TYPE_IN => 'استلام من المخزن',
            self::TYPE_OUT => 'إرجاع للمخزن',
            self::TYPE_SALE => 'بيع',
            self::TYPE_RETURN => 'مرتجع',
            self::TYPE_ADJUSTMENT => 'تسوية',
            default => $this->type,
        };
    }

    /**
     * لون الحركة
     */
    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            self::TYPE_IN, self::TYPE_RETURN => 'success',
            self::TYPE_OUT, self::TYPE_SALE => 'danger',
            self::TYPE_ADJUSTMENT => 'warning',
            default => 'secondary',
        };
    }

    /**
     * تسجيل حركة مخزن
     */
    public static function record(
        int $salesRepId,
        int $productId,
        string $type,
        float $quantity,
        ?int $warehouseId = null,
        ?int $saleId = null,
        ?string $notes = null
    ): self {
        // الحصول على الكمية الحالية
        $inventory = SalesRepInventory::firstOrCreate(
            ['sales_rep_id' => $salesRepId, 'product_id' => $productId],
            ['quantity' => 0, 'reserved_quantity' => 0]
        );

        $quantityBefore = $inventory->quantity;

        // تحديث المخزون حسب نوع الحركة
        if (in_array($type, [self::TYPE_IN, self::TYPE_RETURN])) {
            $inventory->add($quantity);
        } else {
            $inventory->deduct($quantity);
        }

        // تسجيل الحركة
        return self::create([
            'sales_rep_id' => $salesRepId,
            'product_id' => $productId,
            'type' => $type,
            'quantity' => $quantity,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $inventory->quantity,
            'warehouse_id' => $warehouseId,
            'sale_id' => $saleId,
            'created_by' => auth()->id(),
            'notes' => $notes,
        ]);
    }
}
