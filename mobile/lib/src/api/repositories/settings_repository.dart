import 'package:hostel_mobile/src/api/api_client.dart';
import 'package:hostel_mobile/src/api/endpoints.dart';
import 'package:hostel_mobile/src/api/response_parser.dart';
import 'package:hostel_mobile/src/models/settings_model.dart';

class SettingsRepository {
  final ApiClient _client;

  SettingsRepository(this._client);

  Future<SettingsModel> fetchSettings() async {
    final response = await _client.get(Endpoints.settings);
    return SettingsModel.fromJson(ensureMap(response.data));
  }

  Future<void> updateSettings({
    required Map<String, bool> featureFlags,
    required List<String> adminIpAllowlist,
    required int activityRetentionDays,
  }) async {
    final payload = <String, dynamic>{
      'admin_ip_allowlist': adminIpAllowlist.join('\n'),
      'activity_retention_days': activityRetentionDays,
    };

    for (final entry in featureFlags.entries) {
      if (entry.value) {
        payload[entry.key] = true;
      }
    }

    await _client.post(Endpoints.settings, data: payload);
  }
}
