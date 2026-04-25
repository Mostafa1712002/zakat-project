<?php

namespace App\Console\Commands;

use App\Domain\Sales\Models\Invoice;
use App\Jobs\SubmitInvoiceToZatca;
use Illuminate\Console\Command;

/**
 * RetryZatcaFailedSubmissions — re-dispatches the queued submission job
 * for any invoice with zatca_status='failed' issued in the last 7 days.
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 5.6)
 */
class RetryZatcaFailedSubmissions extends Command
{
    protected $signature = 'zatca:retry-failed';

    protected $description = 'Re-dispatch SubmitInvoiceToZatca for failed invoices issued in the last 7 days.';

    public function handle(): int
    {
        $invoices = Invoice::query()
            ->where('zatca_status', Invoice::ZATCA_FAILED)
            ->whereNotNull('issued_at')
            ->where('issued_at', '>=', now()->subDays(7))
            ->get(['id', 'invoice_number']);

        if ($invoices->isEmpty()) {
            $this->info('No failed ZATCA submissions in the last 7 days.');
            return self::SUCCESS;
        }

        foreach ($invoices as $invoice) {
            SubmitInvoiceToZatca::dispatch($invoice->id);
            $this->line("Dispatched: {$invoice->invoice_number}");
        }

        $this->info("Re-dispatched {$invoices->count()} invoice(s).");
        return self::SUCCESS;
    }
}
