<?php

namespace App\Domain\Sales\Actions;

use App\Domain\Sales\Models\Quote;
use DomainException;

/**
 * SubmitQuote — transitions a draft quote to submitted, awaiting approval.
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 5.3)
 */
class SubmitQuote
{
    public function execute(Quote $quote): Quote
    {
        if ($quote->status !== Quote::STATUS_DRAFT) {
            throw new DomainException(
                "Cannot submit quote in status [{$quote->status}]; expected draft."
            );
        }

        $quote->update(['status' => Quote::STATUS_SUBMITTED]);

        return $quote;
    }
}
