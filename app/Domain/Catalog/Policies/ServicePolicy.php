<?php

namespace App\Domain\Catalog\Policies;

use App\Domain\Catalog\Models\Service;
use App\Models\User;

/**
 * Policy mapping Service actions to Spatie permissions.
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 3.5)
 */
class ServicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('services.view');
    }

    public function view(User $user, Service $service): bool
    {
        return $user->can('services.view');
    }

    public function create(User $user): bool
    {
        return $user->can('services.create');
    }

    public function update(User $user, Service $service): bool
    {
        return $user->can('services.edit');
    }

    public function delete(User $user, Service $service): bool
    {
        return $user->can('services.delete');
    }
}
