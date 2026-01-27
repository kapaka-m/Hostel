<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    public function notifyUser(?User $user, string $title, ?string $body = null, ?string $link = null, ?string $type = null): void
    {
        if (!$user) {
            return;
        }

        AdminNotification::create([
            'user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'link' => $link,
            'type' => $type,
        ]);
    }

    public function notifyUsers(iterable $users, string $title, ?string $body = null, ?string $link = null, ?string $type = null): void
    {
        $collection = $users instanceof Collection ? $users : collect($users);

        $collection->each(function ($user) use ($title, $body, $link, $type) {
            if ($user instanceof User) {
                $this->notifyUser($user, $title, $body, $link, $type);
            }
        });
    }
}
