<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateDormAdminRequest;
use App\Models\Dorm;
use App\Models\DormAdmin;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UniversityDormAdminController extends Controller
{
    public function create()
    {
        $dorms = Dorm::orderBy('name')->get();

        return view('admin.university.dorm-admins.create', [
            'dorms' => $dorms,
        ]);
    }

    public function store(CreateDormAdminRequest $request)
    {
        $data = $request->validated();

        $dorm = Dorm::findOrFail($data['dorm_id']);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_DORM_ADMIN,
        ]);

        DormAdmin::create([
            'user_id' => $user->id,
            'dorm_id' => $dorm->id,
        ]);

        return redirect()->route('admin.university.dorms.index')
            ->with('success', 'Dorm admin created successfully.');
    }
}
