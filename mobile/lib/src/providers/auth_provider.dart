import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../models/user.dart';
import '../services/api_service.dart';

class AuthProvider extends ChangeNotifier {
  AuthProvider(this._apiService);

  static const _tokenKey = 'auth_token';

  final ApiService _apiService;

  User? _user;
  String? _token;
  bool _initialized = false;
  bool _busy = false;

  User? get user => _user;
  bool get isAuthenticated => _user != null;
  bool get isInitialized => _initialized;
  bool get isBusy => _busy;
  ApiService get api => _apiService;

  Future<void> initialize() async {
    if (_initialized) {
      return;
    }

    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString(_tokenKey);

    if (_token != null && _token!.isNotEmpty) {
      _apiService.setToken(_token);
      try {
        _user = await _apiService.me();
      } catch (_) {
        await _clearSession(prefs);
      }
    }

    _initialized = true;
    notifyListeners();
  }

  Future<void> login(String email, String password) async {
    _setBusy(true);
    try {
      final result = await _apiService.login(email, password);
      _token = result.token;
      _user = result.user;
      _apiService.setToken(_token);

      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_tokenKey, _token ?? '');
    } finally {
      _setBusy(false);
    }
  }

  Future<void> logout() async {
    _setBusy(true);
    try {
      await _apiService.logout();
    } catch (_) {}

    final prefs = await SharedPreferences.getInstance();
    await _clearSession(prefs);
    _setBusy(false);
  }

  Future<void> _clearSession(SharedPreferences prefs) async {
    _token = null;
    _user = null;
    _apiService.setToken(null);
    await prefs.remove(_tokenKey);
    notifyListeners();
  }

  void _setBusy(bool value) {
    _busy = value;
    notifyListeners();
  }
}
