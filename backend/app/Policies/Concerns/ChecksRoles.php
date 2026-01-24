<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait ChecksRoles
{
    protected function hasRole(User $user, string $role): bool
    {
        if (method_exists($user, 'hasRole') && $user->hasRole($role)) {
            return true;
        }

        return $user->role === $role;
    }

    protected function dormId(User $user): ?int
    {
        return $user->dormAdmin?->dorm_id;
    }
}
