<?php

namespace App\Domain\Sales\Events;

use App\Domain\Sales\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Dispatched when a submitted quote is approved.
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 8.1)
 */
class QuoteApproved
{
    use Dispatchable;

    public function __construct(public Quote $quote, public User $approver)
    {
    }
}
