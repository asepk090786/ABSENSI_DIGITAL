import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/api_constants.dart';
import '../datasources/storage_service.dart';
import '../models/user_model.dart';

class AuthRepository {
  final Dio _dio;
  final StorageService _storage;

  AuthRepository(this._dio, this._storage);

  Future<Map<String, dynamic>> login(String login, String password) async {
    try {
      final response = await _dio.post(
        ApiConstants.login,
        data: {'login': login, 'password': password},
      );

      final token = response.data['token'] as String;
      final userJson = response.data['user'] as Map<String, dynamic>;

      await _storage.saveToken(token);
      await _storage.saveUser(UserModel.fromJson(userJson).toJson());

      return {'success': true, 'data': response.data};
    } on DioException catch (e) {
      final message = e.response?.data?['message'] ?? 'Terjadi kesalahan';
      return {'success': false, 'message': message};
    }
  }

  Future<void> logout() async {
    try {
      await _dio.post(ApiConstants.logout);
    } catch (_) {}
    await _storage.removeToken();
    await _storage.removeUser();
  }

  UserModel? getCurrentUser() {
    final json = _storage.getUser();
    if (json == null) return null;
    return UserModel.fromJson(json);
  }

  bool hasToken() => _storage.getToken() != null;
}

final authRepositoryProvider = Provider<AuthRepository>((ref) {
  return AuthRepository(ref.watch(dioProvider), ref.watch(storageServiceProvider));
});
