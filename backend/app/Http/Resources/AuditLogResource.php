<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'actor' => $this->whenLoaded('actor', function () {
                return [
                    'id' => $this->actor?->id,
                    'name' => $this->actor?->name,
                    'email' => $this->actor?->email,
                    'role' => $this->actor?->role,
                ];
            }),
            'action' => $this->action,
            'entity_type' => $this->entity_type,
            'entity_id' => $this->entity_id,
            'before' => $this->before_json,
            'after' => $this->after_json,
            'ip' => $this->ip,
            'user_agent' => $this->user_agent,
            'correlation_id' => $this->correlation_id,
            'created_at' => optional($this->created_at)->toIso8601String(),
        ];
    }
}
