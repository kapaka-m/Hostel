<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StudentImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if (!$user) {
            return false;
        }

        $allowedRoles = [
            User::ROLE_UNIVERSITY_ADMIN,
            User::ROLE_SUPER_ADMIN,
        ];

        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole($allowedRoles)) {
            return true;
        }

        return in_array($user->role, $allowedRoles, true);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:5120',
                'mimes:csv,txt',
                'mimetypes:text/plain,text/csv,application/csv,application/vnd.ms-excel',
            ],
            'default_dorm_id' => ['nullable', 'exists:dorms,id'],
        ];
    }
}
