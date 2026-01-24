<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;

class UniversityAuditLogController extends Controller
{
    public function index()
    {
        $logs = AuditLog::with('actor')
            ->orderByDesc('created_at')
            ->paginate(50);

        return view('admin.university.audit-logs.index', [
            'logs' => $logs,
        ]);
    }
}
