import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../data/repositories/auth_repository.dart';
import '../data/datasources/storage_service.dart';

final currentUserProvider = Provider<UserModel?>((ref) {
  final authRepo = ref.watch(authRepositoryProvider);
  return authRepo.getCurrentUser();
});

final isLoggedInProvider = Provider<bool>((ref) {
  final authRepo = ref.watch(authRepositoryProvider);
  return authRepo.hasToken();
});

final userRoleProvider = Provider<List<String>?>((ref) {
  final user = ref.watch(currentUserProvider);
  return user?.roles;
});

final authLoadingProvider = StateProvider<bool>((ref) => false);
