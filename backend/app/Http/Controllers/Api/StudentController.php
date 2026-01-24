<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use App\Models\User;
use App\Services\RoomAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    protected function dormId(Request $request): int
    {
        $dormId = $request->user()?->dormAdmin?->dorm_id;

        if (!$dormId) {
            abort(403, 'Dorm admin profile missing.');
        }

        return $dormId;
    }

    public function index(Request $request)
    {
        $dormId = $this->dormId($request);

        $students = Student::where('dorm_id', $dormId)
            ->with('user')
            ->orderBy('full_name')
            ->get();

        return StudentResource::collection($students);
    }

    public function store(StudentRequest $request)
    {
        $dormId = $this->dormId($request);
        $data = $request->validated();

        $password = Str::random(10);

        $user = User::create([
            'name' => $data['full_name'],
            'email' => $data['email'],
            'password' => Hash::make($password),
            'role' => User::ROLE_STUDENT,
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'dorm_id' => $dormId,
            'full_name' => $data['full_name'],
            'student_no' => $data['student_no'],
            'phone' => $data['phone'] ?? null,
        ]);

        $student->load('user');

        return response()->json([
            'message' => 'Student created successfully.',
            'errors' => [],
            'student' => new StudentResource($student),
            'generated_password' => $password,
        ], 201);
    }

    public function show(Request $request, Student $student)
    {
        $dormId = $this->dormId($request);

        if ($student->dorm_id !== $dormId) {
            abort(403);
        }

        $student->load('user');

        return new StudentResource($student);
    }

    public function update(StudentRequest $request, Student $student)
    {
        $dormId = $this->dormId($request);

        if ($student->dorm_id !== $dormId) {
            abort(403);
        }

        $data = $request->validated();

        if (array_key_exists('full_name', $data)) {
            $student->full_name = $data['full_name'];
        }

        if (array_key_exists('student_no', $data)) {
            $student->student_no = $data['student_no'];
        }

        if (array_key_exists('phone', $data)) {
            $student->phone = $data['phone'];
        }

        $student->save();

        if (array_key_exists('full_name', $data)) {
            $student->user->name = $student->full_name;
        }

        if (array_key_exists('email', $data)) {
            $student->user->email = $data['email'];
        }

        if (array_key_exists('full_name', $data) || array_key_exists('email', $data)) {
            $student->user->save();
        }

        $student->load('user');

        return new StudentResource($student);
    }

    public function destroy(Request $request, Student $student, RoomAssignmentService $service)
    {
        $dormId = $this->dormId($request);

        if ($student->dorm_id !== $dormId) {
            abort(403);
        }

        $service->deactivateStudentAssignment($student);

        $student->user()->delete();

        return response()->json([
            'message' => 'Student deleted successfully.',
            'errors' => [],
        ]);
    }
}
