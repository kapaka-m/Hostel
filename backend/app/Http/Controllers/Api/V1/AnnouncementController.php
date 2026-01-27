<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnnouncementRequest;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use App\Models\Dorm;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Announcement::query()->with(['dorm', 'creator']);

        if ($user->role === User::ROLE_UNIVERSITY_ADMIN) {
            $query->where('university_id', $user->university_id);
        } elseif ($user->role === User::ROLE_DORM_ADMIN) {
            $query->where('dorm_id', $user->dormAdmin?->dorm_id);
        } elseif ($user->role === User::ROLE_STUDENT) {
            $dormId = $user->student?->dorm_id;
            $query->where('university_id', $user->university_id)
                ->where(function ($builder) use ($dormId) {
                    $builder->where('audience', 'UNIVERSITY')
                        ->orWhere(function ($subQuery) use ($dormId) {
                            $subQuery->where('audience', 'DORM')
                                ->where('dorm_id', $dormId);
                        });
                })
                ->where(function ($builder) {
                    $builder->whereNull('publish_at')
                        ->orWhere('publish_at', '<=', now());
                })
                ->where(function ($builder) {
                    $builder->whereNull('expire_at')
                        ->orWhere('expire_at', '>', now());
                });
        } else {
            abort(403, 'Unauthorized.');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('audience')) {
            $query->where('audience', $request->input('audience'));
        }

        $announcements = $query->orderByDesc('created_at')->paginate(20);

        return AnnouncementResource::collection($announcements);
    }

    public function show(Request $request, Announcement $announcement)
    {
        $user = $request->user();

        if ($user->role === User::ROLE_UNIVERSITY_ADMIN && $announcement->university_id !== $user->university_id) {
            abort(403);
        }

        if ($user->role === User::ROLE_DORM_ADMIN && $announcement->dorm_id !== $user->dormAdmin?->dorm_id) {
            abort(403);
        }

        if ($user->role === User::ROLE_STUDENT) {
            $dormId = $user->student?->dorm_id;
            if ($announcement->university_id !== $user->university_id) {
                abort(403);
            }
            if ($announcement->audience === 'DORM' && $announcement->dorm_id !== $dormId) {
                abort(403);
            }
        }

        $announcement->load(['dorm', 'creator']);

        return new AnnouncementResource($announcement);
    }

    public function store(AnnouncementRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        $universityId = $user->university_id;
        $dormId = null;

        if ($user->role === User::ROLE_UNIVERSITY_ADMIN) {
            $dormId = $data['dorm_id'] ?? null;
            if (($data['audience'] ?? 'UNIVERSITY') === 'DORM') {
                if (!$dormId) {
                    throw ValidationException::withMessages([
                        'dorm_id' => ['Dorm is required for dorm announcements.'],
                    ]);
                }

                $exists = Dorm::where('id', $dormId)
                    ->where('university_id', $universityId)
                    ->exists();

                if (!$exists) {
                    throw ValidationException::withMessages([
                        'dorm_id' => ['Selected dorm is not in this university.'],
                    ]);
                }
            }
        } elseif ($user->role === User::ROLE_DORM_ADMIN) {
            $dormId = $user->dormAdmin?->dorm_id;
        } else {
            abort(403, 'Unauthorized.');
        }

        $schedule = $this->resolveSchedule($data, null);

        $announcement = Announcement::create([
            'university_id' => $universityId,
            'dorm_id' => $user->role === User::ROLE_DORM_ADMIN ? $dormId : (($data['audience'] ?? 'UNIVERSITY') === 'DORM' ? $dormId : null),
            'created_by' => $user->id,
            'title' => $data['title'],
            'body' => $data['body'],
            'audience' => $user->role === User::ROLE_DORM_ADMIN ? 'DORM' : $data['audience'],
            'status' => $schedule['status'],
            'publish_at' => $schedule['publish_at'],
            'expire_at' => $schedule['expire_at'],
        ]);

        $announcement->load(['dorm', 'creator']);

        return (new AnnouncementResource($announcement))->response()->setStatusCode(201);
    }

    public function update(AnnouncementRequest $request, Announcement $announcement)
    {
        $user = $request->user();
        $data = $request->validated();

        if ($user->role === User::ROLE_UNIVERSITY_ADMIN) {
            if ($announcement->university_id !== $user->university_id) {
                abort(403);
            }
        } elseif ($user->role === User::ROLE_DORM_ADMIN) {
            if ($announcement->dorm_id !== $user->dormAdmin?->dorm_id) {
                abort(403);
            }
        } else {
            abort(403);
        }

        $universityId = $user->university_id;
        $dormId = $data['dorm_id'] ?? $announcement->dorm_id;

        if ($user->role === User::ROLE_UNIVERSITY_ADMIN && ($data['audience'] ?? $announcement->audience) === 'DORM') {
            if (!$dormId) {
                throw ValidationException::withMessages([
                    'dorm_id' => ['Dorm is required for dorm announcements.'],
                ]);
            }

            $exists = Dorm::where('id', $dormId)
                ->where('university_id', $universityId)
                ->exists();

            if (!$exists) {
                throw ValidationException::withMessages([
                    'dorm_id' => ['Selected dorm is not in this university.'],
                ]);
            }
        }

        $schedule = $this->resolveSchedule($data, $announcement);

        $announcement->fill([
            'title' => $data['title'] ?? $announcement->title,
            'body' => $data['body'] ?? $announcement->body,
            'audience' => $user->role === User::ROLE_DORM_ADMIN ? 'DORM' : ($data['audience'] ?? $announcement->audience),
            'dorm_id' => $user->role === User::ROLE_DORM_ADMIN
                ? $announcement->dorm_id
                : (($data['audience'] ?? $announcement->audience) === 'DORM' ? $dormId : null),
            'status' => $schedule['status'],
            'publish_at' => $schedule['publish_at'],
            'expire_at' => $schedule['expire_at'],
        ]);

        $announcement->save();
        $announcement->load(['dorm', 'creator']);

        return new AnnouncementResource($announcement);
    }

    private function resolveSchedule(array $data, ?Announcement $announcement): array
    {
        $statusInput = $data['status'] ?? $announcement?->status ?? 'DRAFT';
        $publishAt = array_key_exists('publish_at', $data)
            ? ($data['publish_at'] ? Carbon::parse($data['publish_at']) : null)
            : $announcement?->publish_at;
        $expireAt = array_key_exists('expire_at', $data)
            ? ($data['expire_at'] ? Carbon::parse($data['expire_at']) : null)
            : $announcement?->expire_at;

        if ($statusInput === 'DRAFT') {
            return [
                'status' => 'DRAFT',
                'publish_at' => $publishAt,
                'expire_at' => $expireAt,
            ];
        }

        if (!$publishAt) {
            $publishAt = Carbon::now();
        }

        $status = $publishAt->isFuture() ? 'SCHEDULED' : 'PUBLISHED';

        if ($expireAt && $expireAt->isPast()) {
            $status = 'EXPIRED';
        }

        return [
            'status' => $status,
            'publish_at' => $publishAt,
            'expire_at' => $expireAt,
        ];
    }
}
