<?php

namespace Tests\Feature;

use App\Models\Dorm;
use App\Models\DormAdmin;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoomAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_dorm_admin_can_assign_student_to_room(): void
    {
        $dorm = Dorm::create(['name' => 'Dorm A', 'address' => null]);
        $floor = Floor::create([
            'dorm_id' => $dorm->id,
            'number' => 1,
            'bathrooms' => 1,
            'kitchens' => 1,
            'showers' => 1,
        ]);
        $room = Room::create([
            'dorm_id' => $dorm->id,
            'floor_id' => $floor->id,
            'room_number' => '101',
            'capacity' => 2,
            'status' => 'AVAILABLE',
        ]);

        $admin = User::factory()->create([
            'password' => Hash::make('Admin123!'),
            'role' => User::ROLE_DORM_ADMIN,
        ]);
        DormAdmin::create([
            'user_id' => $admin->id,
            'dorm_id' => $dorm->id,
        ]);

        $studentUser = User::factory()->create([
            'password' => Hash::make('Student123!'),
            'role' => User::ROLE_STUDENT,
        ]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'dorm_id' => $dorm->id,
            'full_name' => 'Student One',
            'student_no' => '2024001',
            'phone' => null,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson("/api/rooms/{$room->id}/assign-student", [
            'student_id' => $student->id,
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonPath('message', 'Student assigned successfully.');

        $this->assertDatabaseHas('room_assignments', [
            'student_id' => $student->id,
            'room_id' => $room->id,
            'active' => true,
        ]);
    }

    public function test_dorm_admin_only_sees_own_rooms(): void
    {
        $dormA = Dorm::create(['name' => 'Dorm A', 'address' => null]);
        $dormB = Dorm::create(['name' => 'Dorm B', 'address' => null]);

        $floorA = Floor::create([
            'dorm_id' => $dormA->id,
            'number' => 1,
            'bathrooms' => 1,
            'kitchens' => 1,
            'showers' => 1,
        ]);
        $floorB = Floor::create([
            'dorm_id' => $dormB->id,
            'number' => 1,
            'bathrooms' => 1,
            'kitchens' => 1,
            'showers' => 1,
        ]);

        $roomA = Room::create([
            'dorm_id' => $dormA->id,
            'floor_id' => $floorA->id,
            'room_number' => '101',
            'capacity' => 2,
            'status' => 'AVAILABLE',
        ]);
        Room::create([
            'dorm_id' => $dormB->id,
            'floor_id' => $floorB->id,
            'room_number' => '201',
            'capacity' => 2,
            'status' => 'AVAILABLE',
        ]);

        $admin = User::factory()->create([
            'password' => Hash::make('Admin123!'),
            'role' => User::ROLE_DORM_ADMIN,
        ]);
        DormAdmin::create([
            'user_id' => $admin->id,
            'dorm_id' => $dormA->id,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/rooms');

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $roomA->id);
    }

    public function test_dorm_admin_cannot_assign_student_in_other_dorm(): void
    {
        $dormA = Dorm::create(['name' => 'Dorm A', 'address' => null]);
        $dormB = Dorm::create(['name' => 'Dorm B', 'address' => null]);

        $floorA = Floor::create([
            'dorm_id' => $dormA->id,
            'number' => 1,
            'bathrooms' => 1,
            'kitchens' => 1,
            'showers' => 1,
        ]);
        $floorB = Floor::create([
            'dorm_id' => $dormB->id,
            'number' => 1,
            'bathrooms' => 1,
            'kitchens' => 1,
            'showers' => 1,
        ]);

        $roomB = Room::create([
            'dorm_id' => $dormB->id,
            'floor_id' => $floorB->id,
            'room_number' => '201',
            'capacity' => 2,
            'status' => 'AVAILABLE',
        ]);

        $admin = User::factory()->create([
            'password' => Hash::make('Admin123!'),
            'role' => User::ROLE_DORM_ADMIN,
        ]);
        DormAdmin::create([
            'user_id' => $admin->id,
            'dorm_id' => $dormA->id,
        ]);

        $studentUser = User::factory()->create([
            'password' => Hash::make('Student123!'),
            'role' => User::ROLE_STUDENT,
        ]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'dorm_id' => $dormA->id,
            'full_name' => 'Student One',
            'student_no' => '2024002',
            'phone' => null,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson("/api/rooms/{$roomB->id}/assign-student", [
            'student_id' => $student->id,
        ]);

        $response->assertStatus(403);
    }
}
