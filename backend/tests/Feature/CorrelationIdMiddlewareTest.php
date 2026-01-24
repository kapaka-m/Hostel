<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CorrelationIdMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_correlation_id_is_returned_in_response(): void
    {
        SystemSetting::create([
            'key' => 'feature.correlation_ids',
            'value' => '1',
            'type' => 'bool',
        ]);

        $user = User::factory()->create([
            'role' => User::ROLE_DORM_ADMIN,
        ]);

        Sanctum::actingAs($user);

        $response = $this->withHeaders([
            'X-Correlation-Id' => 'test-correlation-id',
        ])->getJson('/api/me');

        $response->assertHeader('X-Correlation-Id', 'test-correlation-id');
    }
}
