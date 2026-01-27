import 'package:flutter/material.dart';

import 'package:hostel_mobile/src/api/api_client.dart';
import 'package:hostel_mobile/src/api/repositories/activity_feed_repository.dart';
import 'package:hostel_mobile/src/models/audit_log_model.dart';

class ActivityFeedProvider extends ChangeNotifier {
  final ActivityFeedRepository _repository;

  ActivityFeedProvider(this._repository);

  bool isLoading = false;
  bool isAvailable = true;
  String? errorMessage;
  List<AuditLogModel> entries = [];

  Future<void> load() async {
    isLoading = true;
    errorMessage = null;
    notifyListeners();

    try {
      entries = await _repository.fetchActivityFeed();
      isAvailable = true;
    } catch (error) {
      if (error is ApiException && error.featureDisabled) {
        isAvailable = false;
      } else {
        errorMessage = _resolveError(error);
      }
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  String _resolveError(Object error) {
    if (error is ApiException) {
      return error.message;
    }
    return 'Unable to load activity feed.';
  }
}
