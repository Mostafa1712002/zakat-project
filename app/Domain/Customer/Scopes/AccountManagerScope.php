<?php

namespace App\Domain\Customer\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * AccountManagerScope — restrict customers to the authenticated user's
 * book of business when they hold ONLY the `Account Manager` role.
 *
 * Bypasses the filter for users that also hold elevated roles
 * (Admin, Super Admin, Accountant) so internal staff see all customers.
 *
 * Reference: .kiro/specs/ammrk-platform/requirements.md (US-011)
 *            .kiro/specs/ammrk-platform/tasks.md (Task 4.1)
 */
class AccountManagerScope implements Scope
{
    /**
     * Roles whose holders bypass the manager filter.
     *
     * @var list<string>
     */
    protected array $elevatedRoles = ['Admin', 'Super Admin', 'Accountant'];

    public function apply(Builder $builder, Model $model): void
    {
        if (! auth()->check()) {
            return;
        }

        $user = auth()->user();

        // Spatie HasRoles is mixed in via the User model; guard against tests
        // or non-User auth providers by checking the method exists.
        if (! method_exists($user, 'hasRole')) {
            return;
        }

        if ($user->hasAnyRole($this->elevatedRoles)) {
            return;
        }

        if ($user->hasRole('Account Manager')) {
            $builder->where($model->qualifyColumn('account_manager_id'), $user->getAuthIdentifier());
        }
    }
}
