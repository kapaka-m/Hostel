import 'package:flutter/material.dart';

import 'package:hostel_mobile/src/api/api_client.dart';
import 'package:hostel_mobile/src/api/response_parser.dart';
import 'package:hostel_mobile/src/api/repositories/audit_log_repository.dart';
import 'package:hostel_mobile/src/models/audit_log_model.dart';

class AuditLogsProvider extends ChangeNotifier {
  final AuditLogRepository _repository;

  AuditLogsProvider(this._repository);

  bool isLoading = false;
  bool isLoadingMore = false;
  bool isAvailable = true;
  String? errorMessage;
  List<AuditLogModel> logs = [];
  PaginationMeta? _meta;
  int _page = 1;

  bool get hasMore => _meta?.hasNextPage ?? false;

  Future<void> load({bool refresh = false}) async {
    if (isLoading) return;
    isLoading = true;
    if (refresh) {
      _page = 1;
      logs = [];
    }
    errorMessage = null;
    notifyListeners();

    try {
      final result = await _repository.fetchAuditLogs(page: _page);
      _meta = result.meta;
      logs = result.items;
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

  Future<void> loadMore() async {
    if (isLoadingMore || !hasMore) return;
    isLoadingMore = true;
    notifyListeners();

    try {
      _page += 1;
      final result = await _repository.fetchAuditLogs(page: _page);
      _meta = result.meta;
      logs = [...logs, ...result.items];
    } catch (error) {
      errorMessage = _resolveError(error);
    } finally {
      isLoadingMore = false;
      notifyListeners();
    }
  }

  String _resolveError(Object error) {
    if (error is ApiException) {
      return error.message;
    }
    return 'Unable to load audit logs.';
  }
}
