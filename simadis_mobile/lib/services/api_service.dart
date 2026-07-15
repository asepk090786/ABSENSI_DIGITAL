import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../constants/app_constants.dart';

final sharedPreferencesProvider = Provider<SharedPreferences>((ref) {
  throw UnimplementedError('sharedPreferencesProvider must be overridden');
});

final dioProvider = Provider<Dio>((ref) {
  final prefs = ref.watch(sharedPreferencesProvider);
  final token = prefs.getString(AppConstants.storageTokenKey);

  final dio = Dio(BaseOptions(
    baseUrl: 'http://10.0.2.2:8000/api',
    connectTimeout: const Duration(seconds: 30),
    receiveTimeout: const Duration(seconds: 30),
    headers: {
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    },
  ));

  dio.interceptors.add(InterceptorsWrapper(
    onError: (error, handler) async {
      if (error.response?.statusCode == 401) {
        final prefs = await SharedPreferences.getInstance();
        await prefs.remove(AppConstants.storageTokenKey);
        await prefs.remove(AppConstants.storageUserKey);
      }
      return handler.next(error);
    },
  ));

  return dio;
});
