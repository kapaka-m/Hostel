<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $student = $this->route('student');
        $studentId = $student?->id;
        $userId = $student?->user_id;

        $emailRule = $userId ? 'unique:users,email,' . $userId : 'unique:users,email';
        $studentNoRule = $studentId ? 'unique:students,student_no,' . $studentId : 'unique:students,student_no';

        $requiredRule = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'full_name' => [$requiredRule, 'string', 'max:255'],
            'student_no' => [$requiredRule, 'string', 'max:50', $studentNoRule],
            'email' => [$requiredRule, 'email', 'max:255', $emailRule],
            'phone' => ['nullable', 'string', 'max:50'],
        ];
    }
}
