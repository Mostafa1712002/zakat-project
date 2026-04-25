<?php

namespace App\Listeners;

use App\Domain\Sales\Events\InvoiceIssued;
use App\Domain\Sales\Events\QuoteApproved;
use App\Domain\Sales\Events\QuoteRejected;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

/**
 * AuditLogListener — records sales/treasury domain events to audit_logs.
 *
 * Listens for:
 *   - InvoiceIssued    → event 'invoice.issued'
 *   - QuoteApproved    → event 'quote.approved'
 *   - QuoteRejected    → event 'quote.rejected'
 *
 * Note: Payment events (recorded/refunded) are NOT routed through this
 * listener because RecordPayment/RefundPayment already write AuditLog rows
 * inline (Phase 6). Routing them through here would create duplicate rows.
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 8.1)
 */
class AuditLogListener
{
    public function handle(object $event): void
    {
        match (true) {
            $event instanceof InvoiceIssued => $this->onInvoiceIssued($event),
            $event instanceof QuoteApproved => $this->onQuoteApproved($event),
            $event instanceof QuoteRejected => $this->onQuoteRejected($event),
            default => null,
        };
    }

    protected function onInvoiceIssued(InvoiceIssued $event): void
    {
        $invoice = $event->invoice;

        $this->write(
            event: 'invoice.issued',
            type: $invoice::class,
            id: $invoice->id,
            data: [
                'invoice_number' => $invoice->invoice_number,
                'customer_id' => $invoice->customer_id,
                'grand_total' => (float) $invoice->grand_total,
                'uuid' => $invoice->uuid,
                'icv' => $invoice->icv,
                'zatca_status' => $invoice->zatca_status,
            ],
        );
    }

    protected function onQuoteApproved(QuoteApproved $event): void
    {
        $quote = $event->quote;

        $this->write(
            event: 'quote.approved',
            type: $quote::class,
            id: $quote->id,
            userId: $event->approver->getAuthIdentifier(),
            data: [
                'quote_number' => $quote->quote_number,
                'customer_id' => $quote->customer_id,
                'grand_total' => (float) $quote->grand_total,
                'approved_by' => $event->approver->getAuthIdentifier(),
            ],
        );
    }

    protected function onQuoteRejected(QuoteRejected $event): void
    {
        $quote = $event->quote;

        $this->write(
            event: 'quote.rejected',
            type: $quote::class,
            id: $quote->id,
            userId: $event->rejecter->getAuthIdentifier(),
            data: [
                'quote_number' => $quote->quote_number,
                'customer_id' => $quote->customer_id,
                'rejection_reason' => $event->reason,
                'rejected_by' => $event->rejecter->getAuthIdentifier(),
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function write(
        string $event,
        string $type,
        int $id,
        array $data,
        ?int $userId = null,
    ): void {
        AuditLog::create([
            'user_id' => $userId ?? Auth::id(),
            'auditable_type' => $type,
            'auditable_id' => $id,
            'event' => $event,
            'old_values' => null,
            'new_values' => $data,
            'ip_address' => $this->safeRequest(fn ($r) => $r->ip()),
            'user_agent' => $this->safeRequest(fn ($r) => $r->userAgent()),
            'url' => $this->safeRequest(fn ($r) => $r->fullUrl()),
        ]);
    }

    /**
     * Some events are dispatched from CLI/queue contexts where request()
     * helpers return null. Guard against that for smoke tests.
     */
    protected function safeRequest(\Closure $fn): ?string
    {
        try {
            $req = request();
            if ($req === null) {
                return null;
            }
            $value = $fn($req);
            return is_string($value) ? $value : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
