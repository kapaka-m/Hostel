<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Support\FeatureFlags;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UniversitySettingsController extends Controller
{
    public function index(Request $request)
    {
        $flags = [];
        $defaults = config('feature-flags.defaults', []);

        foreach ($defaults as $flag => $default) {
            $flags[$flag] = FeatureFlags::enabled($flag);
        }

        return view('admin.university.settings.index', [
            'featureFlags' => $flags,
            'adminIpAllowlist' => SystemSetting::getValue('security.admin_ip_allowlist', []),
            'activityRetentionDays' => SystemSetting::getValue('activity.retention_days', 90),
            'currentIp' => $request->ip(),
        ]);
    }

    public function update(Request $request)
    {
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

        if ($request->boolean('include_current_ip') && $request->ip()) {
            $allowlist[] = $request->ip();
        }

        $allowlist = array_values(array_unique($allowlist));

        foreach ($flags as $flag => $enabled) {
            $this->saveSetting('feature.' . $flag, $enabled ? 'true' : 'false', 'bool', $request->user()->id);
        }

        $this->saveSetting('security.admin_ip_allowlist', json_encode($allowlist), 'json', $request->user()->id);
        $this->saveSetting('activity.retention_days', (string) $retentionDays, 'int', $request->user()->id);

        FeatureFlags::clearCache();

        return redirect()->route('admin.university.settings.index')
            ->with('success', 'Settings updated successfully.');
    }

    private function saveSetting(string $key, ?string $value, string $type, int $userId): void
    {
        SystemSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type, 'updated_by' => $userId]
        );
    }
}
