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
        if ($this->isSuper($user)) {
            return true;
        }

        if ($this->hasRole($user, User::ROLE_UNIVERSITY_ADMIN)) {
            return $this->universityId($user) !== null && $this->universityId($user) === $dorm->university_id;
        }

        if ($this->hasRole($user, User::ROLE_DORM_ADMIN)) {
            return $this->dormId($user) === $dorm->id
                && $this->universityId($user) === $dorm->university_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($this->isSuper($user)) {
            return true;
        }

        return $this->hasRole($user, User::ROLE_UNIVERSITY_ADMIN)
            && $this->universityId($user) !== null;
    }

    public function update(User $user, Dorm $dorm): bool
    {
        if ($this->isSuper($user)) {
            return true;
        }

        return $this->hasRole($user, User::ROLE_UNIVERSITY_ADMIN)
            && $this->universityId($user) === $dorm->university_id;
    }

    public function delete(User $user, Dorm $dorm): bool
    {
        if ($this->isSuper($user)) {
            return true;
        }

        return $this->hasRole($user, User::ROLE_UNIVERSITY_ADMIN)
            && $this->universityId($user) === $dorm->university_id;
    }

    public function createDormAdmin(User $user, Dorm $dorm): bool
    {
        if ($this->isSuper($user)) {
            return true;
        }

        return $this->hasRole($user, User::ROLE_UNIVERSITY_ADMIN)
            && $this->universityId($user) === $dorm->university_id;
    }
}
