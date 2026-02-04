<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'name',
        'code',
        'branch_id',
        'address',
        'phone',
        'manager_name',
        'is_active',
        'is_default',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function inventoryLevels(): HasMany
    {
        return $this->hasMany(InventoryLevel::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'to_warehouse_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeInBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function getProductStock(int $productId): float
    {
        return $this->inventoryLevels()
            ->where('product_id', $productId)
            ->value('quantity') ?? 0;
    }

    public function getAvailableStock(int $productId): float
    {
        $level = $this->inventoryLevels()
            ->where('product_id', $productId)
            ->first();

        return $level ? ($level->quantity - $level->reserved_quantity) : 0;
    }

    public function getTotalStockValueAttribute(): float
    {
        return $this->inventoryLevels()
            ->join('products', 'products.id', '=', 'inventory_levels.product_id')
            ->sum(\DB::raw('inventory_levels.quantity * products.cost_price'));
    }

    /**
     * المندوبين المرتبطين بالمخزن
     */
    public function salesReps(): BelongsToMany
    {
        return $this->belongsToMany(SalesRep::class, 'sales_rep_warehouse')
            ->withPivot('is_default')
            ->withTimestamps();
    }

    /**
     * تعديل المخزون (إضافة أو خصم)
     */
    public function adjustStock(int $productId, float $quantity, string $type, string $notes = null): void
    {
        // البحث عن أو إنشاء مستوى المخزون
        $inventoryLevel = $this->inventoryLevels()->firstOrCreate(
            ['product_id' => $productId],
            ['quantity' => 0, 'reserved_quantity' => 0]
        );

        // تحديث الكمية
        $inventoryLevel->quantity += $quantity;
        $inventoryLevel->save();

        // تسجيل حركة المخزون
        StockMovement::create([
            'warehouse_id' => $this->id,
            'product_id' => $productId,
            'type' => $type,
            'quantity' => $quantity,
            'cost_price' => Product::find($productId)->cost_price ?? 0,
            'reference_type' => 'manual',
            'notes' => $notes,
            'user_id' => auth()->id(),
        ]);
    }
}
