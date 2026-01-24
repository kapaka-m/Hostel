<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $floorRule = $this->isMethod('post') ? 'required' : 'sometimes';
        $numberRule = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'floor_id' => [$floorRule, 'exists:floors,id'],
            'room_number' => [$numberRule, 'string', 'max:50'],
            'capacity' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
