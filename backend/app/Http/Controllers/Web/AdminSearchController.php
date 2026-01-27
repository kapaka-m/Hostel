<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Dorm;
use App\Models\Room;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class AdminSearchController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->get('q', ''));
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        $isUniversity = $user->role === User::ROLE_UNIVERSITY_ADMIN;
        $isDorm = $user->role === User::ROLE_DORM_ADMIN;

        $dormId = $isDorm ? $user->dormAdmin?->dorm_id : null;
        $universityId = $isUniversity ? $this->requireUniversityId($request) : $user->university_id;

        if ($isDorm && !$dormId) {
            abort(403, 'Dorm admin profile missing.');
        }

        if ($query === '' || strlen($query) < 2) {
            return view('admin.search.index', [
                'query' => $query,
                'dorms' => collect(),
                'rooms' => collect(),
                'students' => collect(),
            ]);
        }

        $dorms = Dorm::query()
            ->when($isDorm, fn($builder) => $builder->where('id', $dormId))
            ->when($isUniversity && $universityId, fn($builder) => $builder->where('university_id', $universityId))
            ->where(function ($builder) use ($query) {
                $builder->where('name', 'like', '%' . $query . '%')
                    ->orWhere('address', 'like', '%' . $query . '%');
            })
            ->orderBy('name')
            ->limit(10)
            ->get();

        $rooms = Room::query()
            ->with('floor')
            ->withCount('activeAssignments')
            ->when($isDorm, fn($builder) => $builder->where('dorm_id', $dormId))
            ->when($isUniversity && $universityId, function ($builder) use ($universityId) {
                $builder->whereHas('dorm', fn($inner) => $inner->where('university_id', $universityId));
            })
            ->where('room_number', 'like', '%' . $query . '%')
            ->orderBy('room_number')
            ->limit(10)
            ->get();

        $students = Student::query()
            ->with('user')
            ->when($isDorm, fn($builder) => $builder->where('dorm_id', $dormId))
            ->when($isUniversity && $universityId, function ($builder) use ($universityId) {
                $builder->whereHas('dorm', fn($inner) => $inner->where('university_id', $universityId));
            })
            ->where(function ($builder) use ($query) {
                $builder->where('full_name', 'like', '%' . $query . '%')
                    ->orWhere('student_no', 'like', '%' . $query . '%')
                    ->orWhereHas('user', function ($inner) use ($query) {
                        $inner->where('email', 'like', '%' . $query . '%');
                    });
            })
            ->orderBy('full_name')
            ->limit(10)
            ->get();

        return view('admin.search.index', [
            'query' => $query,
            'dorms' => $dorms,
            'rooms' => $rooms,
            'students' => $students,
        ]);
    }
}
