<?php

namespace App\Domain\Sales\Policies;

use App\Domain\Sales\Models\Quote;
use App\Models\User;

/**
 * QuotePolicy — Spatie permissions plus ownership checks.
 *
 * Account Manager: view-own / edit-own / submit on their own drafts.
 * Admin / Accountant: view-all and approve/reject submitted quotes.
 *
 * Reference: .kiro/specs/ammrk-platform/requirements.md
 *            .kiro/specs/ammrk-platform/tasks.md (Task 5.4)
 */
class QuotePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAny(['quotes.view-own', 'quotes.view-all']);
    }

    public function view(User $user, Quote $quote): bool
    {
        if ($user->can('quotes.view-all')) {
            return true;
        }

        if ($user->can('quotes.view-own')) {
            return $quote->created_by === $user->getAuthIdentifier();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('quotes.create');
    }

    public function update(User $user, Quote $quote): bool
    {
        if ($user->can('quotes.edit-any')) {
            return true;
        }

        if (
            $user->can('quotes.edit-own')
            && $quote->created_by === $user->getAuthIdentifier()
            && $quote->status === Quote::STATUS_DRAFT
        ) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Quote $quote): bool
    {
        return $this->update($user, $quote);
    }

    public function submit(User $user, Quote $quote): bool
    {
        return $user->can('quotes.submit')
            && $quote->created_by === $user->getAuthIdentifier()
            && $quote->status === Quote::STATUS_DRAFT;
    }

    public function approve(User $user, Quote $quote): bool
    {
        return $user->can('quotes.approve')
            && $quote->status === Quote::STATUS_SUBMITTED;
    }

    public function reject(User $user, Quote $quote): bool
    {
        return $user->can('quotes.reject')
            && $quote->status === Quote::STATUS_SUBMITTED;
    }
}
