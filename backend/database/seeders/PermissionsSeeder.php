<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage_dorms',
            'manage_dorm_admins',
            'manage_floors',
            'manage_rooms',
            'assign_students',
            'manage_students',
            'view_reports',
            'view_audit_logs',
            'view_activity_feed',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $universityRole = Role::findOrCreate(User::ROLE_UNIVERSITY_ADMIN);
        $dormRole = Role::findOrCreate(User::ROLE_DORM_ADMIN);
        $studentRole = Role::findOrCreate(User::ROLE_STUDENT);

        $universityRole->syncPermissions($permissions);
        $dormRole->syncPermissions([
            'manage_floors',
            'manage_rooms',
            'assign_students',
            'manage_students',
            'view_reports',
        ]);
        $studentRole->syncPermissions([]);

        User::where('role', User::ROLE_UNIVERSITY_ADMIN)
            ->get()
            ->each(fn(User $user) => $user->syncRoles([$universityRole->name]));
        User::where('role', User::ROLE_DORM_ADMIN)
            ->get()
            ->each(fn(User $user) => $user->syncRoles([$dormRole->name]));
        User::where('role', User::ROLE_STUDENT)
            ->get()
            ->each(fn(User $user) => $user->syncRoles([$studentRole->name]));
    }
}
