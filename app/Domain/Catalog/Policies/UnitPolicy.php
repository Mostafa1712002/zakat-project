<?php

namespace App\Domain\Catalog\Policies;

use App\Domain\Catalog\Models\Unit;
use App\Models\User;

/**
 * Policy mapping Unit actions to Spatie permissions.
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 3.5)
 */
class UnitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('units.view');
    }

    public function view(User $user, Unit $unit): bool
    {
        return $user->can('units.view');
    }

    public function create(User $user): bool
    {
        return $user->can('units.create');
    }

    public function update(User $user, Unit $unit): bool
    {
        return $user->can('units.edit');
    }

    public function delete(User $user, Unit $unit): bool
    {
        return $user->can('units.delete');
    }
}
