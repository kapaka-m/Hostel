<?php

namespace App\Policies;

use App\Models\Floor;
use App\Models\User;
use App\Policies\Concerns\ChecksRoles;

class FloorPolicy
{
    use ChecksRoles;

    public function viewAny(User $user): bool
    {
        return $this->hasRole($user, User::ROLE_UNIVERSITY_ADMIN)
            || $this->hasRole($user, User::ROLE_DORM_ADMIN);
    }

    public function view(User $user, Floor $floor): bool
    {
        if ($this->isSuper($user)) {
            return true;
        }

        if ($this->hasRole($user, User::ROLE_UNIVERSITY_ADMIN)) {
            return $this->dormMatchesUniversity($user, $floor->dorm_id);
        }

        if ($this->hasRole($user, User::ROLE_DORM_ADMIN)) {
            return $this->dormId($user) === $floor->dorm_id
                && $this->dormMatchesUniversity($user, $floor->dorm_id);
        }

        return false;
    }

    public function create(User $user, ?int $dormId = null): bool
    {
        if ($this->isSuper($user)) {
            return true;
        }

        if ($this->hasRole($user, User::ROLE_UNIVERSITY_ADMIN)) {
            return $this->dormMatchesUniversity($user, $dormId);
        }

        if ($this->hasRole($user, User::ROLE_DORM_ADMIN)) {
            return ($dormId === null || $this->dormId($user) === $dormId)
                && $this->dormMatchesUniversity($user, $dormId);
        }

        return false;
    }

    public function update(User $user, Floor $floor): bool
    {
        return $this->view($user, $floor);
    }

    public function delete(User $user, Floor $floor): bool
    {
        return $this->view($user, $floor);
    }
}
