import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'app.dart';
import 'core/theme/app_theme.dart';
import 'data/datasources/storage_service.dart';
import 'services/api_service.dart';
import 'shared/providers/theme_provider.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final prefs = await SharedPreferences.getInstance();
  final themeMode = await AppTheme.getSavedThemeMode();

  runApp(
    ProviderScope(
      overrides: [
        sharedPreferencesProvider.overrideWithValue(prefs),
        storageServiceProvider.overrideWithValue(StorageService(prefs)),
        themeModeProvider.overrideWith((ref) => themeMode),
      ],
      child: const App(),
    ),
  );
}
