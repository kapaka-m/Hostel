<?php

namespace App\Services;

use App\Models\Dorm;
use App\Models\Student;
use App\Models\User;
use App\Support\FeatureFlags;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class StudentImportService
{
    public function import(UploadedFile $file, int $universityId, ?int $defaultDormId = null): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return [
                'created' => [],
                'errors' => [['row' => 0, 'messages' => ['Unable to read the uploaded file.']]],
            ];
        }

        $header = fgetcsv($handle);

        if (!$header) {
            fclose($handle);

            return [
                'created' => [],
                'errors' => [['row' => 0, 'messages' => ['The file is empty or missing headers.']]],
            ];
        }

        $columns = array_map(fn($value) => strtolower(trim((string) $value)), $header);
        $index = array_flip($columns);

        $created = [];
        $errors = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            $payload = [
                'full_name' => $this->columnValue($row, $index, 'full_name'),
                'student_no' => $this->columnValue($row, $index, 'student_no'),
                'email' => $this->columnValue($row, $index, 'email'),
                'phone' => $this->columnValue($row, $index, 'phone'),
                'dorm_code' => $this->columnValue($row, $index, 'dorm_code'),
            ];

            $rules = [
                'full_name' => ['required', 'string', 'max:255'],
                'student_no' => ['required', 'string', 'max:50', Rule::unique('students', 'student_no')],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
                'phone' => ['nullable', 'string', 'max:50'],
            ];

            if (!$defaultDormId && !$payload['dorm_code']) {
                $rules['dorm_code'] = ['required', 'string', 'max:50'];
            }

            $validator = Validator::make($payload, $rules);

            if ($validator->fails()) {
                $errors[] = [
                    'row' => $rowNumber,
                    'messages' => $validator->errors()->all(),
                ];

                continue;
            }

            $dormId = $defaultDormId;

            if (!$dormId) {
                $dorm = Dorm::where('university_id', $universityId)
                    ->where('code', $payload['dorm_code'])
                    ->first();

                if (!$dorm) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'messages' => ["Dorm code '{$payload['dorm_code']}' not found."],
                    ];

                    continue;
                }

                $dormId = $dorm->id;
            }

            $password = Str::random(10);

            $user = User::create([
                'name' => $payload['full_name'],
                'email' => $payload['email'],
                'password' => Hash::make($password),
                'role' => User::ROLE_STUDENT,
                'university_id' => $universityId,
            ]);

            if (FeatureFlags::enabled('permissions')) {
                Role::findOrCreate($user->role);
                $user->syncRoles([$user->role]);
            }

            $student = Student::create([
                'user_id' => $user->id,
                'dorm_id' => $dormId,
                'full_name' => $payload['full_name'],
                'student_no' => $payload['student_no'],
                'phone' => $payload['phone'] ?: null,
            ]);

            $created[] = [
                'row' => $rowNumber,
                'student_no' => $student->student_no,
                'email' => $user->email,
                'generated_password' => $password,
            ];
        }

        fclose($handle);

        return [
            'created' => $created,
            'errors' => $errors,
        ];
    }

    private function columnValue(array $row, array $index, string $key): ?string
    {
        if (!array_key_exists($key, $index)) {
            return null;
        }

        $value = $row[$index[$key]] ?? null;

        return $value !== null ? trim((string) $value) : null;
    }
}
