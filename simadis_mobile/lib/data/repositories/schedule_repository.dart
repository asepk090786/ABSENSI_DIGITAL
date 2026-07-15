import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/api_constants.dart';

class ScheduleRepository {
  final Dio _dio;

  ScheduleRepository(this._dio);

  Future<List<dynamic>> getSchedule() async {
    try {
      final response = await _dio.get(ApiConstants.mobileSchedule);
      return response.data['schedule'] as List;
    } on DioException catch (e) {
      throw Exception(e.response?.data?['message'] ?? 'Gagal memuat jadwal');
    }
  }
}

final scheduleRepositoryProvider = Provider<ScheduleRepository>((ref) {
  return ScheduleRepository(ref.watch(dioProvider));
});
