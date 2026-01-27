<?php

namespace Tests\Feature;

use App\Models\Dorm;
use App\Models\DormAdmin;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_contract(): void
    {
        $password = 'Secret123!';
        $user = User::factory()->create([
            'password' => Hash::make($password),
            'role' => User::ROLE_DORM_ADMIN,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'token',
            'user' => ['id', 'name', 'email', 'role'],
        ]);
    }

    public function test_api_me_contract(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_DORM_ADMIN,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'name', 'email', 'role'],
        ]);
    }

    public function test_api_logout_contract(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_DORM_ADMIN,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'errors']);
    }

    public function test_dorm_admin_endpoints_contract(): void
    {
        $context = $this->seedDormAdminContext();

        Sanctum::actingAs($context['user']);

        $floorsResponse = $this->getJson('/api/floors');
        $floorsResponse->assertStatus(200);
        $floorsResponse->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'dorm_id',
                    'number',
                    'bathrooms',
                    'kitchens',
                    'showers',
                ],
            ],
        ]);

        $roomsResponse = $this->getJson('/api/rooms');
        $roomsResponse->assertStatus(200);
        $roomsResponse->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'dorm_id',
                    'floor_id',
                    'room_number',
                    'capacity',
                    'status',
                    'occupancy',
                ],
            ],
        ]);

        $studentsResponse = $this->getJson('/api/students');
        $studentsResponse->assertStatus(200);
        $studentsResponse->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'user_id',
                    'dorm_id',
                    'full_name',
                    'student_no',
                    'phone',
                    'email',
                ],
            ],
        ]);

        $assignResponse = $this->postJson("/api/rooms/{$context['room']->id}/assign-student", [
            'student_id' => $context['student']->id,
        ]);
        $assignResponse->assertStatus(200);
        $assignResponse->assertJsonStructure(['message', 'errors', 'assignment_id']);

        $occupantsResponse = $this->getJson("/api/rooms/{$context['room']->id}/occupants");
        $occupantsResponse->assertStatus(200);
        $occupantsResponse->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'user_id',
                    'dorm_id',
                    'full_name',
                    'student_no',
                    'phone',
                    'email',
                ],
            ],
        ]);
    }

    public function test_university_dorm_endpoints_contract(): void
    {
        $university = University::firstOrCreate(
            ['code' => 'DEFAULT'],
            ['name' => 'Default University']
        );

        $user = User::factory()->create([
            'role' => User::ROLE_UNIVERSITY_ADMIN,
            'university_id' => $university->id,
        ]);

        $dorm = Dorm::create([
            'name' => 'Dorm A',
            'university_id' => $university->id,
            'address' => 'Campus',
        ]);

        Sanctum::actingAs($user);

        $indexResponse = $this->getJson('/api/dorms');
        $indexResponse->assertStatus(200);
        $indexResponse->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'university_id',
                    'code',
                    'name',
                    'address',
                    'capacity',
                    'status',
                    'contact_name',
                    'contact_email',
                    'contact_phone',
                    'notes',
                ],
            ],
        ]);

        $storeResponse = $this->postJson('/api/dorms', [
            'name' => 'Dorm B',
            'address' => 'North Campus',
        ]);
        $storeResponse->assertStatus(201);
        $storeResponse->assertJsonStructure([
            'data' => [
                'id',
                'university_id',
                'code',
                'name',
                'address',
                'capacity',
                'status',
                'contact_name',
                'contact_email',
                'contact_phone',
                'notes',
            ],
        ]);
        $storeResponse->assertJsonPath('data.name', 'Dorm B');

        $showResponse = $this->getJson("/api/dorms/{$dorm->id}");
        $showResponse->assertStatus(200);
        $showResponse->assertJsonStructure([
            'data' => [
                'id',
                'university_id',
                'code',
                'name',
                'address',
                'capacity',
                'status',
                'contact_name',
                'contact_email',
                'contact_phone',
                'notes',
            ],
        ]);

        $createAdminResponse = $this->postJson("/api/dorms/{$dorm->id}/create-dorm-admin", [
            'name' => 'Dorm Manager',
            'email' => 'dorm.manager@test.com',
            'password' => 'Password123!',
        ]);
        $createAdminResponse->assertStatus(201);
        $createAdminResponse->assertJsonStructure([
            'message',
            'errors',
            'user' => ['id', 'name', 'email', 'role'],
        ]);

        $deleteResponse = $this->deleteJson("/api/dorms/{$dorm->id}");
        $deleteResponse->assertStatus(200);
        $deleteResponse->assertJsonStructure(['message', 'errors']);
    }

    private function seedDormAdminContext(): array
    {
        $university = University::firstOrCreate(
            ['code' => 'DEFAULT'],
            ['name' => 'Default University']
        );

        $dorm = Dorm::create([
            'name' => 'Dorm A',
            'university_id' => $university->id,
            'address' => 'Campus',
        ]);

        $user = User::factory()->create([
            'role' => User::ROLE_DORM_ADMIN,
            'university_id' => $university->id,
        ]);

        DormAdmin::create([
            'user_id' => $user->id,
            'dorm_id' => $dorm->id,
        ]);

        $floor = Floor::create([
            'dorm_id' => $dorm->id,
            'number' => 1,
            'bathrooms' => 2,
            'kitchens' => 1,
            'showers' => 2,
        ]);

        $room = Room::create([
            'dorm_id' => $dorm->id,
            'floor_id' => $floor->id,
            'room_number' => '101',
            'capacity' => 2,
            'status' => 'AVAILABLE',
        ]);

        $studentUser = User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'university_id' => $university->id,
        ]);

        $student = Student::create([
            'user_id' => $studentUser->id,
            'dorm_id' => $dorm->id,
            'full_name' => 'Student One',
            'student_no' => 'S-001',
            'phone' => '123456',
        ]);

        return [
            'user' => $user,
            'dorm' => $dorm,
            'floor' => $floor,
            'room' => $room,
            'student' => $student,
        ];
    }
}
