<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'dorm_id' => $this->dorm_id,
            'full_name' => $this->full_name,
            'student_no' => $this->student_no,
            'phone' => $this->phone,
            'email' => $this->user?->email,
        ];
    }
}
