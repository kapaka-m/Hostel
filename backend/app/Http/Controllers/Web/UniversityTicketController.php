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

class UniversityTicketController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeIfEnabled('viewAny', arguments: Ticket::class);

        $universityId = $this->requireUniversityId($request);

        $query = Ticket::where('university_id', $universityId)
            ->with(['dorm', 'creator', 'assignee']);

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

        if ($request->filled('dorm_id')) {
            $query->where('dorm_id', $request->integer('dorm_id'));
        }

        $tickets = $query->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $dorms = Dorm::where('university_id', $universityId)->orderBy('name')->get();

        return view('admin.university.tickets.index', [
            'tickets' => $tickets,
            'dorms' => $dorms,
            'filters' => [
                'q' => $request->input('q', ''),
                'status' => $request->input('status', ''),
                'priority' => $request->input('priority', ''),
                'dorm_id' => $request->input('dorm_id', ''),
            ],
        ]);
    }

    public function create(Request $request)
    {
        $universityId = $this->requireUniversityId($request);
        $this->authorizeIfEnabled('create', [Ticket::class, $universityId, null]);

        return view('admin.university.tickets.form', [
            'ticket' => new Ticket,
            'dorms' => Dorm::where('university_id', $universityId)->orderBy('name')->get(),
            'assignees' => $this->loadAssignees($universityId),
        ]);
    }

    public function store(TicketRequest $request)
    {
        $universityId = $this->requireUniversityId($request);
        $this->authorizeIfEnabled('create', [Ticket::class, $universityId, null]);

        $data = $request->validated();
        $dormId = $data['dorm_id'] ?? null;

        if ($dormId) {
            $exists = Dorm::where('id', $dormId)
                ->where('university_id', $universityId)
                ->exists();

            if (!$exists) {
                throw ValidationException::withMessages([
                    'dorm_id' => ['Selected dorm is not in this university.'],
                ]);
            }
        }

        $assigneeId = $data['assigned_to'] ?? null;
        $this->validateAssignee($assigneeId, $this->loadAssigneeIds($universityId));

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
                route('admin.university.tickets.show', $ticket)
            );
        }

        return redirect()->route('admin.university.tickets.show', $ticket)
            ->with('success', 'Ticket created successfully.');
    }

    public function show(Request $request, Ticket $ticket)
    {
        $universityId = $this->requireUniversityId($request);

        if ($ticket->university_id !== $universityId) {
            abort(403);
        }

        $this->authorizeIfEnabled('view', $ticket);

        $ticket->load(['dorm', 'creator', 'assignee', 'comments.user']);

        return view('admin.university.tickets.show', [
            'ticket' => $ticket,
        ]);
    }

    public function edit(Request $request, Ticket $ticket)
    {
        $universityId = $this->requireUniversityId($request);

        if ($ticket->university_id !== $universityId) {
            abort(403);
        }

        $this->authorizeIfEnabled('update', $ticket);

        $ticket->load(['dorm', 'assignee']);

        return view('admin.university.tickets.form', [
            'ticket' => $ticket,
            'dorms' => Dorm::where('university_id', $universityId)->orderBy('name')->get(),
            'assignees' => $this->loadAssignees($universityId),
        ]);
    }

    public function update(TicketRequest $request, Ticket $ticket)
    {
        $universityId = $this->requireUniversityId($request);

        if ($ticket->university_id !== $universityId) {
            abort(403);
        }

        $this->authorizeIfEnabled('update', $ticket);

        $data = $request->validated();
        $previousAssigneeId = $ticket->assigned_to;
        $dormId = $data['dorm_id'] ?? $ticket->dorm_id;

        if ($dormId) {
            $exists = Dorm::where('id', $dormId)
                ->where('university_id', $universityId)
                ->exists();

            if (!$exists) {
                throw ValidationException::withMessages([
                    'dorm_id' => ['Selected dorm is not in this university.'],
                ]);
            }
        }

        $assigneeId = $data['assigned_to'] ?? $ticket->assigned_to;
        $this->validateAssignee($assigneeId, $this->loadAssigneeIds($universityId));

        $ticket->fill([
            'subject' => $data['subject'] ?? $ticket->subject,
            'description' => $data['description'] ?? $ticket->description,
            'category' => array_key_exists('category', $data) ? $data['category'] : $ticket->category,
            'priority' => $data['priority'] ?? $ticket->priority,
            'status' => $data['status'] ?? $ticket->status,
            'dorm_id' => $dormId,
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
                    route('admin.university.tickets.show', $ticket)
                );
            }
        }

        return redirect()->route('admin.university.tickets.show', $ticket)
            ->with('success', 'Ticket updated successfully.');
    }

    public function comment(TicketCommentRequest $request, Ticket $ticket)
    {
        $universityId = $this->requireUniversityId($request);

        if ($ticket->university_id !== $universityId) {
            abort(403);
        }

        $this->authorizeIfEnabled('view', $ticket);

        TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'body' => $request->validated()['body'],
        ]);

        return redirect()->route('admin.university.tickets.show', $ticket)
            ->with('success', 'Comment added.');
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorizeIfEnabled('viewAny', Ticket::class);

        $universityId = $this->requireUniversityId($request);

        $query = Ticket::where('university_id', $universityId)
            ->with(['dorm', 'creator', 'assignee']);

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

        if ($request->filled('dorm_id')) {
            $query->where('dorm_id', $request->integer('dorm_id'));
        }

        $filename = 'tickets_export_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'subject', 'priority', 'status', 'dorm', 'assignee', 'created_at']);

            $query->orderByDesc('created_at')->chunk(200, function ($tickets) use ($handle) {
                foreach ($tickets as $ticket) {
                    fputcsv($handle, [
                        $ticket->id,
                        $ticket->subject,
                        $ticket->priority,
                        $ticket->status,
                        $ticket->dorm?->name ?? 'University-wide',
                        $ticket->assignee?->name ?? 'Unassigned',
                        $ticket->created_at?->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        }, $filename);
    }

    private function loadAssignees(int $universityId)
    {
        return User::where('university_id', $universityId)
            ->whereIn('role', [User::ROLE_UNIVERSITY_ADMIN, User::ROLE_DORM_ADMIN])
            ->orderBy('name')
            ->get();
    }

    private function loadAssigneeIds(int $universityId): array
    {
        return User::where('university_id', $universityId)
            ->whereIn('role', [User::ROLE_UNIVERSITY_ADMIN, User::ROLE_DORM_ADMIN])
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->all();
    }

    private function validateAssignee(?int $assigneeId, array $allowedIds): void
    {
        if ($assigneeId && !in_array($assigneeId, $allowedIds, true)) {
            throw ValidationException::withMessages([
                'assigned_to' => ['Assignee must belong to this university.'],
            ]);
        }
    }
}
