<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class UniversityAuditLogController extends Controller
{
    public function index(Request $request)
    {
        $universityId = $this->requireUniversityId($request);

        $query = AuditLog::with('actor')
            ->where('university_id', $universityId);

        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->input('action') . '%');
        }

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->input('entity_type'));
        }

        if ($request->filled('entity_id')) {
            $query->where('entity_id', $request->input('entity_id'));
        }

        if ($request->filled('actor')) {
            $term = $request->input('actor');
            $query->whereHas('actor', function ($builder) use ($term) {
                $builder->where('name', 'like', '%' . $term . '%')
                    ->orWhere('email', 'like', '%' . $term . '%');
            });
        }

        if ($request->filled('correlation_id')) {
            $query->where('correlation_id', 'like', '%' . $request->input('correlation_id') . '%');
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        $logs = $query->orderByDesc('created_at')
            ->paginate(50)
            ->withQueryString();

        return view('admin.university.audit-logs.index', [
            'logs' => $logs,
            'filters' => [
                'action' => $request->input('action', ''),
                'entity_type' => $request->input('entity_type', ''),
                'entity_id' => $request->input('entity_id', ''),
                'actor' => $request->input('actor', ''),
                'correlation_id' => $request->input('correlation_id', ''),
                'from' => $request->input('from', ''),
                'to' => $request->input('to', ''),
            ],
        ]);
    }
}
