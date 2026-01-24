<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;

class ActivityFeedController extends Controller
{
    public function index()
    {
        $logs = AuditLog::with('actor')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return AuditLogResource::collection($logs);
    }
}
