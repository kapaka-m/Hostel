<?php

namespace App\Policies;

use App\Models\Dorm;
use App\Models\User;
use App\Policies\Concerns\ChecksRoles;

class DormPolicy
{
    use ChecksRoles;

    public function viewAny(User $user): bool
    {
        return $this->hasRole($user, User::ROLE_UNIVERSITY_ADMIN);
    }

    public function view(User $user, Dorm $dorm): bool
    {
        if ($this->hasRole($user, User::ROLE_UNIVERSITY_ADMIN)) {
            return true;
        }

        if ($this->hasRole($user, User::ROLE_DORM_ADMIN)) {
            return $this->dormId($user) === $dorm->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->hasRole($user, User::ROLE_UNIVERSITY_ADMIN);
    }

    public function update(User $user, Dorm $dorm): bool
    {
        return $this->hasRole($user, User::ROLE_UNIVERSITY_ADMIN);
    }

    public function delete(User $user, Dorm $dorm): bool
    {
        return $this->hasRole($user, User::ROLE_UNIVERSITY_ADMIN);
    }

    public function createDormAdmin(User $user, Dorm $dorm): bool
    {
        return $this->hasRole($user, User::ROLE_UNIVERSITY_ADMIN);
    }
}
