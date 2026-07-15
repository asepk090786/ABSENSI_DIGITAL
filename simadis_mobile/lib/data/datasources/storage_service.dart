import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../constants/app_constants.dart';

class StorageService {
  final SharedPreferences _prefs;

  StorageService(this._prefs);

  Future<void> saveToken(String token) async {
    await _prefs.setString(AppConstants.storageTokenKey, token);
  }

  String? getToken() => _prefs.getString(AppConstants.storageTokenKey);

  Future<void> removeToken() async {
    await _prefs.remove(AppConstants.storageTokenKey);
  }

  Future<void> saveUser(String userJson) async {
    await _prefs.setString(AppConstants.storageUserKey, userJson);
  }

  String? getUser() => _prefs.getString(AppConstants.storageUserKey);

  Future<void> removeUser() async {
    await _prefs.remove(AppConstants.storageUserKey);
  }

  Future<void> clear() async {
    await _prefs.clear();
  }
}

final storageServiceProvider = Provider<StorageService>((ref) {
  throw UnimplementedError('storageServiceProvider must be provided in main');
});
