import 'package:flutter/material.dart';

import 'package:hostel_mobile/src/api/api_client.dart';
import 'package:hostel_mobile/src/api/response_parser.dart';
import 'package:hostel_mobile/src/api/repositories/ticket_repository.dart';
import 'package:hostel_mobile/src/models/ticket_comment_model.dart';
import 'package:hostel_mobile/src/models/ticket_model.dart';

class TicketsProvider extends ChangeNotifier {
  final TicketRepository _repository;

  TicketsProvider(this._repository);

  bool isLoading = false;
  bool isLoadingMore = false;
  String? errorMessage;
  List<TicketModel> items = [];
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
      final result = await _repository.fetchTickets(filters: filters, page: _page);
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
      final result = await _repository.fetchTickets(filters: filters, page: _page);
      _meta = result.meta;
      items = [...items, ...result.items];
    } catch (error) {
      errorMessage = _resolveError(error);
    } finally {
      isLoadingMore = false;
      notifyListeners();
    }
  }

  Future<TicketModel?> fetchDetail(int id) async {
    try {
      return await _repository.fetchTicket(id);
    } catch (error) {
      errorMessage = _resolveError(error);
      notifyListeners();
      return null;
    }
  }

  Future<bool> createTicket(Map<String, dynamic> payload) async {
    try {
      await _repository.createTicket(
        subject: payload['subject'],
        description: payload['description'],
        category: payload['category'],
        priority: payload['priority'],
        status: payload['status'],
        assignedTo: payload['assigned_to'],
        dormId: payload['dorm_id'],
      );
      await load(refresh: true);
      return true;
    } catch (error) {
      errorMessage = _resolveError(error);
      notifyListeners();
      return false;
    }
  }

  Future<bool> updateTicket(int id, Map<String, dynamic> payload) async {
    try {
      await _repository.updateTicket(id, payload);
      await load(refresh: true);
      return true;
    } catch (error) {
      errorMessage = _resolveError(error);
      notifyListeners();
      return false;
    }
  }

  Future<TicketCommentModel?> addComment(int id, String body) async {
    try {
      return await _repository.addComment(id, body);
    } catch (error) {
      errorMessage = _resolveError(error);
      notifyListeners();
      return null;
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
    return 'Unable to load tickets.';
  }
}
