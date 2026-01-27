<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnnouncementRequest;
use App\Models\Announcement;
use App\Models\Dorm;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class UniversityAnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeIfEnabled('viewAny', Announcement::class);

        $universityId = $this->requireUniversityId($request);

        $query = Announcement::where('university_id', $universityId)
            ->with(['dorm', 'creator']);

        if ($request->filled('q')) {
            $term = $request->input('q');
            $query->where(function ($builder) use ($term) {
                $builder->where('title', 'like', '%' . $term . '%')
                    ->orWhere('body', 'like', '%' . $term . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('audience')) {
            $query->where('audience', $request->input('audience'));
        }

        if ($request->filled('dorm_id')) {
            $query->where('dorm_id', $request->integer('dorm_id'));
        }

        $announcements = $query->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $dorms = Dorm::where('university_id', $universityId)->orderBy('name')->get();

        return view('admin.university.announcements.index', [
            'announcements' => $announcements,
            'dorms' => $dorms,
            'filters' => [
                'q' => $request->input('q', ''),
                'status' => $request->input('status', ''),
                'audience' => $request->input('audience', ''),
                'dorm_id' => $request->input('dorm_id', ''),
            ],
        ]);
    }

    public function create(Request $request)
    {
        $universityId = $this->requireUniversityId($request);
        $this->authorizeIfEnabled('create', [Announcement::class, $universityId, null]);

        return view('admin.university.announcements.form', [
            'announcement' => new Announcement,
            'dorms' => Dorm::where('university_id', $universityId)->orderBy('name')->get(),
        ]);
    }

    public function store(AnnouncementRequest $request)
    {
        $universityId = $this->requireUniversityId($request);
        $this->authorizeIfEnabled('create', [Announcement::class, $universityId, null]);

        $data = $request->validated();
        $dormId = $data['dorm_id'] ?? null;

        if ($data['audience'] === 'DORM') {
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

        $schedule = $this->resolveSchedule($data, null);

        $announcement = Announcement::create([
            'university_id' => $universityId,
            'dorm_id' => $data['audience'] === 'DORM' ? $dormId : null,
            'created_by' => $request->user()->id,
            'title' => $data['title'],
            'body' => $data['body'],
            'audience' => $data['audience'],
            'status' => $schedule['status'],
            'publish_at' => $schedule['publish_at'],
            'expire_at' => $schedule['expire_at'],
        ]);

        return redirect()->route('admin.university.announcements.show', $announcement)
            ->with('success', 'Announcement created successfully.');
    }

    public function show(Request $request, Announcement $announcement)
    {
        $universityId = $this->requireUniversityId($request);

        if ($announcement->university_id !== $universityId) {
            abort(403);
        }

        $this->authorizeIfEnabled('view', $announcement);

        $announcement->load(['dorm', 'creator']);

        return view('admin.university.announcements.show', [
            'announcement' => $announcement,
        ]);
    }

    public function edit(Request $request, Announcement $announcement)
    {
        $universityId = $this->requireUniversityId($request);

        if ($announcement->university_id !== $universityId) {
            abort(403);
        }

        $this->authorizeIfEnabled('update', $announcement);

        return view('admin.university.announcements.form', [
            'announcement' => $announcement,
            'dorms' => Dorm::where('university_id', $universityId)->orderBy('name')->get(),
        ]);
    }

    public function update(AnnouncementRequest $request, Announcement $announcement)
    {
        $universityId = $this->requireUniversityId($request);

        if ($announcement->university_id !== $universityId) {
            abort(403);
        }

        $this->authorizeIfEnabled('update', $announcement);

        $data = $request->validated();
        $dormId = $data['dorm_id'] ?? $announcement->dorm_id;

        if (($data['audience'] ?? $announcement->audience) === 'DORM') {
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
            'audience' => $data['audience'] ?? $announcement->audience,
            'dorm_id' => ($data['audience'] ?? $announcement->audience) === 'DORM' ? $dormId : null,
            'status' => $schedule['status'],
            'publish_at' => $schedule['publish_at'],
            'expire_at' => $schedule['expire_at'],
        ]);

        $announcement->save();

        return redirect()->route('admin.university.announcements.show', $announcement)
            ->with('success', 'Announcement updated successfully.');
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
