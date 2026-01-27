<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\TicketCommentRequest;
use App\Http\Requests\TicketRequest;
use App\Http\Resources\TicketCommentResource;
use App\Http\Resources\TicketResource;
use App\Models\Dorm;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Ticket::query()->with(['dorm', 'creator', 'assignee']);

        if ($user->role === User::ROLE_UNIVERSITY_ADMIN) {
            $query->where('university_id', $user->university_id);
        } elseif ($user->role === User::ROLE_DORM_ADMIN) {
            $dormId = $user->dormAdmin?->dorm_id;
            $query->where('dorm_id', $dormId);
        } else {
            abort(403, 'Unauthorized.');
        }

        if ($request->filled('q')) {
            $term = $request->input('q');
            $query->where(function ($builder) use ($term) {
                $builder->where('subject', 'like', '%' . $term . '%')
                    ->orWhere('description', 'like', '%' . $term . '%')
                    ->orWhere('category', 'like', '%' . $term . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        $tickets = $query->orderByDesc('created_at')->paginate(20);

        return TicketResource::collection($tickets);
    }

    public function store(TicketRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        $universityId = $user->university_id;
        $dormId = null;

        if ($user->role === User::ROLE_UNIVERSITY_ADMIN) {
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
        } elseif ($user->role === User::ROLE_DORM_ADMIN) {
            $dormId = $user->dormAdmin?->dorm_id;
        } else {
            abort(403, 'Unauthorized.');
        }

        $assigneeId = $data['assigned_to'] ?? null;
        $this->validateAssignee($assigneeId, $this->loadAssigneeIds($user, $dormId));

        $ticket = Ticket::create([
            'university_id' => $universityId,
            'dorm_id' => $dormId,
            'created_by' => $user->id,
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
                $ticket->subject
            );
        }

        $ticket->load(['dorm', 'creator', 'assignee']);

        return (new TicketResource($ticket))->response()->setStatusCode(201);
    }

    public function show(Request $request, Ticket $ticket)
    {
        $user = $request->user();
        $this->ensureUserCanAccessTicket($user, $ticket);

        $ticket->load(['dorm', 'creator', 'assignee', 'comments.user']);

        return new TicketResource($ticket);
    }

    public function update(TicketRequest $request, Ticket $ticket)
    {
        $user = $request->user();
        $data = $request->validated();

        $this->ensureUserCanAccessTicket($user, $ticket);

        $previousAssigneeId = $ticket->assigned_to;
        $assigneeId = $data['assigned_to'] ?? $ticket->assigned_to;
        $this->validateAssignee($assigneeId, $this->loadAssigneeIds($user, $ticket->dorm_id));

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
                    $ticket->subject
                );
            }
        }

        $ticket->load(['dorm', 'creator', 'assignee']);

        return new TicketResource($ticket);
    }

    public function comment(TicketCommentRequest $request, Ticket $ticket)
    {
        $user = $request->user();

        $this->ensureUserCanAccessTicket($user, $ticket);

        $comment = TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'body' => $request->validated()['body'],
        ]);

        $comment->load('user');

        return (new TicketCommentResource($comment))->response()->setStatusCode(201);
    }

    private function loadAssigneeIds(User $user, ?int $dormId): array
    {
        $query = User::where('university_id', $user->university_id)
            ->whereIn('role', [User::ROLE_UNIVERSITY_ADMIN, User::ROLE_DORM_ADMIN]);

        if ($user->role === User::ROLE_DORM_ADMIN && $dormId) {
            $query->where(function ($builder) use ($dormId) {
                $builder->where('role', User::ROLE_UNIVERSITY_ADMIN)
                    ->orWhere(function ($subQuery) use ($dormId) {
                        $subQuery->where('role', User::ROLE_DORM_ADMIN)
                            ->whereHas('dormAdmin', function ($dormQuery) use ($dormId) {
                                $dormQuery->where('dorm_id', $dormId);
                            });
                    });
            });
        }

        return $query->pluck('id')->map(fn($id) => (int) $id)->all();
    }

    private function validateAssignee(?int $assigneeId, array $allowedIds): void
    {
        if ($assigneeId && !in_array($assigneeId, $allowedIds, true)) {
            throw ValidationException::withMessages([
                'assigned_to' => ['Assignee must belong to this university.'],
            ]);
        }
    }

    private function ensureUserCanAccessTicket(User $user, Ticket $ticket): void
    {
        if ($user->role === User::ROLE_UNIVERSITY_ADMIN) {
            abort_if($ticket->university_id !== $user->university_id, 403);
            return;
        }

        if ($user->role === User::ROLE_DORM_ADMIN) {
            abort_if($ticket->dorm_id !== $user->dormAdmin?->dorm_id, 403);
            return;
        }

        abort(403);
    }
}
