<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FloorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $numberRule = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'number' => [$numberRule, 'integer', 'min:1'],
            'bathrooms' => ['sometimes', 'integer', 'min:0'],
            'kitchens' => ['sometimes', 'integer', 'min:0'],
            'showers' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
