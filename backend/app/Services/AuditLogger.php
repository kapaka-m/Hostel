<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Support\FeatureFlags;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    protected array $ignoredFields = [
        'password',
        'remember_token',
    ];

    public function log(string $action, ?Model $entity = null, ?array $before = null, ?array $after = null): void
    {
        if (!FeatureFlags::enabled('audit_logs')) {
            return;
        }

        $request = app()->bound('request') ? request() : null;
        $actor = Auth::user();
        $universityId = $actor?->university_id;

        if (!$universityId && $entity) {
            if (isset($entity->university_id)) {
                $universityId = $entity->university_id;
            } elseif (method_exists($entity, 'dorm')) {
                $universityId = $entity->dorm?->university_id;
            } elseif (method_exists($entity, 'user')) {
                $universityId = $entity->user?->university_id;
            }
        }

        AuditLog::create([
            'actor_id' => $actor?->id,
            'university_id' => $universityId,
            'action' => $action,
            'entity_type' => $entity ? class_basename($entity) : 'system',
            'entity_id' => $entity?->getKey(),
            'before_json' => $this->filterPayload($before),
            'after_json' => $this->filterPayload($after),
            'ip' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'correlation_id' => $request?->headers->get('X-Correlation-Id'),
            'created_at' => now(),
        ]);
    }

    protected function filterPayload(?array $payload): ?array
    {
        if ($payload === null) {
            return null;
        }

        foreach ($this->ignoredFields as $field) {
            unset($payload[$field]);
        }

        return $payload;
    }
}
