import 'dart:async';

import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';

import 'package:hostel_mobile/src/api/api_client.dart';
import 'package:hostel_mobile/src/auth/token_storage.dart';
import 'package:hostel_mobile/src/models/user_model.dart';
import 'package:hostel_mobile/src/api/repositories/auth_repository.dart';

class AuthProvider extends ChangeNotifier {
  final AuthRepository _repository;
  final TokenStorage _storage;
  final ApiClient _client;

  AuthProvider(this._repository, this._storage, this._client);

  UserModel? _user;
  bool _isLoading = false;
  bool _isRestoring = true;
  Map<String, List<String>> validationErrors = {};
  String? errorMessage;

  UserModel? get user => _user;
  bool get isAuthenticated => _user != null;
  bool get isLoading => _isLoading;
  bool get isRestoring => _isRestoring;

  Future<void> initialize() async {
    final session = await _storage.readSession();

    if (session != null) {
      _client.setToken(session.token);
      try {
        final me = await _repository.me();
        _user = me;
        await _storage.saveSession(StoredSession(
          token: session.token,
          user: me.toJson(),
        ));
      } catch (_) {
        await _clearSession();
      }
    }

    _isRestoring = false;
    notifyListeners();
  }

  Future<void> login({
    required String email,
    required String password,
  }) async {
    _isLoading = true;
    validationErrors = {};
    errorMessage = null;
    notifyListeners();

    try {
      final session = await _repository.login(email: email, password: password);
      _client.setToken(session.token);
      _user = session.user;
      await _storage.saveSession(StoredSession(
        token: session.token,
        user: session.user.toJson(),
      ));
    } catch (error) {
      if (error is ApiException) {
        validationErrors = error.errors;
        errorMessage = error.message;
      } else {
        errorMessage = 'Unable to sign in right now. Please try again later.';
      }
      rethrow;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> logout() async {
    try {
      await _repository.logout();
    } catch (_) {}

    await _clearSession();
    notifyListeners();
  }

  void handleUnauthorized() {
    unawaited(_clearSession());
    notifyListeners();
  }

  Future<void> _clearSession() async {
    _user = null;
    _client.setToken(null);
    await _storage.clearSession();
  }
}

