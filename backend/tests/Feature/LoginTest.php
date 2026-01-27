<?php

namespace Tests\Feature;

use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_via_web(): void
    {
        $university = University::firstOrCreate(
            ['code' => 'DEFAULT'],
            ['name' => 'Default University']
        );

        $password = 'Secret123!';
        $user = User::factory()->create([
            'password' => Hash::make($password),
            'role' => User::ROLE_UNIVERSITY_ADMIN,
            'university_id' => $university->id,
        ]);

        $csrf = 'test_csrf_token';
        $response = $this->from('/login')
            ->withSession(['_token' => $csrf])
            ->post('/login', [
                '_token' => $csrf,
                'email' => $user->email,
                'password' => $password,
            ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.university.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_web_login_rejects_credentials(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Secret123!'),
            'role' => User::ROLE_UNIVERSITY_ADMIN,
        ]);

        $csrf = 'test_csrf_token';
        $response = $this->from('/login')
            ->withSession(['_token' => $csrf])
            ->post('/login', [
                '_token' => $csrf,
                'email' => $user->email,
                'password' => 'WrongPassword',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
    }
}
