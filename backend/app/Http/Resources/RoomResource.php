<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $occupancy = $this->active_assignments_count ?? $this->activeAssignments()->count();

        return [
            'id' => $this->id,
            'dorm_id' => $this->dorm_id,
            'floor_id' => $this->floor_id,
            'room_number' => $this->room_number,
            'capacity' => $this->capacity,
            'status' => $this->status,
            'occupancy' => $occupancy,
        ];
    }
}
