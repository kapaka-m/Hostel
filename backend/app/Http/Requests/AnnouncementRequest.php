<?php

namespace App\Http\Requests;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $requiredRule = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'title' => [$requiredRule, 'string', 'max:200'],
            'body' => [$requiredRule, 'string'],
            'audience' => [$requiredRule, Rule::in(Announcement::AUDIENCES)],
            'status' => ['sometimes', Rule::in(Announcement::STATUSES)],
            'dorm_id' => ['nullable', 'integer', 'exists:dorms,id', 'required_if:audience,DORM'],
            'publish_at' => ['nullable', 'date'],
            'expire_at' => ['nullable', 'date', 'after:publish_at'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $user = $this->user();

        if ($user?->role === User::ROLE_DORM_ADMIN) {
            $this->merge([
                'audience' => 'DORM',
                'dorm_id' => $user->dormAdmin?->dorm_id,
            ]);
        }
    }
}
