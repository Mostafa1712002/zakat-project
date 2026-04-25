<?php

namespace App\Domain\Customer\Policies;

use App\Domain\Customer\Models\Customer;
use App\Models\User;

/**
 * CustomerPolicy — Spatie permission checks plus account-manager
 * ownership for view-own/edit-own/delete-own scenarios.
 *
 * Reference: .kiro/specs/ammrk-platform/requirements.md (US-011)
 *            .kiro/specs/ammrk-platform/tasks.md (Task 4.4)
 */
class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAny(['customers.view-own', 'customers.view-all']);
    }

    public function view(User $user, Customer $customer): bool
    {
        if ($user->can('customers.view-all')) {
            return true;
        }

        if ($user->can('customers.view-own')) {
            return $customer->account_manager_id === $user->getAuthIdentifier();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('customers.create');
    }

    public function update(User $user, Customer $customer): bool
    {
        if (! $user->can('customers.edit')) {
            return false;
        }

        return $this->view($user, $customer);
    }

    public function delete(User $user, Customer $customer): bool
    {
        if (! $user->can('customers.delete')) {
            return false;
        }

        return $this->view($user, $customer);
    }
}
