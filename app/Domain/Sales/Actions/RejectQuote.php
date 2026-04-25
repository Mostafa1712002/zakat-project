<?php

namespace App\Domain\Sales\Actions;

use App\Domain\Sales\Events\QuoteRejected;
use App\Domain\Sales\Models\Quote;
use App\Models\User;
use DomainException;
use InvalidArgumentException;

/**
 * RejectQuote — admin rejects a submitted quote with a required reason.
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 5.3)
 */
class RejectQuote
{
    public function execute(Quote $quote, User $rejecter, string $reason): Quote
    {
        if ($quote->status !== Quote::STATUS_SUBMITTED) {
            throw new DomainException(
                "Cannot reject quote in status [{$quote->status}]; expected submitted."
            );
        }

        $reason = trim($reason);
        if ($reason === '') {
            throw new InvalidArgumentException('Rejection reason is required.');
        }

        $quote->update([
            'status' => Quote::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'approved_by' => $rejecter->getAuthIdentifier(),
            'approved_at' => null,
        ]);

        QuoteRejected::dispatch($quote, $rejecter, $reason);

        return $quote;
    }
}
