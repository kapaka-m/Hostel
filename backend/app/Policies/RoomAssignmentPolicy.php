<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\User;
use App\Policies\Concerns\ChecksRoles;

class RoomAssignmentPolicy
{
    use ChecksRoles;

    public function viewAny(User $user): bool
    {
        return $this->hasRole($user, User::ROLE_UNIVERSITY_ADMIN)
            || $this->hasRole($user, User::ROLE_DORM_ADMIN);
    }

    public function view(User $user, RoomAssignment $assignment): bool
    {
        $dormId = $assignment->room?->dorm_id
            ?? Room::where('id', $assignment->room_id)->value('dorm_id');

        if ($this->isSuper($user)) {
            return true;
        }

        if ($this->hasRole($user, User::ROLE_UNIVERSITY_ADMIN)) {
            return $this->dormMatchesUniversity($user, $dormId);
        }

        if ($this->hasRole($user, User::ROLE_DORM_ADMIN)) {
            return $this->dormId($user) === $dormId
                && $this->dormMatchesUniversity($user, $dormId);
        }

        return false;
    }

    public function update(User $user, RoomAssignment $assignment): bool
    {
        return $this->view($user, $assignment);
    }
}
