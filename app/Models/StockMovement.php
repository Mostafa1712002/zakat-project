<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    use HasFactory, Auditable;

    public const TYPE_IN = 'in';
    public const TYPE_OUT = 'out';
    public const TYPE_TRANSFER = 'transfer';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_RETURN = 'return';

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'to_warehouse_id',
        'type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'unit_cost',
        'reference_type',
        'reference_id',
        'reference_number',
        'user_id',
        'reason',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'quantity_before' => 'decimal:3',
        'quantity_after' => 'decimal:3',
        'unit_cost' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeInWarehouse($query, int $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function isIncoming(): bool
    {
        return in_array($this->type, [self::TYPE_IN, self::TYPE_RETURN]);
    }

    public function isOutgoing(): bool
    {
        return $this->type === self::TYPE_OUT;
    }

    public function isTransfer(): bool
    {
        return $this->type === self::TYPE_TRANSFER;
    }

    public function isAdjustment(): bool
    {
        return $this->type === self::TYPE_ADJUSTMENT;
    }

    public function getTotalValueAttribute(): float
    {
        return $this->quantity * ($this->unit_cost ?? 0);
    }

    public static function recordMovement(array $data): self
    {
        $inventoryLevel = InventoryLevel::firstOrCreate(
            [
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['warehouse_id'],
            ],
            ['quantity' => 0, 'reserved_quantity' => 0]
        );

        $data['quantity_before'] = $inventoryLevel->quantity;

        $quantityChange = match ($data['type']) {
            self::TYPE_IN, self::TYPE_RETURN => $data['quantity'],
            self::TYPE_OUT => -$data['quantity'],
            self::TYPE_ADJUSTMENT => $data['quantity'],
            self::TYPE_TRANSFER => -$data['quantity'],
            default => 0,
        };

        $inventoryLevel->increment('quantity', $quantityChange);
        $data['quantity_after'] = $inventoryLevel->fresh()->quantity;

        if ($data['type'] === self::TYPE_TRANSFER && isset($data['to_warehouse_id'])) {
            $toInventory = InventoryLevel::firstOrCreate(
                [
                    'product_id' => $data['product_id'],
                    'warehouse_id' => $data['to_warehouse_id'],
                ],
                ['quantity' => 0, 'reserved_quantity' => 0]
            );
            $toInventory->increment('quantity', $data['quantity']);
        }

        return self::create($data);
    }
}
