<?php

namespace App\Observers;

use App\Models\RecordHistory;
use App\Services\AuditLogger;
use App\Support\FeatureFlags;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AuditObserver
{
    protected array $ignoredFields = [
        'created_at',
        'updated_at',
        'password',
        'remember_token',
    ];

    public function created(Model $model): void
    {
        $this->logger()->log(
            $this->actionName($model, 'created'),
            $model,
            null,
            $this->filterAttributes($model->getAttributes())
        );
    }

    public function updated(Model $model): void
    {
        $changes = $this->extractChanges($model);

        if (!$changes) {
            return;
        }

        $this->logger()->log(
            $this->actionName($model, 'updated'),
            $model,
            $changes['before'],
            $changes['after']
        );

        if (FeatureFlags::enabled('audit_logs')) {
            RecordHistory::create([
                'entity_type' => class_basename($model),
                'entity_id' => $model->getKey(),
                'changes_json' => $changes,
                'actor_id' => auth()->id(),
                'created_at' => now(),
            ]);
        }
    }

    public function deleted(Model $model): void
    {
        $this->logger()->log(
            $this->actionName($model, 'deleted'),
            $model,
            $this->filterAttributes($model->getAttributes()),
            null
        );
    }

    protected function logger(): AuditLogger
    {
        return app(AuditLogger::class);
    }

    protected function actionName(Model $model, string $suffix): string
    {
        return Str::snake(class_basename($model)) . '.' . $suffix;
    }

    protected function extractChanges(Model $model): ?array
    {
        $changes = $model->getChanges();
        $before = [];
        $after = [];

        foreach ($changes as $key => $value) {
            if (in_array($key, $this->ignoredFields, true)) {
                continue;
            }

            $before[$key] = $model->getOriginal($key);
            $after[$key] = $value;
        }

        if (!$after) {
            return null;
        }

        return [
            'before' => $before,
            'after' => $after,
        ];
    }

    protected function filterAttributes(array $attributes): array
    {
        foreach ($this->ignoredFields as $field) {
            unset($attributes[$field]);
        }

        return $attributes;
    }
}
