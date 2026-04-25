<?php

namespace App\Domain\Treasury\Models;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Treasury — a cash or bank vault that holds money.
 *
 * `balance` is incremented when a Payment is recorded against an invoice
 * (see `RecordPayment`).
 *
 * Reference: .kiro/specs/ammrk-platform/design.md (Treasury schema)
 *            .kiro/specs/ammrk-platform/tasks.md  (Task 6.1)
 */
class Treasury extends Model
{
    use HasFactory;

    public const TYPE_CASH = 'cash';
    public const TYPE_BANK = 'bank';

    protected $fillable = [
        'name',
        'branch_id',
        'type',
        'balance',
        'is_active',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
