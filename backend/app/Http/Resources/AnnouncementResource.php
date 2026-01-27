<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'audience' => $this->audience,
            'status' => $this->status,
            'current_status' => $this->currentStatus(),
            'publish_at' => $this->publish_at,
            'expire_at' => $this->expire_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'dorm' => new DormResource($this->whenLoaded('dorm')),
            'creator' => new UserResource($this->whenLoaded('creator')),
        ];
    }
}
