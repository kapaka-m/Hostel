<?php

namespace App\Policies\Concerns;

use App\Models\Dorm;
use App\Models\User;

trait ChecksRoles
{
    protected function isSuper(User $user): bool
    {
        return $user->role === User::ROLE_SUPER_ADMIN;
    }

    protected function hasRole(User $user, string $role): bool
    {
        if ($user->role === User::ROLE_SUPER_ADMIN) {
            return true;
        }

        if (method_exists($user, 'hasRole') && $user->hasRole($role)) {
            return true;
        }

        return $user->role === $role;
    }

    protected function universityId(User $user): ?int
    {
        return $user->university_id ?? $user->dormAdmin?->dorm?->university_id;
    }

    protected function dormId(User $user): ?int
    {
        return $user->dormAdmin?->dorm_id;
    }

    protected function dormMatchesUniversity(User $user, ?int $dormId): bool
    {
        if ($dormId === null) {
            return $this->universityId($user) !== null;
        }

        $universityId = $this->universityId($user);

        if ($universityId === null) {
            return false;
        }

        return Dorm::query()
            ->where('id', $dormId)
            ->where('university_id', $universityId)
            ->exists();
    }
}
