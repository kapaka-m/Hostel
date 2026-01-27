import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ThemeProvider extends ChangeNotifier {
  static const _storageKey = 'hostel_theme_mode';
  ThemeMode _mode = ThemeMode.system;

  ThemeProvider() {
    _load();
  }

  ThemeMode get mode => _mode;

  bool get isDark => _mode == ThemeMode.dark;

  Future<void> setMode(ThemeMode mode) async {
    if (_mode == mode) return;
    _mode = mode;
    notifyListeners();
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_storageKey, mode.toString());
  }

  Future<void> _load() async {
    final prefs = await SharedPreferences.getInstance();
    final stored = prefs.getString(_storageKey);
    if (stored != null) {
      final selected = ThemeMode.values.firstWhere(
        (value) => value.toString() == stored,
        orElse: () => ThemeMode.system,
      );
      _mode = selected;
      notifyListeners();
    }
  }
}
