import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';

class StoredSession {
  final String token;
  final Map<String, dynamic> user;

  StoredSession({
    required this.token,
    required this.user,
  });
}

abstract class TokenStorage {
  Future<void> saveSession(StoredSession session);

  Future<StoredSession?> readSession();

  Future<void> clearSession();
}

TokenStorage createTokenStorage() {
  if (kIsWeb) {
    return _PrefTokenStorage();
  }

  return _SecureTokenStorage();
}

const _tokenKey = 'hostel_token';
const _userKey = 'hostel_user';

class _SecureTokenStorage implements TokenStorage {
  final FlutterSecureStorage _secureStorage;

  _SecureTokenStorage() : _secureStorage = const FlutterSecureStorage();

  @override
  Future<void> saveSession(StoredSession session) async {
    await _secureStorage.write(key: _tokenKey, value: session.token);
    await _secureStorage.write(key: _userKey, value: jsonEncode(session.user));
  }

  @override
  Future<StoredSession?> readSession() async {
    final token = await _secureStorage.read(key: _tokenKey);
    final userJson = await _secureStorage.read(key: _userKey);

    if (token == null || userJson == null) {
      return null;
    }

    final user = _decodeUser(userJson);
    if (user == null) {
      await clearSession();
      return null;
    }

    return StoredSession(token: token, user: user);
  }

  @override
  Future<void> clearSession() async {
    await Future.wait([
      _secureStorage.delete(key: _tokenKey),
      _secureStorage.delete(key: _userKey),
    ]);
  }

  Map<String, dynamic>? _decodeUser(String value) {
    try {
      final decoded = jsonDecode(value);
      return decoded is Map<String, dynamic> ? decoded : null;
    } catch (_) {
      return null;
    }
  }
}

class _PrefTokenStorage implements TokenStorage {
  SharedPreferences? _prefs;

  Future<SharedPreferences> get _instance async {
    return _prefs ??= await SharedPreferences.getInstance();
  }

  @override
  Future<void> saveSession(StoredSession session) async {
    final instance = await _instance;
    await instance.setString(_tokenKey, session.token);
    await instance.setString(_userKey, jsonEncode(session.user));
  }

  @override
  Future<StoredSession?> readSession() async {
    final instance = await _instance;
    final token = instance.getString(_tokenKey);
    final userJson = instance.getString(_userKey);

    if (token == null || userJson == null) {
      return null;
    }

    final user = _decodeUser(userJson);
    if (user == null) {
      await clearSession();
      return null;
    }

    return StoredSession(token: token, user: user);
  }

  @override
  Future<void> clearSession() async {
    final instance = await _instance;
    await instance.remove(_tokenKey);
    await instance.remove(_userKey);
  }

  Map<String, dynamic>? _decodeUser(String value) {
    try {
      final decoded = jsonDecode(value);
      return decoded is Map<String, dynamic> ? decoded : null;
    } catch (_) {
      return null;
    }
  }
}
