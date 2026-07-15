import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/api_constants.dart';
import '../datasources/storage_service.dart';
import '../models/dashboard_model.dart';

class DashboardRepository {
  final Dio _dio;
  final StorageService _storage;

  DashboardRepository(this._dio, this._storage);

  Future<DashboardResponse?> getMobileDashboard({
    String? filterTanggal,
    String? filterMinggu,
    String? filterBulan,
  }) async {
    try {
      final params = <String, dynamic>{};
      if (filterTanggal != null) params['filter_tanggal'] = filterTanggal;
      if (filterMinggu != null) params['filter_minggu'] = filterMinggu;
      if (filterBulan != null) params['filter_bulan'] = filterBulan;

      final response = await _dio.get(
        ApiConstants.dashboard,
        queryParameters: params,
      );

      return DashboardResponse.fromJson(response.data);
    } on DioException catch (e) {
      throw Exception(e.response?.data?['message'] ?? 'Gagal memuat dashboard');
    }
  }
}

final dashboardRepositoryProvider = Provider<DashboardRepository>((ref) {
  return DashboardRepository(ref.watch(dioProvider), ref.watch(storageServiceProvider));
});
