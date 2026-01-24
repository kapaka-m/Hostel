<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['key' => 'feature.audit_logs', 'value' => '0', 'type' => 'bool'],
            ['key' => 'feature.activity_feed', 'value' => '0', 'type' => 'bool'],
            ['key' => 'feature.permissions', 'value' => '0', 'type' => 'bool'],
            ['key' => 'feature.correlation_ids', 'value' => '0', 'type' => 'bool'],
            ['key' => 'feature.response_time_logging', 'value' => '0', 'type' => 'bool'],
            ['key' => 'feature.user_freeze', 'value' => '0', 'type' => 'bool'],
            ['key' => 'feature.strong_passwords', 'value' => '0', 'type' => 'bool'],
            ['key' => 'feature.admin_ip_allowlist', 'value' => '0', 'type' => 'bool'],
            ['key' => 'feature.suspicious_login_alerts', 'value' => '0', 'type' => 'bool'],
            ['key' => 'security.admin_ip_allowlist', 'value' => json_encode([]), 'type' => 'json'],
        ];

        foreach ($defaults as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'updated_by' => null,
                ]
            );
        }
    }
}
