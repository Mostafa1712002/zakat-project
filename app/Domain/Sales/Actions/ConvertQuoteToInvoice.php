<?php

namespace App\Domain\Sales\Actions;

use App\Domain\Customer\Validators\ZatcaCustomerValidator;
use App\Domain\Sales\Models\Invoice;
use App\Domain\Sales\Models\Quote;
use App\Domain\Sales\Services\InvoiceCalculator;
use App\Domain\Sales\Services\InvoiceNumberGenerator;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use LogicException;
use RuntimeException;

/**
 * ConvertQuoteToInvoice — copies an approved quote into a new draft invoice.
 *
 * Validates that:
 *   1) the quote is in `approved` status,
 *   2) the customer is ZATCA-ready (VAT + REGA fields).
 *
 * Marks the quote as `converted` after creating the invoice. Items, event
 * details and totals are mirrored. The invoice starts in `draft` status — a
 * separate IssueInvoice action takes care of UUID/ICV/PIH/hash/QR/sign.
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 5.5)
 */
class ConvertQuoteToInvoice
{
    public function __construct(
        protected InvoiceNumberGenerator $numberGenerator,
        protected InvoiceCalculator $calculator,
    ) {
    }

    public function execute(Quote $quote): Invoice
    {
        if ($quote->status !== Quote::STATUS_APPROVED) {
            throw new LogicException(
                "Quote {$quote->quote_number} must be approved before conversion (status={$quote->status})."
            );
        }

        $quote->loadMissing('customer', 'items');

        $check = ZatcaCustomerValidator::isReadyForInvoicing($quote->customer);
        if (! $check['ok']) {
            throw new RuntimeException(
                'Customer is not ZATCA-ready: ' . implode(' ', $check['errors'])
            );
        }

        return DB::transaction(function () use ($quote) {
            $dueDays = (int) Setting::get('invoice_due_days', 30);

            $invoice = Invoice::create([
                'invoice_number' => $this->numberGenerator->next(),
                'quote_id' => $quote->id,
                'customer_id' => $quote->customer_id,
                'created_by' => $quote->created_by,
                'event_name' => $quote->event_name,
                'event_start_date' => $quote->event_start_date,
                'event_end_date' => $quote->event_end_date,
                'event_location' => $quote->event_location,
                'event_type' => $quote->event_type,
                'status' => Invoice::STATUS_DRAFT,
                'zatca_status' => Invoice::ZATCA_PENDING,
                'due_date' => Carbon::now()->addDays($dueDays)->toDateString(),
                'notes' => $quote->notes,
            ]);

            foreach ($quote->items as $row) {
                $invoice->items()->create([
                    'service_id' => $row->service_id,
                    'description' => $row->description,
                    'quantity' => $row->quantity,
                    'unit_price' => $row->unit_price,
                    'discount_amount' => $row->discount_amount,
                    'tax_rate' => $row->tax_rate,
                    'sort_order' => $row->sort_order,
                ]);
            }

            $this->calculator->recalculate($invoice);

            $quote->update(['status' => Quote::STATUS_CONVERTED]);

            return $invoice->refresh();
        });
    }
}
