<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $dormId = $this->user()?->dormAdmin?->dorm_id;
        $roomId = $this->route('room')?->id;
        $roomNumberRules = [$numberRule, 'string', 'max:50'];

        if ($dormId) {
            $uniqueRoom = Rule::unique('rooms', 'room_number')
                ->where('dorm_id', $dormId);

            if ($roomId) {
                $uniqueRoom->ignore($roomId);
            }

            $roomNumberRules[] = $uniqueRoom;
        }

        return [
            'floor_id' => [$floorRule, 'exists:floors,id'],
            'room_number' => $roomNumberRules,
            'capacity' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
