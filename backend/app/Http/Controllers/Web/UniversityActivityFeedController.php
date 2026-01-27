<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class UniversityActivityFeedController extends Controller
{
    public function index(Request $request)
    {
        $universityId = $this->requireUniversityId($request);

        $query = AuditLog::with('actor')
            ->where('university_id', $universityId);

        if ($request->filled('q')) {
            $term = $request->input('q');
            $query->where(function ($builder) use ($term) {
                $builder->where('action', 'like', '%' . $term . '%')
                    ->orWhere('entity_type', 'like', '%' . $term . '%')
                    ->orWhereHas('actor', function ($inner) use ($term) {
                        $inner->where('name', 'like', '%' . $term . '%')
                            ->orWhere('email', 'like', '%' . $term . '%');
                    });
            });
        }

        $logs = $query->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return view('admin.university.activity-feed', [
            'logs' => $logs,
        ]);
    }

    public function data(Request $request)
    {
        $universityId = $this->requireUniversityId($request);

        $query = AuditLog::with('actor')
            ->where('university_id', $universityId);

        if ($request->filled('q')) {
            $term = $request->input('q');
            $query->where(function ($builder) use ($term) {
                $builder->where('action', 'like', '%' . $term . '%')
                    ->orWhere('entity_type', 'like', '%' . $term . '%')
                    ->orWhereHas('actor', function ($inner) use ($term) {
                        $inner->where('name', 'like', '%' . $term . '%')
                            ->orWhere('email', 'like', '%' . $term . '%');
                    });
            });
        }

        $logs = $query->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return AuditLogResource::collection($logs);
    }
}
