<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateDormAdminRequest;
use App\Http\Requests\UpdateDormAdminRequest;
use App\Models\Dorm;
use App\Models\DormAdmin;
use App\Models\User;
use App\Models\UserFreezeLog;
use App\Support\FeatureFlags;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UniversityDormAdminController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeIfEnabled('viewAny', Dorm::class);

        $universityId = $this->requireUniversityId($request);

        $query = DormAdmin::query()
            ->with(['user', 'dorm'])
            ->whereHas('dorm', function ($builder) use ($universityId) {
                $builder->where('university_id', $universityId);
            });

        if ($request->filled('q')) {
            $term = $request->string('q')->toString();
            $query->where(function ($builder) use ($term) {
                $builder->whereHas('user', function ($inner) use ($term) {
                    $inner->where('name', 'like', '%' . $term . '%')
                        ->orWhere('email', 'like', '%' . $term . '%');
                })->orWhereHas('dorm', function ($inner) use ($term) {
                    $inner->where('name', 'like', '%' . $term . '%');
                });
            });
        }

        $dormAdmins = $query->orderByDesc('id')->paginate(15)->withQueryString();

        return view('admin.university.dorm-admins.index', [
            'dormAdmins' => $dormAdmins,
            'filters' => [
                'q' => $request->input('q', ''),
            ],
        ]);
    }

    public function create(Request $request)
    {
        $this->authorizeIfEnabled('create', Dorm::class);

        $universityId = $this->requireUniversityId($request);

        $dorms = Dorm::where('university_id', $universityId)->orderBy('name')->get();

        return view('admin.university.dorm-admins.create', [
            'dorms' => $dorms,
        ]);
    }

    public function edit(Request $request, DormAdmin $dormAdmin)
    {
        $this->authorizeIfEnabled('create', Dorm::class);

        $universityId = $this->requireUniversityId($request);

        if ($dormAdmin->dorm?->university_id !== $universityId) {
            abort(403);
        }

        $dorms = Dorm::where('university_id', $universityId)->orderBy('name')->get();

        return view('admin.university.dorm-admins.edit', [
            'dormAdmin' => $dormAdmin->load('user', 'dorm'),
            'dorms' => $dorms,
        ]);
    }

    public function update(UpdateDormAdminRequest $request, DormAdmin $dormAdmin)
    {
        $this->authorizeIfEnabled('create', Dorm::class);

        $universityId = $this->requireUniversityId($request);

        if ($dormAdmin->dorm?->university_id !== $universityId) {
            abort(403);
        }

        $data = $request->validated();

        $dorm = Dorm::where('id', $data['dorm_id'])
            ->where('university_id', $universityId)
            ->firstOrFail();

        $dormAdmin->dorm_id = $dorm->id;
        $dormAdmin->save();

        $user = $dormAdmin->user;
        $previousActive = (bool) $user->is_active;
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->is_active = (bool) $data['is_active'];
        $user->frozen_at = $user->is_active ? null : now();
        $user->university_id = $universityId;
        $user->save();

        if ($previousActive !== $user->is_active) {
            UserFreezeLog::create([
                'user_id' => $user->id,
                'actor_id' => $request->user()->id,
                'action' => $user->is_active ? 'unfreeze' : 'freeze',
                'reason' => $data['freeze_reason'] ?? null,
            ]);
        }

        if (FeatureFlags::enabled('permissions')) {
            Role::findOrCreate($user->role);
            $user->syncRoles([$user->role]);
        }

        return redirect()->route('admin.university.dorm-admins.index')
            ->with('success', 'Dorm admin updated successfully.');
    }

    public function store(CreateDormAdminRequest $request)
    {
        $data = $request->validated();

        $universityId = $this->requireUniversityId($request);

        $dorm = Dorm::where('id', $data['dorm_id'])
            ->where('university_id', $universityId)
            ->firstOrFail();

        $this->authorizeIfEnabled('createDormAdmin', $dorm);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_DORM_ADMIN,
            'university_id' => $universityId,
        ]);

        if (FeatureFlags::enabled('permissions')) {
            Role::findOrCreate($user->role);
            $user->syncRoles([$user->role]);
        }

        DormAdmin::create([
            'user_id' => $user->id,
            'dorm_id' => $dorm->id,
        ]);

        return redirect()->route('admin.university.dorms.index')
            ->with('success', 'Dorm admin created successfully.');
    }
}
