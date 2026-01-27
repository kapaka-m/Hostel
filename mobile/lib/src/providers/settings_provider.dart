import 'package:flutter/material.dart';

import 'package:hostel_mobile/src/api/api_client.dart';
import 'package:hostel_mobile/src/api/repositories/settings_repository.dart';
import 'package:hostel_mobile/src/models/settings_model.dart';

class SettingsProvider extends ChangeNotifier {
  final SettingsRepository _repository;

  SettingsProvider(this._repository);

  bool isLoading = false;
  bool isSaving = false;
  String? errorMessage;
  SettingsModel? settings;

  Map<String, bool> get featureFlags => settings?.featureFlags ?? {};

  Future<void> load() async {
    isLoading = true;
    errorMessage = null;
    notifyListeners();

    try {
      settings = await _repository.fetchSettings();
    } catch (error) {
      errorMessage = _resolveError(error);
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> update({
    required Map<String, bool> featureFlags,
    required List<String> adminIpAllowlist,
    required int activityRetentionDays,
  }) async {
    isSaving = true;
    errorMessage = null;
    notifyListeners();

    try {
      await _repository.updateSettings(
        featureFlags: featureFlags,
        adminIpAllowlist: adminIpAllowlist,
        activityRetentionDays: activityRetentionDays,
      );
      await load();
      return true;
    } catch (error) {
      errorMessage = _resolveError(error);
      return false;
    } finally {
      isSaving = false;
      notifyListeners();
    }
  }

  String _resolveError(Object error) {
    if (error is ApiException) {
      return error.message;
    }
    return 'Unable to load settings.';
  }
}
