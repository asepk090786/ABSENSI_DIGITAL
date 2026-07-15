import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../core/theme/app_theme.dart';

final themeModeProvider = StateProvider<ThemeMode>((ref) => ThemeMode.system);

class ThemeNotifier extends StateNotifier<ThemeMode> {
  ThemeNotifier(this._prefs) : super(ThemeMode.system) {
    _load();
  }

  final SharedPreferences _prefs;

  Future<void> _load() async {
    final saved = await AppTheme.getSavedThemeMode();
    state = saved;
  }

  Future<void> setMode(ThemeMode mode) async {
    state = mode;
    await AppTheme.saveThemeMode(mode);
  }
}

final themeNotifierProvider =
    StateNotifierProvider<ThemeNotifier, ThemeMode>((ref) {
  final prefs = ref.watch(sharedPreferencesProvider);
  return ThemeNotifier(prefs);
});
