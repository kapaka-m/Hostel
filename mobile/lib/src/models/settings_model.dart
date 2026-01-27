class SettingsModel {
  final Map<String, bool> featureFlags;
  final List<String> adminIpAllowlist;
  final int activityRetentionDays;

  SettingsModel({
    required this.featureFlags,
    required this.adminIpAllowlist,
    required this.activityRetentionDays,
  });

  factory SettingsModel.fromJson(Map<String, dynamic> json) {
    final flags = <String, bool>{};
    if (json['feature_flags'] is Map<String, dynamic>) {
      (json['feature_flags'] as Map<String, dynamic>).forEach((key, value) {
        flags[key] = value == true;
      });
    }

    final allowlistRaw = json['admin_ip_allowlist'];
    final allowlist = allowlistRaw is Iterable
        ? allowlistRaw.map((item) => item.toString()).toList()
        : <String>[];

    return SettingsModel(
      featureFlags: flags,
      adminIpAllowlist: allowlist,
      activityRetentionDays: json['activity_retention_days'] as int? ?? 90,
    );
  }
}
