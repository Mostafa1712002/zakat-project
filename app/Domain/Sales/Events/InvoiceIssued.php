<?php

namespace App\Domain\Sales\Events;

use App\Domain\Sales\Models\Invoice;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Dispatched after an invoice transitions from draft → issued.
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 8.1)
 */
class InvoiceIssued
{
    use Dispatchable;

    public function __construct(public Invoice $invoice)
    {
    }
}
