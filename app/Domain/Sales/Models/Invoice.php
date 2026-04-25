<?php

namespace App\Domain\Sales\Models;

use App\Domain\Customer\Models\Customer;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Invoice — Phase 5b sales invoice header.
 *
 * Lifecycle: draft → issued → paid_partial / paid (or cancelled).
 * After issuance the invoice is immutable; ZATCA artefacts (uuid, icv, hash,
 * qr_code, signed_xml) are persisted on issue.
 *
 * The XML/QR generation pipeline (ZatcaXmlService) was originally built around
 * the legacy `Sale` model. To reuse it without rewriting the tested signing
 * pipeline, this model exposes the same accessor surface (zatca_uuid,
 * zatca_invoice_counter, zatca_previous_invoice_hash, zatca_issued_at,
 * zatca_invoice_type, zatca_note_type, invoice_date, items, tax_amount,
 * discount_amount, total_amount, payment_type) via lightweight accessors.
 *
 * Reference: .kiro/specs/ammrk-platform/design.md (Sales schema — invoices)
 *            .kiro/specs/ammrk-platform/tasks.md  (Task 5.5)
 */
class Invoice extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ISSUED = 'issued';
    public const STATUS_PAID_PARTIAL = 'paid_partial';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';

    public const ZATCA_PENDING = 'pending';
    public const ZATCA_CLEARED = 'cleared';
    public const ZATCA_REPORTED = 'reported';
    public const ZATCA_FAILED = 'failed';

    // Compat constants used by the legacy XML service when invoked with this
    // model. We only ever issue plain tax invoices in Phase 5b; credit/debit
    // notes are out of scope.
    public const ZATCA_INVOICE_STANDARD = 'standard';
    public const ZATCA_INVOICE_SIMPLIFIED = 'simplified';

    protected $fillable = [
        'invoice_number',
        'quote_id',
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
        'paid_amount',
        'status',
        'uuid',
        'icv',
        'pih',
        'invoice_hash',
        'qr_code',
        'signed_xml',
        'zatca_status',
        'zatca_uuid',
        'zatca_warnings',
        'zatca_submitted_at',
        'issued_at',
        'due_date',
        'notes',
    ];

    protected $casts = [
        'status' => 'string',
        'zatca_status' => 'string',
        'event_start_date' => 'date',
        'event_end_date' => 'date',
        'due_date' => 'date',
        'issued_at' => 'datetime',
        'zatca_submitted_at' => 'datetime',
        'zatca_warnings' => 'array',
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'icv' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Polymorphic payments relationship — Phase 6 will land the actual
     * Payment workflow. The `payable_*` columns already exist on the legacy
     * payments table.
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeIssued($query)
    {
        return $query->whereIn('status', [
            self::STATUS_ISSUED,
            self::STATUS_PAID_PARTIAL,
            self::STATUS_PAID,
        ]);
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', [self::STATUS_ISSUED, self::STATUS_PAID_PARTIAL]);
    }

    // -----------------------------------------------------------------------
    // Compatibility accessors for ZatcaXmlService (legacy Sale-shaped surface)
    // -----------------------------------------------------------------------

    public function getZatcaInvoiceCounterAttribute(): ?int
    {
        return $this->icv !== null ? (int) $this->icv : null;
    }

    public function getZatcaPreviousInvoiceHashAttribute(): ?string
    {
        return $this->pih;
    }

    public function getZatcaIssuedAtAttribute()
    {
        return $this->issued_at ?? $this->created_at ?? now();
    }

    /** Always 'standard' in Phase 5b (B2B tax invoice). */
    public function getZatcaInvoiceTypeAttribute(): string
    {
        return self::ZATCA_INVOICE_STANDARD;
    }

    /** No credit/debit notes in Phase 5b. */
    public function getZatcaNoteTypeAttribute(): ?string
    {
        return null;
    }

    public function getOriginalSaleAttribute()
    {
        return null;
    }

    /** ZATCA wants the issue date as `invoice_date`. */
    public function getInvoiceDateAttribute()
    {
        return $this->getZatcaIssuedAtAttribute();
    }

    /** Default to credit (B2B); cash invoices come later. */
    public function getPaymentTypeAttribute(): string
    {
        return 'credit';
    }

    public function getTaxAmountAttribute(): float
    {
        return (float) $this->attributes['tax_total'] ?? 0.0;
    }

    public function getDiscountAmountAttribute(): float
    {
        return (float) ($this->attributes['discount_total'] ?? 0);
    }

    public function getTotalAmountAttribute(): float
    {
        return (float) ($this->attributes['grand_total'] ?? 0);
    }
}
