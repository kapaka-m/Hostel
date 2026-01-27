import 'package:flutter/material.dart';

import 'package:hostel_mobile/src/api/api_client.dart';
import 'package:hostel_mobile/src/api/repositories/report_repository.dart';
import 'package:hostel_mobile/src/models/report_overview_model.dart';

class ReportsProvider extends ChangeNotifier {
  final ReportRepository _repository;

  ReportsProvider(this._repository);

  bool isLoading = false;
  String? errorMessage;
  ReportOverview? overview;

  Future<void> load() async {
    isLoading = true;
    errorMessage = null;
    notifyListeners();

    try {
      overview = await _repository.fetchOverview();
    } catch (error) {
      errorMessage = _resolveError(error);
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  String _resolveError(Object error) {
    if (error is ApiException) {
      return error.message;
    }
    return 'Unable to load overview.';
  }
}
