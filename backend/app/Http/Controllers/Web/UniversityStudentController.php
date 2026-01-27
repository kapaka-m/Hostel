<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentImportRequest;
use App\Models\Dorm;
use App\Models\Student;
use App\Services\StudentImportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UniversityStudentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeIfEnabled('viewAny', Student::class);

        $universityId = $this->requireUniversityId($request);

        $query = Student::query()
            ->with(['user', 'dorm'])
            ->whereHas('dorm', function ($builder) use ($universityId) {
                $builder->where('university_id', $universityId);
            });

        if ($request->filled('q')) {
            $term = $request->string('q')->toString();
            $query->where(function ($builder) use ($term) {
                $builder->where('full_name', 'like', '%' . $term . '%')
                    ->orWhere('student_no', 'like', '%' . $term . '%')
                    ->orWhereHas('user', function ($inner) use ($term) {
                        $inner->where('email', 'like', '%' . $term . '%');
                    })
                    ->orWhereHas('dorm', function ($inner) use ($term) {
                        $inner->where('name', 'like', '%' . $term . '%');
                    });
            });
        }

        if ($request->filled('dorm_id')) {
            $query->where('dorm_id', $request->integer('dorm_id'));
        }

        if ($request->filled('status')) {
            $query->whereHas('user', function ($builder) use ($request) {
                $builder->where('is_active', $request->input('status') === 'ACTIVE');
            });
        }

        $students = $query->orderBy('full_name')->paginate(20)->withQueryString();

        $dorms = Dorm::where('university_id', $universityId)->orderBy('name')->get();

        return view('admin.university.students.index', [
            'students' => $students,
            'dorms' => $dorms,
            'filters' => [
                'q' => $request->input('q', ''),
                'dorm_id' => $request->input('dorm_id', ''),
                'status' => $request->input('status', ''),
            ],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorizeIfEnabled('viewAny', Student::class);

        $universityId = $this->requireUniversityId($request);

        $query = Student::query()
            ->with(['user', 'dorm'])
            ->whereHas('dorm', function ($builder) use ($universityId) {
                $builder->where('university_id', $universityId);
            });

        if ($request->filled('q')) {
            $term = $request->string('q')->toString();
            $query->where(function ($builder) use ($term) {
                $builder->where('full_name', 'like', '%' . $term . '%')
                    ->orWhere('student_no', 'like', '%' . $term . '%')
                    ->orWhereHas('user', function ($inner) use ($term) {
                        $inner->where('email', 'like', '%' . $term . '%');
                    });
            });
        }

        if ($request->filled('dorm_id')) {
            $query->where('dorm_id', $request->integer('dorm_id'));
        }

        if ($request->filled('status')) {
            $query->whereHas('user', function ($builder) use ($request) {
                $builder->where('is_active', $request->input('status') === 'ACTIVE');
            });
        }

        $filename = 'students_export_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['full_name', 'student_no', 'email', 'phone', 'dorm', 'status']);

            $query->orderBy('full_name')->chunk(200, function ($students) use ($handle) {
                foreach ($students as $student) {
                    fputcsv($handle, [
                        $student->full_name,
                        $student->student_no,
                        $student->user?->email,
                        $student->phone,
                        $student->dorm?->name,
                        $student->user?->is_active ? 'ACTIVE' : 'INACTIVE',
                    ]);
                }
            });

            fclose($handle);
        }, $filename);
    }

    public function import(Request $request)
    {
        $this->authorizeIfEnabled('viewAny', Student::class);

        $universityId = $this->requireUniversityId($request);
        $dorms = Dorm::where('university_id', $universityId)->orderBy('name')->get();

        return view('admin.university.students.import', [
            'dorms' => $dorms,
        ]);
    }

    public function storeImport(StudentImportRequest $request, StudentImportService $service)
    {
        $this->authorizeIfEnabled('viewAny', Student::class);

        $universityId = $this->requireUniversityId($request);
        $defaultDormId = $request->input('default_dorm_id') ? $request->integer('default_dorm_id') : null;

        if ($defaultDormId) {
            Dorm::where('id', $defaultDormId)
                ->where('university_id', $universityId)
                ->firstOrFail();
        }

        $result = $service->import($request->file('file'), $universityId, $defaultDormId);

        return view('admin.university.students.import-result', [
            'created' => $result['created'],
            'importErrors' => $result['errors'],
        ]);
    }
}
