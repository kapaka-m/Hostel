<?php

namespace Tests\Feature;

use App\Models\Dorm;
use App\Models\DormAdmin;
use App\Models\Floor;
use App\Models\Room;
use App\Models\SystemSetting;
use App\Models\University;
use App\Models\User;
use App\Support\FeatureFlags;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthorizationScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_university_admin_cannot_access_other_university_dorm(): void
    {
        SystemSetting::create([
            'key' => 'feature.permissions',
            'value' => '0',
            'type' => 'bool',
        ]);
        FeatureFlags::clearCache();

        $universityA = University::create(['name' => 'Uni A', 'code' => 'UNIA']);
        $universityB = University::create(['name' => 'Uni B', 'code' => 'UNIB']);

        $adminA = User::factory()->create([
            'role' => User::ROLE_UNIVERSITY_ADMIN,
            'university_id' => $universityA->id,
        ]);

        $dormB = Dorm::create([
            'name' => 'Dorm B',
            'address' => null,
            'university_id' => $universityB->id,
        ]);

        Sanctum::actingAs($adminA);

        $response = $this->getJson("/api/dorms/{$dormB->id}");

        $response->assertStatus(403);
    }

    public function test_dorm_admin_cannot_view_room_from_other_dorm_when_permissions_off(): void
    {
        SystemSetting::create([
            'key' => 'feature.permissions',
            'value' => '0',
            'type' => 'bool',
        ]);
        FeatureFlags::clearCache();

        $university = University::create(['name' => 'Uni A', 'code' => 'UNIA']);

        $dormA = Dorm::create([
            'name' => 'Dorm A',
            'address' => null,
            'university_id' => $university->id,
        ]);
        $dormB = Dorm::create([
            'name' => 'Dorm B',
            'address' => null,
            'university_id' => $university->id,
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
            'room_number' => '101',
            'capacity' => 2,
            'status' => 'AVAILABLE',
        ]);

        $admin = User::factory()->create([
            'role' => User::ROLE_DORM_ADMIN,
            'university_id' => $university->id,
        ]);
        DormAdmin::create([
            'user_id' => $admin->id,
            'dorm_id' => $dormA->id,
        ]);

        $response = $this->actingAs($admin)->get("/admin/dorm/rooms/{$roomB->id}");

        $response->assertStatus(403);
    }
}
