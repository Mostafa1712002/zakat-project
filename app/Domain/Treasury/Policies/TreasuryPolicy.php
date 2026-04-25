<?php

namespace App\Domain\Treasury\Policies;

use App\Domain\Treasury\Models\Treasury;
use App\Models\User;

/**
 * TreasuryPolicy — gates treasury management to settings.system holders
 * (Super Admin) plus accountants who can record payments.
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 6.3)
 */
class TreasuryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('payments.view');
    }

    public function view(User $user, Treasury $treasury): bool
    {
        return $user->can('payments.view');
    }

    public function create(User $user): bool
    {
        return $user->canAny(['settings.system', 'payments.refund']);
    }

    public function update(User $user, Treasury $treasury): bool
    {
        return $user->canAny(['settings.system', 'payments.refund']);
    }

    public function delete(User $user, Treasury $treasury): bool
    {
        return $user->canAny(['settings.system', 'payments.refund']);
    }
}
