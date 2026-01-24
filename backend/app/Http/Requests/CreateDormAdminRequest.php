<?php

namespace App\Http\Requests;

use App\Support\FeatureFlags;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class CreateDormAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $dormRule = $this->route('dorm') ? 'sometimes' : 'required';
        $passwordRules = ['required', 'string', 'min:6'];

        if (FeatureFlags::enabled('strong_passwords')) {
            $passwordRules = [
                'required',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
            ];
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => $passwordRules,
            'dorm_id' => [$dormRule, 'exists:dorms,id'],
        ];
    }
}
