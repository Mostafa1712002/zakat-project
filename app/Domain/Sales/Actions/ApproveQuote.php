<?php

namespace App\Domain\Sales\Actions;

use App\Domain\Sales\Models\Quote;
use App\Models\User;
use DomainException;
use Illuminate\Support\Carbon;

/**
 * ApproveQuote — admin approves a submitted quote.
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 5.3)
 */
class ApproveQuote
{
    public function execute(Quote $quote, User $approver): Quote
    {
        if ($quote->status !== Quote::STATUS_SUBMITTED) {
            throw new DomainException(
                "Cannot approve quote in status [{$quote->status}]; expected submitted."
            );
        }

        $quote->update([
            'status' => Quote::STATUS_APPROVED,
            'approved_by' => $approver->getAuthIdentifier(),
            'approved_at' => Carbon::now(),
            'rejection_reason' => null,
        ]);

        return $quote;
    }
}
