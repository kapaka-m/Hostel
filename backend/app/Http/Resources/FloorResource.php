<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FloorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'dorm_id' => $this->dorm_id,
            'number' => $this->number,
            'bathrooms' => $this->bathrooms,
            'kitchens' => $this->kitchens,
            'showers' => $this->showers,
        ];
    }
}
