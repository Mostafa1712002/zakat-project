<?php

namespace App\Domain\Treasury\Models;

use App\Domain\Customer\Models\Customer;
use App\Domain\Sales\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Payment — money received against an invoice and parked in a Treasury.
 *
 * Payments are immutable once recorded; deletion is treated as a refund and
 * reverses both the invoice's `paid_amount` and the treasury's `balance`
 * (see `RefundPayment` action).
 *
 * Reference: .kiro/specs/ammrk-platform/design.md (Treasury schema)
 *            .kiro/specs/ammrk-platform/tasks.md  (Task 6.1)
 */
class Payment extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const METHOD_CASH = 'cash';
    public const METHOD_BANK = 'bank';
    public const METHOD_TRANSFER = 'transfer';
    public const METHOD_CHECK = 'check';

    protected $fillable = [
        'payment_number',
        'invoice_id',
        'customer_id',
        'treasury_id',
        'amount',
        'method',
        'reference_number',
        'payment_date',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function treasury(): BelongsTo
    {
        return $this->belongsTo(Treasury::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
