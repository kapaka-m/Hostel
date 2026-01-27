<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;

class AdminMenu
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function sections(?User $user): array
    {
        if (!$user) {
            return [];
        }

        $sections = config('admin-menu.sections', []);

        $filtered = [];

        foreach ($sections as $section) {
            if (!self::sectionVisible($section, $user)) {
                continue;
            }

            $items = Arr::get($section, 'items', []);
            $items = array_values(array_filter($items, fn($item) => self::itemVisible($item, $user)));

            if ($items === []) {
                continue;
            }

            $section['items'] = $items;
            $filtered[] = $section;
        }

        return $filtered;
    }

    protected static function sectionVisible(array $section, User $user): bool
    {
        $roles = Arr::get($section, 'roles');

        if (!$roles) {
            return true;
        }

        return in_array($user->role, $roles, true);
    }

    protected static function itemVisible(array $item, User $user): bool
    {
        $roles = Arr::get($item, 'roles');

        if ($roles && !in_array($user->role, $roles, true)) {
            return false;
        }

        $feature = Arr::get($item, 'feature');

        if ($feature && !FeatureFlags::enabled($feature)) {
            return false;
        }

        $routeName = Arr::get($item, 'route');

        if ($routeName && !Route::has($routeName)) {
            return false;
        }

        return true;
    }
}
