<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;

class UniversityActivityFeedController extends Controller
{
    public function index()
    {
        $logs = AuditLog::with('actor')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return view('admin.university.activity-feed', [
            'logs' => $logs,
        ]);
    }

    public function data()
    {
        $logs = AuditLog::with('actor')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return AuditLogResource::collection($logs);
    }
}
