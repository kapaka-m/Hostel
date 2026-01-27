<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use App\Policies\Concerns\ChecksRoles;

class TicketPolicy
{
    use ChecksRoles;

    public function viewAny(User $user): bool
    {
        return $this->hasRole($user, User::ROLE_UNIVERSITY_ADMIN)
            || $this->hasRole($user, User::ROLE_DORM_ADMIN);
    }

    public function view(User $user, Ticket $ticket): bool
    {
        if ($this->isSuper($user)) {
            return true;
        }

        if ($this->hasRole($user, User::ROLE_UNIVERSITY_ADMIN)) {
            return $ticket->university_id === $this->universityId($user);
        }

        if ($this->hasRole($user, User::ROLE_DORM_ADMIN)) {
            return $ticket->dorm_id === $this->dormId($user)
                && $this->dormMatchesUniversity($user, $ticket->dorm_id);
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

    public function update(User $user, Ticket $ticket): bool
    {
        return $this->view($user, $ticket);
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $this->view($user, $ticket);
    }
}
