<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_log_is_recorded_for_dorm_creation(): void
    {
        SystemSetting::create([
            'key' => 'feature.audit_logs',
            'value' => '1',
            'type' => 'bool',
        ]);

        $user = User::factory()->create([
            'role' => User::ROLE_UNIVERSITY_ADMIN,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/dorms', [
            'name' => 'Dorm A',
            'address' => null,
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'dorm.created',
            'actor_id' => $user->id,
        ]);

        $this->assertTrue(AuditLog::query()->exists());
    }
}
