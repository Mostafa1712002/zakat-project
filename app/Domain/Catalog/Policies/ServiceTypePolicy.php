<?php

namespace App\Domain\Catalog\Policies;

use App\Domain\Catalog\Models\ServiceType;
use App\Models\User;

/**
 * Policy mapping ServiceType actions to Spatie permissions.
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 3.5)
 */
class ServiceTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('service-types.view');
    }

    public function view(User $user, ServiceType $serviceType): bool
    {
        return $user->can('service-types.view');
    }

    public function create(User $user): bool
    {
        return $user->can('service-types.create');
    }

    public function update(User $user, ServiceType $serviceType): bool
    {
        return $user->can('service-types.edit');
    }

    public function delete(User $user, ServiceType $serviceType): bool
    {
        return $user->can('service-types.delete');
    }
}
