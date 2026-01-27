<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Models\Dorm;
use App\Models\RoomAssignment;
use App\Models\Student;
use App\Models\User;
use App\Models\UserFreezeLog;
use App\Services\RoomAssignmentService;
use App\Support\FeatureFlags;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DormStudentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeIfEnabled('viewAny', Student::class);

        $dormId = $this->requireDormId($request);

        $query = Student::where('dorm_id', $dormId)
            ->with(['user', 'activeAssignment.room']);

        if ($request->filled('status') && $request->input('status') === 'archived') {
            $query->onlyTrashed();
        }

        if ($request->filled('q')) {
            $term = $request->input('q');
            $query->where(function ($builder) use ($term) {
                $builder->where('full_name', 'like', '%' . $term . '%')
                    ->orWhere('student_no', 'like', '%' . $term . '%')
                    ->orWhereHas('user', function ($userQuery) use ($term) {
                        $userQuery->where('email', 'like', '%' . $term . '%');
                    });
            });
        }

        if ($request->filled('assignment')) {
            if ($request->input('assignment') === 'assigned') {
                $query->whereHas('activeAssignment');
            } elseif ($request->input('assignment') === 'unassigned') {
                $query->whereDoesntHave('activeAssignment');
            }
        }

        $students = $query->orderBy('full_name')->paginate(20)->withQueryString();

        return view('admin.dorm.students.index', [
            'students' => $students,
            'filters' => [
                'q' => $request->input('q', ''),
                'assignment' => $request->input('assignment', ''),
                'status' => $request->input('status', ''),
            ],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorizeIfEnabled('viewAny', Student::class);

        $dormId = $this->requireDormId($request);

        $query = Student::where('dorm_id', $dormId)
            ->with(['user', 'activeAssignment.room']);

        if ($request->filled('status') && $request->input('status') === 'archived') {
            $query->onlyTrashed();
        }

        if ($request->filled('q')) {
            $term = $request->input('q');
            $query->where(function ($builder) use ($term) {
                $builder->where('full_name', 'like', '%' . $term . '%')
                    ->orWhere('student_no', 'like', '%' . $term . '%')
                    ->orWhereHas('user', function ($userQuery) use ($term) {
                        $userQuery->where('email', 'like', '%' . $term . '%');
                    });
            });
        }

        if ($request->filled('assignment')) {
            if ($request->input('assignment') === 'assigned') {
                $query->whereHas('activeAssignment');
            } elseif ($request->input('assignment') === 'unassigned') {
                $query->whereDoesntHave('activeAssignment');
            }
        }

        $filename = 'students_export_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['full_name', 'student_no', 'email', 'phone', 'room', 'status']);

            $query->orderBy('full_name')->chunk(200, function ($students) use ($handle) {
                foreach ($students as $student) {
                    fputcsv($handle, [
                        $student->full_name,
                        $student->student_no,
                        $student->user?->email,
                        $student->phone,
                        $student->activeAssignment?->room?->room_number,
                        $student->trashed() ? 'ARCHIVED' : 'ACTIVE',
                    ]);
                }
            });

            fclose($handle);
        }, $filename);
    }

    public function create()
    {
        $this->authorizeIfEnabled('create', Student::class);

        return view('admin.dorm.students.form', [
            'student' => new Student,
        ]);
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

        Student::create([
            'user_id' => $user->id,
            'dorm_id' => $dormId,
            'full_name' => $data['full_name'],
            'student_no' => $data['student_no'],
            'phone' => $data['phone'] ?? null,
        ]);

        return redirect()->route('admin.dorm.students.index')
            ->with('success', 'Student created successfully.')
            ->with('generated_password', $password);
    }

    public function edit(Request $request, Student $student)
    {
        $dormId = $this->requireDormId($request);

        if ($student->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('view', $student);

        return view('admin.dorm.students.form', [
            'student' => $student,
        ]);
    }

    public function show(Request $request, Student $student)
    {
        $dormId = $this->requireDormId($request);

        if ($student->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('view', $student);

        $student->load(['user', 'activeAssignment.room.floor']);

        $assignments = RoomAssignment::where('student_id', $student->id)
            ->with(['room.floor'])
            ->orderByDesc('from_date')
            ->get();

        return view('admin.dorm.students.show', [
            'student' => $student,
            'assignments' => $assignments,
        ]);
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

        return redirect()->route('admin.dorm.students.index')
            ->with('success', 'Student updated successfully.');
    }

    public function destroy(Request $request, Student $student, RoomAssignmentService $service)
    {
        $dormId = $this->requireDormId($request);

        if ($student->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('delete', $student);

        $service->deactivateStudentAssignment($student);

        if ($student->user) {
            $student->user->is_active = false;
            $student->user->frozen_at = now();
            $student->user->save();

            UserFreezeLog::create([
                'user_id' => $student->user->id,
                'actor_id' => $request->user()->id,
                'action' => 'freeze',
                'reason' => 'Archived student record.',
            ]);
        }

        $student->delete();

        return redirect()->route('admin.dorm.students.index')
            ->with('success', 'Student archived successfully.');
    }

    public function restore(Request $request, Student $student)
    {
        $dormId = $this->requireDormId($request);

        if ($student->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('update', $student);

        $student->restore();

        if ($student->user) {
            $student->user->is_active = true;
            $student->user->frozen_at = null;
            $student->user->save();

            UserFreezeLog::create([
                'user_id' => $student->user->id,
                'actor_id' => $request->user()->id,
                'action' => 'unfreeze',
                'reason' => 'Restored student record.',
            ]);
        }

        return redirect()->route('admin.dorm.students.index', [
            'status' => 'archived',
        ])->with('success', 'Student restored successfully.');
    }

    public function unassign(Request $request, Student $student, RoomAssignmentService $service)
    {
        $dormId = $this->requireDormId($request);

        if ($student->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('update', $student);

        if (!$student->activeAssignment) {
            return redirect()->route('admin.dorm.students.show', $student)
                ->with('error', 'Student has no active assignment.');
        }

        $service->deactivateStudentAssignment($student);

        return redirect()->route('admin.dorm.students.show', $student)
            ->with('success', 'Student unassigned successfully.');
    }

    public function freeze(Request $request, Student $student)
    {
        $dormId = $this->requireDormId($request);

        if ($student->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('update', $student);

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        if ($student->user) {
            $student->user->is_active = false;
            $student->user->frozen_at = now();
            $student->user->save();

            UserFreezeLog::create([
                'user_id' => $student->user->id,
                'actor_id' => $request->user()->id,
                'action' => 'freeze',
                'reason' => $data['reason'] ?? null,
            ]);
        }

        return redirect()->route('admin.dorm.students.show', $student)
            ->with('success', 'Student account frozen successfully.');
    }

    public function unfreeze(Request $request, Student $student)
    {
        $dormId = $this->requireDormId($request);

        if ($student->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('update', $student);

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        if ($student->user) {
            $student->user->is_active = true;
            $student->user->frozen_at = null;
            $student->user->save();

            UserFreezeLog::create([
                'user_id' => $student->user->id,
                'actor_id' => $request->user()->id,
                'action' => 'unfreeze',
                'reason' => $data['reason'] ?? null,
            ]);
        }

        return redirect()->route('admin.dorm.students.show', $student)
            ->with('success', 'Student account reactivated successfully.');
    }

    public function bulkDelete(Request $request, RoomAssignmentService $service)
    {
        $this->authorizeIfEnabled('viewAny', Student::class);

        $dormId = $this->requireDormId($request);
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:students,id'],
        ]);

        $students = Student::where('dorm_id', $dormId)
            ->whereIn('id', $data['ids'])
            ->with('user')
            ->get();

        if ($students->isEmpty()) {
            return redirect()->route('admin.dorm.students.index')
                ->with('error', 'No students selected.');
        }

        foreach ($students as $student) {
            $this->authorizeIfEnabled('delete', $student);
        }

        foreach ($students as $student) {
            $service->deactivateStudentAssignment($student);

            if ($student->user) {
                $student->user->is_active = false;
                $student->user->frozen_at = now();
                $student->user->save();

                UserFreezeLog::create([
                    'user_id' => $student->user->id,
                    'actor_id' => $request->user()->id,
                    'action' => 'freeze',
                    'reason' => 'Archived via bulk action.',
                ]);
            }

            $student->delete();
        }

        return redirect()->route('admin.dorm.students.index')
            ->with('success', 'Selected students archived successfully.');
    }

    public function bulkRestore(Request $request)
    {
        $this->authorizeIfEnabled('viewAny', Student::class);

        $dormId = $this->requireDormId($request);
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:students,id'],
        ]);

        $students = Student::onlyTrashed()
            ->where('dorm_id', $dormId)
            ->whereIn('id', $data['ids'])
            ->with('user')
            ->get();

        if ($students->isEmpty()) {
            return redirect()->route('admin.dorm.students.index', ['status' => 'archived'])
                ->with('error', 'No archived students selected.');
        }

        foreach ($students as $student) {
            $this->authorizeIfEnabled('update', $student);
        }

        foreach ($students as $student) {
            $student->restore();

            if ($student->user) {
                $student->user->is_active = true;
                $student->user->frozen_at = null;
                $student->user->save();

                UserFreezeLog::create([
                    'user_id' => $student->user->id,
                    'actor_id' => $request->user()->id,
                    'action' => 'unfreeze',
                    'reason' => 'Restored via bulk action.',
                ]);
            }
        }

        return redirect()->route('admin.dorm.students.index', ['status' => 'archived'])
            ->with('success', 'Selected students restored successfully.');
    }

    public function bulkFreeze(Request $request)
    {
        $this->authorizeIfEnabled('viewAny', Student::class);

        $dormId = $this->requireDormId($request);
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:students,id'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $students = Student::where('dorm_id', $dormId)
            ->whereIn('id', $data['ids'])
            ->with('user')
            ->get();

        if ($students->isEmpty()) {
            return redirect()->route('admin.dorm.students.index')
                ->with('error', 'No students selected.');
        }

        foreach ($students as $student) {
            $this->authorizeIfEnabled('update', $student);
        }

        foreach ($students as $student) {
            if ($student->user) {
                $student->user->is_active = false;
                $student->user->frozen_at = now();
                $student->user->save();

                UserFreezeLog::create([
                    'user_id' => $student->user->id,
                    'actor_id' => $request->user()->id,
                    'action' => 'freeze',
                    'reason' => $data['reason'] ?? null,
                ]);
            }
        }

        return redirect()->route('admin.dorm.students.index')
            ->with('success', 'Selected students frozen successfully.');
    }

    public function bulkUnfreeze(Request $request)
    {
        $this->authorizeIfEnabled('viewAny', Student::class);

        $dormId = $this->requireDormId($request);
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:students,id'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $students = Student::where('dorm_id', $dormId)
            ->whereIn('id', $data['ids'])
            ->with('user')
            ->get();

        if ($students->isEmpty()) {
            return redirect()->route('admin.dorm.students.index')
                ->with('error', 'No students selected.');
        }

        foreach ($students as $student) {
            $this->authorizeIfEnabled('update', $student);
        }

        foreach ($students as $student) {
            if ($student->user) {
                $student->user->is_active = true;
                $student->user->frozen_at = null;
                $student->user->save();

                UserFreezeLog::create([
                    'user_id' => $student->user->id,
                    'actor_id' => $request->user()->id,
                    'action' => 'unfreeze',
                    'reason' => $data['reason'] ?? null,
                ]);
            }
        }

        return redirect()->route('admin.dorm.students.index')
            ->with('success', 'Selected students reactivated successfully.');
    }

    public function bulkExport(Request $request): StreamedResponse
    {
        $this->authorizeIfEnabled('viewAny', Student::class);

        $dormId = $this->requireDormId($request);
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:students,id'],
        ]);

        $query = Student::withTrashed()
            ->where('dorm_id', $dormId)
            ->whereIn('id', $data['ids'])
            ->with(['user', 'activeAssignment.room']);

        $filename = 'students_export_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['full_name', 'student_no', 'email', 'phone', 'room', 'status', 'archived']);

            $query->orderBy('full_name')->chunk(200, function ($students) use ($handle) {
                foreach ($students as $student) {
                    fputcsv($handle, [
                        $student->full_name,
                        $student->student_no,
                        $student->user?->email,
                        $student->phone,
                        $student->activeAssignment?->room?->room_number,
                        $student->user?->is_active ? 'ACTIVE' : 'INACTIVE',
                        $student->trashed() ? 'YES' : 'NO',
                    ]);
                }
            });

            fclose($handle);
        }, $filename);
    }
}
