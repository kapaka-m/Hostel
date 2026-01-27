<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\Dorm;
use App\Models\Student;
use App\Models\User;
use App\Services\RoomAssignmentService;
use App\Support\FeatureFlags;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeIfEnabled('viewAny', Student::class);

        $dormId = $this->requireDormId($request);

        $students = Student::where('dorm_id', $dormId)
            ->with('user')
            ->orderBy('full_name')
            ->get();

        return StudentResource::collection($students);
    }

    public function store(StudentRequest $request)
    {
        $dormId = $this->requireDormId($request);
        $this->authorizeIfEnabled('create', [Student::class, $dormId]);

        $data = $request->validated();

        $password = Str::random(10);
        $dorm = Dorm::findOrFail($dormId);

        $user = User::create([
            'name' => $data['full_name'],
            'email' => $data['email'],
            'password' => Hash::make($password),
            'role' => User::ROLE_STUDENT,
            'university_id' => $dorm->university_id,
        ]);

        if (FeatureFlags::enabled('permissions')) {
            Role::findOrCreate($user->role);
            $user->syncRoles([$user->role]);
        }

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
        $dormId = $this->requireDormId($request);

        if ($student->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('view', $student);

        $student->load('user');

        return new StudentResource($student);
    }

    public function update(StudentRequest $request, Student $student)
    {
        $dormId = $this->requireDormId($request);

        if ($student->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('update', $student);

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
        $dormId = $this->requireDormId($request);

        if ($student->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('delete', $student);

        $service->deactivateStudentAssignment($student);

        $student->user()->delete();

        return response()->json([
            'message' => 'Student deleted successfully.',
            'errors' => [],
        ]);
    }
}
