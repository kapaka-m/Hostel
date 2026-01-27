<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateDormAdminRequest;
use App\Http\Requests\DormRequest;
use App\Http\Resources\DormResource;
use App\Http\Resources\UserResource;
use App\Models\Dorm;
use App\Models\DormAdmin;
use App\Models\User;
use App\Support\FeatureFlags;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DormController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeIfEnabled('viewAny', Dorm::class);

        $universityId = $this->requireUniversityId($request);

        return DormResource::collection(
            Dorm::where('university_id', $universityId)->orderBy('name')->get()
        );
    }

    public function store(DormRequest $request)
    {
        $this->authorizeIfEnabled('create', Dorm::class);

        $universityId = $this->requireUniversityId($request);

        $dorm = Dorm::create(array_merge($request->validated(), [
            'university_id' => $universityId,
        ]));

        return new DormResource($dorm);
    }

    public function show(Dorm $dorm)
    {
        $this->authorizeIfEnabled('view', $dorm);

        return new DormResource($dorm);
    }

    public function update(DormRequest $request, Dorm $dorm)
    {
        $this->authorizeIfEnabled('update', $dorm);

        $dorm->update($request->validated());

        return new DormResource($dorm);
    }

    public function destroy(Dorm $dorm)
    {
        $this->authorizeIfEnabled('delete', $dorm);

        $dorm->delete();

        return response()->json([
            'message' => 'Dorm deleted successfully.',
            'errors' => [],
        ]);
    }

    public function createDormAdmin(CreateDormAdminRequest $request, Dorm $dorm)
    {
        $this->authorizeIfEnabled('createDormAdmin', $dorm);
        $universityId = $this->requireUniversityId($request);

        $user = User::create([
            'name' => $request->validated()['name'],
            'email' => $request->validated()['email'],
            'password' => Hash::make($request->validated()['password']),
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

        return response()->json([
            'message' => 'Dorm admin created successfully.',
            'errors' => [],
            'user' => new UserResource($user),
        ], 201);
    }
}
