<?php

namespace App\Domain\Sales\Actions;

use App\Domain\Sales\Models\Quote;
use LogicException;

/**
 * ConvertQuoteToInvoice — STUB.
 *
 * Phase 5b will introduce the Invoice model and ZATCA wiring; this stub
 * keeps the action class compilable so callers (e.g. controller routes)
 * can be wired in advance without referencing a non-existent model.
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 5.3, Phase 5b)
 */
class ConvertQuoteToInvoice
{
    public function execute(Quote $quote): never
    {
        throw new LogicException(
            'ConvertQuoteToInvoice will be implemented in Phase 5b (Invoice + ZATCA).'
        );
    }
}
