<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\User;
use App\Support\FeatureFlags;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role !== User::ROLE_UNIVERSITY_ADMIN) {
            abort(403, 'Unauthorized.');
        }

        $defaults = config('feature-flags.defaults', []);
        $flags = [];

        foreach ($defaults as $flag => $default) {
            $flags[$flag] = FeatureFlags::enabled($flag);
        }

        return response()->json([
            'feature_flags' => $flags,
            'admin_ip_allowlist' => SystemSetting::getValue('security.admin_ip_allowlist', []),
            'activity_retention_days' => SystemSetting::getValue('activity.retention_days', 90),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        if ($user->role !== User::ROLE_UNIVERSITY_ADMIN) {
            abort(403, 'Unauthorized.');
        }

        $defaults = config('feature-flags.defaults', []);
        $flagKeys = array_keys($defaults);

        $data = $request->all();
        $flags = [];

        foreach ($flagKeys as $flag) {
            $flags[$flag] = array_key_exists($flag, $data);
        }

        $retentionDays = (int) $request->input('activity_retention_days', 90);
        if ($retentionDays < 1) {
            throw ValidationException::withMessages([
                'activity_retention_days' => ['Retention days must be at least 1.'],
            ]);
        }

        $allowlistRaw = (string) $request->input('admin_ip_allowlist', '');
        $allowlist = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $allowlistRaw) ?: [])));

        foreach ($allowlist as $ip) {
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                throw ValidationException::withMessages([
                    'admin_ip_allowlist' => ['Invalid IP address: ' . $ip],
                ]);
            }
        }

        foreach ($flags as $flag => $enabled) {
            $this->saveSetting('feature.' . $flag, $enabled ? 'true' : 'false', 'bool', $user->id);
        }

        $this->saveSetting('security.admin_ip_allowlist', json_encode($allowlist), 'json', $user->id);
        $this->saveSetting('activity.retention_days', (string) $retentionDays, 'int', $user->id);

        FeatureFlags::clearCache();

        return response()->json([
            'message' => 'Settings updated successfully.',
            'errors' => [],
        ]);
    }

    private function saveSetting(string $key, ?string $value, string $type, int $userId): void
    {
        SystemSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type, 'updated_by' => $userId]
        );
    }
}
