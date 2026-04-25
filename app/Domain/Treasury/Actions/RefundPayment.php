<?php

namespace App\Domain\Treasury\Actions;

use App\Domain\Sales\Models\Invoice;
use App\Domain\Treasury\Models\Payment;
use App\Domain\Treasury\Models\Treasury;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * RefundPayment — soft-deletes a payment and reverses its side-effects.
 *
 * Reverses:
 *   - invoice.paid_amount -= payment.amount
 *   - invoice.status downgrade (paid_partial → issued if paid_amount=0)
 *   - treasury.balance -= payment.amount
 *   - payment.deleted_at = now()
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 6.2 / 6.3)
 */
class RefundPayment
{
    public function execute(Payment $payment, User $actor): void
    {
        DB::transaction(function () use ($payment, $actor) {
            /** @var Invoice $invoice */
            $invoice = Invoice::lockForUpdate()->findOrFail($payment->invoice_id);
            /** @var Treasury $treasury */
            $treasury = Treasury::lockForUpdate()->findOrFail($payment->treasury_id);

            $amount = (float) $payment->amount;

            $newPaid = max(0.0, (float) $invoice->paid_amount - $amount);
            $newStatus = $invoice->status;
            if ($newPaid <= 0.001) {
                $newStatus = Invoice::STATUS_ISSUED;
            } elseif ($newPaid + 0.001 < (float) $invoice->grand_total) {
                $newStatus = Invoice::STATUS_PAID_PARTIAL;
            }
            $invoice->update([
                'paid_amount' => $newPaid,
                'status' => $newStatus,
            ]);

            $treasury->update([
                'balance' => max(0.0, (float) $treasury->balance - $amount),
            ]);

            $payment->delete();

            AuditLog::create([
                'user_id' => $actor->getAuthIdentifier(),
                'auditable_type' => Payment::class,
                'auditable_id' => $payment->id,
                'event' => 'payment.refunded',
                'old_values' => ['amount' => $amount],
                'new_values' => null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
            ]);
        });
    }
}
