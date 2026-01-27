<?php

namespace App\Http\Requests;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $requiredRule = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'subject' => [$requiredRule, 'string', 'max:200'],
            'description' => [$requiredRule, 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'priority' => ['nullable', Rule::in(Ticket::PRIORITIES)],
            'status' => ['sometimes', Rule::in(Ticket::STATUSES)],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'dorm_id' => ['nullable', 'integer', 'exists:dorms,id'],
        ];
    }
}
