<?php

namespace App\Http\Controllers;

use App\Models\Dorm;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

abstract class Controller
{
    use AuthorizesRequests;

    protected function authorizeIfEnabled(string $ability, mixed $arguments = []): void
    {
        $this->authorize($ability, $arguments);
    }

    protected function requireUniversityId(Request $request): int
    {
        $universityId = $request->user()?->university_id;

        if (!$universityId) {
            abort(403, 'University context missing.');
        }

        return $universityId;
    }

    protected function requireDormId(Request $request): int
    {
        $dormId = $request->user()?->dormAdmin?->dorm_id;

        if (!$dormId) {
            abort(403, 'Dorm admin profile missing.');
        }

        $universityId = $request->user()?->university_id;

        if ($universityId) {
            $belongs = Dorm::query()
                ->where('id', $dormId)
                ->where('university_id', $universityId)
                ->exists();

            if (!$belongs) {
                abort(403, 'Dorm not in university scope.');
            }
        }

        return $dormId;
    }
}
