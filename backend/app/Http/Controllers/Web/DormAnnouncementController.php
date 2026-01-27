<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnnouncementRequest;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DormAnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeIfEnabled('viewAny', Announcement::class);

        $dormId = $this->requireDormId($request);

        $query = Announcement::where('dorm_id', $dormId)
            ->with(['creator']);

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

        $announcements = $query->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.dorm.announcements.index', [
            'announcements' => $announcements,
            'filters' => [
                'q' => $request->input('q', ''),
                'status' => $request->input('status', ''),
            ],
        ]);
    }

    public function create(Request $request)
    {
        $dormId = $this->requireDormId($request);
        $this->authorizeIfEnabled('create', [Announcement::class, null, $dormId]);

        return view('admin.dorm.announcements.form', [
            'announcement' => new Announcement,
        ]);
    }

    public function store(AnnouncementRequest $request)
    {
        $dormId = $this->requireDormId($request);
        $this->authorizeIfEnabled('create', [Announcement::class, null, $dormId]);

        $data = $request->validated();
        $schedule = $this->resolveSchedule($data, null);

        $announcement = Announcement::create([
            'university_id' => $request->user()->university_id,
            'dorm_id' => $dormId,
            'created_by' => $request->user()->id,
            'title' => $data['title'],
            'body' => $data['body'],
            'audience' => 'DORM',
            'status' => $schedule['status'],
            'publish_at' => $schedule['publish_at'],
            'expire_at' => $schedule['expire_at'],
        ]);

        return redirect()->route('admin.dorm.announcements.show', $announcement)
            ->with('success', 'Announcement created successfully.');
    }

    public function show(Request $request, Announcement $announcement)
    {
        $dormId = $this->requireDormId($request);

        if ($announcement->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('view', $announcement);

        $announcement->load(['creator']);

        return view('admin.dorm.announcements.show', [
            'announcement' => $announcement,
        ]);
    }

    public function edit(Request $request, Announcement $announcement)
    {
        $dormId = $this->requireDormId($request);

        if ($announcement->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('update', $announcement);

        return view('admin.dorm.announcements.form', [
            'announcement' => $announcement,
        ]);
    }

    public function update(AnnouncementRequest $request, Announcement $announcement)
    {
        $dormId = $this->requireDormId($request);

        if ($announcement->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('update', $announcement);

        $data = $request->validated();
        $schedule = $this->resolveSchedule($data, $announcement);

        $announcement->fill([
            'title' => $data['title'] ?? $announcement->title,
            'body' => $data['body'] ?? $announcement->body,
            'audience' => 'DORM',
            'status' => $schedule['status'],
            'publish_at' => $schedule['publish_at'],
            'expire_at' => $schedule['expire_at'],
        ]);

        $announcement->save();

        return redirect()->route('admin.dorm.announcements.show', $announcement)
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
