import 'package:flutter/foundation.dart';

class AppConfig {
  static String get baseUrl {
    const override = String.fromEnvironment('API_BASE_URL');
    if (override.isNotEmpty) {
      return override;
    }

    if (kIsWeb) {
      return 'http://localhost:8000';
    }

    return defaultTargetPlatform == TargetPlatform.windows
        ? 'http://127.0.0.1:8000'
        : 'http://10.0.2.2:8000';
  }
}
