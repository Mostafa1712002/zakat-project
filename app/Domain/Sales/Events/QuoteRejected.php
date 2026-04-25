<?php

namespace App\Domain\Sales\Events;

use App\Domain\Sales\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Dispatched when a submitted quote is rejected.
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 8.1)
 */
class QuoteRejected
{
    use Dispatchable;

    public function __construct(
        public Quote $quote,
        public User $rejecter,
        public string $reason,
    ) {
    }
}
