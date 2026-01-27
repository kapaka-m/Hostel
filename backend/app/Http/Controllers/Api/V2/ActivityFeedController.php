<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class ActivityFeedController extends Controller
{
    public function index(Request $request)
    {
        $universityId = $this->requireUniversityId($request);

        $logs = AuditLog::with('actor')
            ->where('university_id', $universityId)
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return AuditLogResource::collection($logs);
    }
}
