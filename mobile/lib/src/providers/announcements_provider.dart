import 'package:flutter/material.dart';

import 'package:hostel_mobile/src/api/api_client.dart';
import 'package:hostel_mobile/src/api/response_parser.dart';
import 'package:hostel_mobile/src/api/repositories/announcement_repository.dart';
import 'package:hostel_mobile/src/models/announcement_model.dart';

class AnnouncementsProvider extends ChangeNotifier {
  final AnnouncementRepository _repository;

  AnnouncementsProvider(this._repository);

  bool isLoading = false;
  bool isLoadingMore = false;
  String? errorMessage;
  List<AnnouncementModel> items = [];
  PaginationMeta? _meta;
  int _page = 1;
  Map<String, dynamic> filters = {};

  bool get hasMore => _meta?.hasNextPage ?? false;

  Future<void> load({bool refresh = false}) async {
    if (isLoading) return;
    isLoading = true;
    if (refresh) {
      _page = 1;
      items = [];
    }
    errorMessage = null;
    notifyListeners();

    try {
      final result = await _repository.fetchAnnouncements(filters: filters, page: _page);
      _meta = result.meta;
      items = result.items;
    } catch (error) {
      errorMessage = _resolveError(error);
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
      final result = await _repository.fetchAnnouncements(filters: filters, page: _page);
      _meta = result.meta;
      items = [...items, ...result.items];
    } catch (error) {
      errorMessage = _resolveError(error);
    } finally {
      isLoadingMore = false;
      notifyListeners();
    }
  }

  Future<AnnouncementModel?> fetchDetail(int id) async {
    try {
      return await _repository.fetchAnnouncement(id);
    } catch (error) {
      errorMessage = _resolveError(error);
      notifyListeners();
      return null;
    }
  }

  Future<bool> createAnnouncement(Map<String, dynamic> payload) async {
    try {
      await _repository.createAnnouncement(payload);
      await load(refresh: true);
      return true;
    } catch (error) {
      errorMessage = _resolveError(error);
      notifyListeners();
      return false;
    }
  }

  Future<bool> updateAnnouncement(int id, Map<String, dynamic> payload) async {
    try {
      await _repository.updateAnnouncement(id, payload);
      await load(refresh: true);
      return true;
    } catch (error) {
      errorMessage = _resolveError(error);
      notifyListeners();
      return false;
    }
  }

  void setFilters(Map<String, dynamic> next) {
    filters = next;
    _page = 1;
    load(refresh: true);
  }

  String _resolveError(Object error) {
    if (error is ApiException) {
      return error.message;
    }
    return 'Unable to load announcements.';
  }
}
