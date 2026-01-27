<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;
use App\Policies\Concerns\ChecksRoles;

class StudentPolicy
{
    use ChecksRoles;

    public function viewAny(User $user): bool
    {
        return $this->hasRole($user, User::ROLE_UNIVERSITY_ADMIN)
            || $this->hasRole($user, User::ROLE_DORM_ADMIN);
    }

    public function view(User $user, Student $student): bool
    {
        if ($this->isSuper($user)) {
            return true;
        }

        if ($this->hasRole($user, User::ROLE_UNIVERSITY_ADMIN)) {
            return $this->dormMatchesUniversity($user, $student->dorm_id);
        }

        if ($this->hasRole($user, User::ROLE_DORM_ADMIN)) {
            return $this->dormId($user) === $student->dorm_id
                && $this->dormMatchesUniversity($user, $student->dorm_id);
        }

        if ($this->hasRole($user, User::ROLE_STUDENT)) {
            return $student->user_id === $user->id;
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

    public function update(User $user, Student $student): bool
    {
        if ($this->isSuper($user)) {
            return true;
        }

        return ($this->hasRole($user, User::ROLE_UNIVERSITY_ADMIN)
            && $this->dormMatchesUniversity($user, $student->dorm_id))
            || ($this->hasRole($user, User::ROLE_DORM_ADMIN)
                && $this->dormId($user) === $student->dorm_id
                && $this->dormMatchesUniversity($user, $student->dorm_id));
    }

    public function delete(User $user, Student $student): bool
    {
        if ($this->isSuper($user)) {
            return true;
        }

        return ($this->hasRole($user, User::ROLE_UNIVERSITY_ADMIN)
            && $this->dormMatchesUniversity($user, $student->dorm_id))
            || ($this->hasRole($user, User::ROLE_DORM_ADMIN)
                && $this->dormId($user) === $student->dorm_id
                && $this->dormMatchesUniversity($user, $student->dorm_id));
    }

    public function viewMyRoom(User $user, Student $student): bool
    {
        return $this->hasRole($user, User::ROLE_STUDENT) && $student->user_id === $user->id;
    }
}
