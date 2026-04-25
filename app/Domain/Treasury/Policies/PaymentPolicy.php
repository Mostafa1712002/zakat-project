<?php

namespace App\Domain\Treasury\Policies;

use App\Domain\Treasury\Models\Payment;
use App\Models\User;

/**
 * PaymentPolicy — Spatie permission-driven authorization.
 *
 * Treats `delete` as the refund flow (payments are immutable; soft-deleting
 * a payment is the only way to reverse it).
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 6.2)
 */
class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('payments.view');
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->can('payments.view');
    }

    public function create(User $user): bool
    {
        return $user->can('payments.create');
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->can('payments.refund');
    }
}
