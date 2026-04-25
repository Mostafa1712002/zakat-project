<?php

namespace App\Domain\Treasury\Actions;

use App\Domain\Sales\Models\Invoice;
use App\Domain\Treasury\Models\Payment;
use App\Domain\Treasury\Models\Treasury;
use App\Domain\Treasury\Services\PaymentNumberGenerator;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * RecordPayment — records a customer payment against an invoice.
 *
 * Side-effects (all in one DB transaction):
 *   1. Creates the Payment row with an auto-generated payment_number.
 *   2. Increments invoice.paid_amount.
 *   3. Updates invoice.status (paid_partial / paid) when fully covered.
 *   4. Increments treasury.balance.
 *   5. Writes an AuditLog entry under 'payment.recorded'.
 *
 * Throws `ValidationException` if amount <= 0 or the payment would overpay
 * the remaining invoice balance.
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 6.2)
 */
class RecordPayment
{
    public function __construct(private PaymentNumberGenerator $numberGenerator)
    {
    }

    /**
     * @param  array{
     *     invoice_id:int,
     *     treasury_id:int,
     *     amount:numeric,
     *     method?:string,
     *     reference_number?:string|null,
     *     payment_date?:string|null,
     *     notes?:string|null,
     * }  $data
     */
    public function execute(array $data, User $actor): Payment
    {
        $amount = (float) $data['amount'];
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'يجب أن يكون مبلغ الدفع أكبر من صفر',
            ]);
        }

        return DB::transaction(function () use ($data, $actor, $amount) {
            /** @var Invoice $invoice */
            $invoice = Invoice::lockForUpdate()->findOrFail($data['invoice_id']);

            if (in_array($invoice->status, [Invoice::STATUS_DRAFT, Invoice::STATUS_CANCELLED], true)) {
                throw ValidationException::withMessages([
                    'invoice_id' => 'لا يمكن تسجيل دفعة على فاتورة بحالة ' . $invoice->status,
                ]);
            }

            $remaining = (float) $invoice->grand_total - (float) $invoice->paid_amount;
            if ($amount > $remaining + 0.001) { // float tolerance
                throw ValidationException::withMessages([
                    'amount' => sprintf(
                        'المبلغ يتجاوز المتبقي على الفاتورة (%.2f)',
                        $remaining
                    ),
                ]);
            }

            /** @var Treasury $treasury */
            $treasury = Treasury::lockForUpdate()->findOrFail($data['treasury_id']);

            $payment = Payment::create([
                'payment_number' => $this->numberGenerator->next(),
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'treasury_id' => $treasury->id,
                'amount' => $amount,
                'method' => $data['method'] ?? Payment::METHOD_CASH,
                'reference_number' => $data['reference_number'] ?? null,
                'payment_date' => $data['payment_date'] ?? Carbon::now()->toDateString(),
                'created_by' => $actor->getAuthIdentifier(),
                'notes' => $data['notes'] ?? null,
            ]);

            // Update invoice
            $newPaid = (float) $invoice->paid_amount + $amount;
            $newStatus = $invoice->status;
            if ($newPaid + 0.001 >= (float) $invoice->grand_total) {
                $newStatus = Invoice::STATUS_PAID;
            } elseif ($newPaid > 0) {
                $newStatus = Invoice::STATUS_PAID_PARTIAL;
            }
            $invoice->update([
                'paid_amount' => $newPaid,
                'status' => $newStatus,
            ]);

            // Update treasury balance
            $treasury->update([
                'balance' => (float) $treasury->balance + $amount,
            ]);

            AuditLog::create([
                'user_id' => $actor->getAuthIdentifier(),
                'auditable_type' => Payment::class,
                'auditable_id' => $payment->id,
                'event' => 'payment.recorded',
                'old_values' => null,
                'new_values' => [
                    'invoice_id' => $invoice->id,
                    'treasury_id' => $treasury->id,
                    'amount' => $amount,
                    'method' => $payment->method,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
            ]);

            return $payment->refresh();
        });
    }
}
