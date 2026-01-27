import 'package:flutter/material.dart';

class AppTheme {
  static const _brand = Color(0xFF0F766E);
  static const _surfaceLight = Color(0xFFF8FAFC);
  static const _surfaceDark = Color(0xFF0F172A);

  static final lightTheme = ThemeData(
    useMaterial3: true,
    colorScheme: ColorScheme.fromSeed(seedColor: _brand).copyWith(
      primary: _brand,
      surface: _surfaceLight,
    ),
    brightness: Brightness.light,
    scaffoldBackgroundColor: _surfaceLight,
    inputDecorationTheme: const InputDecorationTheme(
      border: OutlineInputBorder(),
    ),
    appBarTheme: const AppBarTheme(
      elevation: 0,
      centerTitle: false,
      surfaceTintColor: Colors.transparent,
    ),
    cardTheme: CardThemeData(
      elevation: 0,
      color: Colors.white,
      surfaceTintColor: Colors.transparent,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
    ),
    navigationBarTheme: const NavigationBarThemeData(
      elevation: 0,
      height: 68,
    ),
    navigationRailTheme: const NavigationRailThemeData(
      elevation: 0,
      backgroundColor: Colors.white,
    ),
    chipTheme: ChipThemeData(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
    ),
    listTileTheme: const ListTileThemeData(
      contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 6),
    ),
  );

  static final darkTheme = ThemeData(
    useMaterial3: true,
    colorScheme: ColorScheme.fromSeed(
      seedColor: _brand,
      brightness: Brightness.dark,
    ).copyWith(
      primary: _brand,
      surface: _surfaceDark,
    ),
    brightness: Brightness.dark,
    scaffoldBackgroundColor: _surfaceDark,
    inputDecorationTheme: const InputDecorationTheme(
      border: OutlineInputBorder(),
    ),
    appBarTheme: const AppBarTheme(
      elevation: 0,
      centerTitle: false,
      surfaceTintColor: Colors.transparent,
    ),
    cardTheme: CardThemeData(
      elevation: 0,
      color: const Color(0xFF111827),
      surfaceTintColor: Colors.transparent,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
    ),
    navigationBarTheme: const NavigationBarThemeData(
      elevation: 0,
      height: 68,
    ),
    navigationRailTheme: const NavigationRailThemeData(
      elevation: 0,
    ),
    chipTheme: ChipThemeData(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
    ),
    listTileTheme: const ListTileThemeData(
      contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 6),
    ),
  );
}
