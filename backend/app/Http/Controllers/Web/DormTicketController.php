<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\TicketCommentRequest;
use App\Http\Requests\TicketRequest;
use App\Models\Dorm;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DormTicketController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeIfEnabled('viewAny', Ticket::class);

        $dormId = $this->requireDormId($request);

        $query = Ticket::where('dorm_id', $dormId)
            ->with(['creator', 'assignee']);

        if ($request->filled('q')) {
            $term = $request->input('q');
            $query->where(function ($builder) use ($term) {
                $builder->where('subject', 'like', '%' . $term . '%')
                    ->orWhere('description', 'like', '%' . $term . '%')
                    ->orWhere('category', 'like', '%' . $term . '%')
                    ->orWhereHas('creator', function ($userQuery) use ($term) {
                        $userQuery->where('name', 'like', '%' . $term . '%')
                            ->orWhere('email', 'like', '%' . $term . '%');
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        $tickets = $query->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.dorm.tickets.index', [
            'tickets' => $tickets,
            'filters' => [
                'q' => $request->input('q', ''),
                'status' => $request->input('status', ''),
                'priority' => $request->input('priority', ''),
            ],
        ]);
    }

    public function create(Request $request)
    {
        $dormId = $this->requireDormId($request);
        $this->authorizeIfEnabled('create', [Ticket::class, null, $dormId]);

        $universityId = $this->resolveUniversityId($request, $dormId);

        return view('admin.dorm.tickets.form', [
            'ticket' => new Ticket,
            'assignees' => $this->loadAssignees($universityId, $dormId),
        ]);
    }

    public function store(TicketRequest $request)
    {
        $dormId = $this->requireDormId($request);
        $this->authorizeIfEnabled('create', [Ticket::class, null, $dormId]);

        $universityId = $this->resolveUniversityId($request, $dormId);
        $data = $request->validated();

        $assigneeId = $data['assigned_to'] ?? null;
        $this->validateAssignee($assigneeId, $this->loadAssigneeIds($universityId, $dormId));

        $ticket = Ticket::create([
            'university_id' => $universityId,
            'dorm_id' => $dormId,
            'created_by' => $request->user()->id,
            'assigned_to' => $assigneeId,
            'subject' => $data['subject'],
            'description' => $data['description'],
            'category' => $data['category'] ?? null,
            'priority' => $data['priority'] ?? 'MEDIUM',
            'status' => 'OPEN',
        ]);

        if ($ticket->assignee) {
            app(NotificationService::class)->notifyUser(
                $ticket->assignee,
                'New ticket assigned',
                $ticket->subject,
                route('admin.dorm.tickets.show', $ticket)
            );
        }

        return redirect()->route('admin.dorm.tickets.show', $ticket)
            ->with('success', 'Ticket created successfully.');
    }

    public function show(Request $request, Ticket $ticket)
    {
        $dormId = $this->requireDormId($request);

        if ($ticket->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('view', $ticket);

        $ticket->load(['creator', 'assignee', 'comments.user']);

        return view('admin.dorm.tickets.show', [
            'ticket' => $ticket,
        ]);
    }

    public function edit(Request $request, Ticket $ticket)
    {
        $dormId = $this->requireDormId($request);

        if ($ticket->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('update', $ticket);

        $universityId = $this->resolveUniversityId($request, $dormId);

        $ticket->load(['assignee']);

        return view('admin.dorm.tickets.form', [
            'ticket' => $ticket,
            'assignees' => $this->loadAssignees($universityId, $dormId),
        ]);
    }

    public function update(TicketRequest $request, Ticket $ticket)
    {
        $dormId = $this->requireDormId($request);

        if ($ticket->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('update', $ticket);

        $universityId = $this->resolveUniversityId($request, $dormId);
        $data = $request->validated();
        $previousAssigneeId = $ticket->assigned_to;

        $assigneeId = $data['assigned_to'] ?? $ticket->assigned_to;
        $this->validateAssignee($assigneeId, $this->loadAssigneeIds($universityId, $dormId));

        $ticket->fill([
            'subject' => $data['subject'] ?? $ticket->subject,
            'description' => $data['description'] ?? $ticket->description,
            'category' => array_key_exists('category', $data) ? $data['category'] : $ticket->category,
            'priority' => $data['priority'] ?? $ticket->priority,
            'status' => $data['status'] ?? $ticket->status,
            'assigned_to' => $assigneeId,
        ]);

        if (array_key_exists('status', $data)) {
            if (in_array($data['status'], ['RESOLVED', 'CLOSED'], true)) {
                $ticket->resolved_at = now();
            } else {
                $ticket->resolved_at = null;
            }
        }

        $ticket->save();

        if ($assigneeId && $assigneeId !== $previousAssigneeId) {
            $assignee = User::find($assigneeId);
            if ($assignee) {
                app(NotificationService::class)->notifyUser(
                    $assignee,
                    'Ticket assigned to you',
                    $ticket->subject,
                    route('admin.dorm.tickets.show', $ticket)
                );
            }
        }

        return redirect()->route('admin.dorm.tickets.show', $ticket)
            ->with('success', 'Ticket updated successfully.');
    }

    public function comment(TicketCommentRequest $request, Ticket $ticket)
    {
        $dormId = $this->requireDormId($request);

        if ($ticket->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('view', $ticket);

        TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'body' => $request->validated()['body'],
        ]);

        return redirect()->route('admin.dorm.tickets.show', $ticket)
            ->with('success', 'Comment added.');
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorizeIfEnabled('viewAny', Ticket::class);

        $dormId = $this->requireDormId($request);

        $query = Ticket::where('dorm_id', $dormId)
            ->with(['creator', 'assignee']);

        if ($request->filled('q')) {
            $term = $request->input('q');
            $query->where(function ($builder) use ($term) {
                $builder->where('subject', 'like', '%' . $term . '%')
                    ->orWhere('description', 'like', '%' . $term . '%')
                    ->orWhere('category', 'like', '%' . $term . '%')
                    ->orWhereHas('creator', function ($userQuery) use ($term) {
                        $userQuery->where('name', 'like', '%' . $term . '%')
                            ->orWhere('email', 'like', '%' . $term . '%');
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        $filename = 'tickets_export_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'subject', 'priority', 'status', 'assignee', 'created_at']);

            $query->orderByDesc('created_at')->chunk(200, function ($tickets) use ($handle) {
                foreach ($tickets as $ticket) {
                    fputcsv($handle, [
                        $ticket->id,
                        $ticket->subject,
                        $ticket->priority,
                        $ticket->status,
                        $ticket->assignee?->name ?? 'Unassigned',
                        $ticket->created_at?->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        }, $filename);
    }

    private function resolveUniversityId(Request $request, int $dormId): int
    {
        $universityId = $request->user()?->university_id;

        if ($universityId) {
            return $universityId;
        }

        return (int) Dorm::where('id', $dormId)->value('university_id');
    }

    private function loadAssignees(int $universityId, int $dormId)
    {
        return User::where('university_id', $universityId)
            ->where(function ($query) use ($dormId) {
                $query->where('role', User::ROLE_UNIVERSITY_ADMIN)
                    ->orWhere(function ($subQuery) use ($dormId) {
                        $subQuery->where('role', User::ROLE_DORM_ADMIN)
                            ->whereHas('dormAdmin', function ($dormQuery) use ($dormId) {
                                $dormQuery->where('dorm_id', $dormId);
                            });
                    });
            })
            ->orderBy('name')
            ->get();
    }

    private function loadAssigneeIds(int $universityId, int $dormId): array
    {
        return User::where('university_id', $universityId)
            ->where(function ($query) use ($dormId) {
                $query->where('role', User::ROLE_UNIVERSITY_ADMIN)
                    ->orWhere(function ($subQuery) use ($dormId) {
                        $subQuery->where('role', User::ROLE_DORM_ADMIN)
                            ->whereHas('dormAdmin', function ($dormQuery) use ($dormId) {
                                $dormQuery->where('dorm_id', $dormId);
                            });
                    });
            })
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->all();
    }

    private function validateAssignee(?int $assigneeId, array $allowedIds): void
    {
        if ($assigneeId && !in_array($assigneeId, $allowedIds, true)) {
            throw ValidationException::withMessages([
                'assigned_to' => ['Assignee must belong to this dorm or university.'],
            ]);
        }
    }
}
