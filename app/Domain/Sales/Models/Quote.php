<?php

namespace App\Domain\Sales\Models;

use App\Domain\Customer\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Quote — Phase 5a sales quote header.
 *
 * Workflow: draft → submitted → approved/rejected → converted (Phase 5b).
 *
 * Reference: .kiro/specs/ammrk-platform/design.md (Sales schema)
 *            .kiro/specs/ammrk-platform/tasks.md (Task 5.2)
 */
class Quote extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CONVERTED = 'converted';

    protected $fillable = [
        'quote_number',
        'customer_id',
        'created_by',
        'event_name',
        'event_start_date',
        'event_end_date',
        'event_location',
        'event_type',
        'subtotal',
        'discount_total',
        'tax_total',
        'grand_total',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'valid_until',
        'notes',
    ];

    protected $casts = [
        'status' => 'string',
        'event_start_date' => 'date',
        'event_end_date' => 'date',
        'valid_until' => 'date',
        'approved_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }
}
