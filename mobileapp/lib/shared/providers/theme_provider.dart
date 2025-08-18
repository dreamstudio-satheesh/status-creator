import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../core/constants/app_constants.dart';

class ThemeNotifier extends StateNotifier<ThemeMode> {
  ThemeNotifier() : super(ThemeMode.system) {
    _loadTheme();
  }

  Future<void> _loadTheme() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final themeString = prefs.getString(AppConstants.themeKey) ?? 'system';
      
      switch (themeString) {
        case 'light':
          state = ThemeMode.light;
          break;
        case 'dark':
          state = ThemeMode.dark;
          break;
        default:
          state = ThemeMode.system;
          break;
      }
    } catch (e) {
      // If loading fails, use system default
      state = ThemeMode.system;
    }
  }

  Future<void> setTheme(ThemeMode themeMode) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      String themeString;
      
      switch (themeMode) {
        case ThemeMode.light:
          themeString = 'light';
          break;
        case ThemeMode.dark:
          themeString = 'dark';
          break;
        default:
          themeString = 'system';
          break;
      }
      
      await prefs.setString(AppConstants.themeKey, themeString);
      state = themeMode;
    } catch (e) {
      // If saving fails, still update the state
      state = themeMode;
    }
  }

  bool get isLight => state == ThemeMode.light;
  bool get isDark => state == ThemeMode.dark;
  bool get isSystem => state == ThemeMode.system;
}

// Provider for theme state
final themeProvider = StateNotifierProvider<ThemeNotifier, ThemeMode>((ref) {
  return ThemeNotifier();
});

// Helper provider to get current brightness
final currentBrightnessProvider = Provider<Brightness>((ref) {
  final themeMode = ref.watch(themeProvider);
  
  switch (themeMode) {
    case ThemeMode.light:
      return Brightness.light;
    case ThemeMode.dark:
      return Brightness.dark;
    case ThemeMode.system:
      // This would need to be determined by the system
      // For now, default to light
      return Brightness.light;
  }
});

// Provider to check if current theme is dark
final isDarkThemeProvider = Provider<bool>((ref) {
  final brightness = ref.watch(currentBrightnessProvider);
  return brightness == Brightness.dark;
});

// Extension for easy theme switching
extension ThemeExtension on WidgetRef {
  void toggleTheme() {
    final currentTheme = read(themeProvider);
    final themeNotifier = read(themeProvider.notifier);
    
    switch (currentTheme) {
      case ThemeMode.light:
        themeNotifier.setTheme(ThemeMode.dark);
        break;
      case ThemeMode.dark:
        themeNotifier.setTheme(ThemeMode.system);
        break;
      case ThemeMode.system:
        themeNotifier.setTheme(ThemeMode.light);
        break;
    }
  }
  
  void setLightTheme() {
    read(themeProvider.notifier).setTheme(ThemeMode.light);
  }
  
  void setDarkTheme() {
    read(themeProvider.notifier).setTheme(ThemeMode.dark);
  }
  
  void setSystemTheme() {
    read(themeProvider.notifier).setTheme(ThemeMode.system);
  }
}