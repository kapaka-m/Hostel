<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;
use App\Policies\Concerns\ChecksRoles;

class AnnouncementPolicy
{
    use ChecksRoles;

    public function viewAny(User $user): bool
    {
        return $this->hasRole($user, User::ROLE_UNIVERSITY_ADMIN)
            || $this->hasRole($user, User::ROLE_DORM_ADMIN);
    }

    public function view(User $user, Announcement $announcement): bool
    {
        if ($this->isSuper($user)) {
            return true;
        }

        if ($this->hasRole($user, User::ROLE_UNIVERSITY_ADMIN)) {
            return $announcement->university_id === $this->universityId($user);
        }

        if ($this->hasRole($user, User::ROLE_DORM_ADMIN)) {
            return $announcement->dorm_id === $this->dormId($user)
                && $this->dormMatchesUniversity($user, $announcement->dorm_id);
        }

        return false;
    }

    public function create(User $user, ?int $universityId = null, ?int $dormId = null): bool
    {
        if ($this->isSuper($user)) {
            return true;
        }

        if ($this->hasRole($user, User::ROLE_UNIVERSITY_ADMIN)) {
            return $universityId !== null && $universityId === $this->universityId($user);
        }

        if ($this->hasRole($user, User::ROLE_DORM_ADMIN)) {
            return $dormId !== null && $dormId === $this->dormId($user)
                && $this->dormMatchesUniversity($user, $dormId);
        }

        return false;
    }

    public function update(User $user, Announcement $announcement): bool
    {
        return $this->view($user, $announcement);
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        return $this->view($user, $announcement);
    }
}
