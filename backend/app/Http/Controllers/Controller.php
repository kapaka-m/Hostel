<?php

namespace App\Http\Controllers;

use App\Support\FeatureFlags;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    use AuthorizesRequests;

    protected function authorizeIfEnabled(string $ability, mixed $arguments = []): void
    {
        if (!FeatureFlags::enabled('permissions')) {
            return;
        }

        $this->authorize($ability, $arguments);
    }
}
