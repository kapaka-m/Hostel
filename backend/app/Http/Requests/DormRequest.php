<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $dorm = $this->route('dorm');
        $dormId = $dorm?->id;
        $universityId = $this->user()?->university_id;

        $codeRule = Rule::unique('dorms', 'code')
            ->where(fn($query) => $query->where('university_id', $universityId));

        if ($dormId) {
            $codeRule->ignore($dormId);
        }

        $requiredRule = $this->isMethod('post') ? 'required' : 'sometimes';
        $statusRule = $this->isMethod('post') ? 'sometimes' : 'sometimes';

        return [
            'name' => [$requiredRule, 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', $codeRule],
            'address' => ['nullable', 'string', 'max:255'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'status' => [$statusRule, 'in:ACTIVE,INACTIVE'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
