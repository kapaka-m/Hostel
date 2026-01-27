<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;
use App\Policies\Concerns\ChecksRoles;

class RoomPolicy
{
    use ChecksRoles;

    public function viewAny(User $user): bool
    {
        return $this->hasRole($user, User::ROLE_UNIVERSITY_ADMIN)
            || $this->hasRole($user, User::ROLE_DORM_ADMIN);
    }

    public function view(User $user, Room $room): bool
    {
        if ($this->isSuper($user)) {
            return true;
        }

        if ($this->hasRole($user, User::ROLE_UNIVERSITY_ADMIN)) {
            return $this->dormMatchesUniversity($user, $room->dorm_id);
        }

        if ($this->hasRole($user, User::ROLE_DORM_ADMIN)) {
            return $this->dormId($user) === $room->dorm_id
                && $this->dormMatchesUniversity($user, $room->dorm_id);
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

    public function update(User $user, Room $room): bool
    {
        return $this->view($user, $room);
    }

    public function delete(User $user, Room $room): bool
    {
        return $this->view($user, $room);
    }

    public function assignStudent(User $user, Room $room): bool
    {
        return $this->view($user, $room);
    }

    public function viewOccupants(User $user, Room $room): bool
    {
        return $this->view($user, $room);
    }
}
