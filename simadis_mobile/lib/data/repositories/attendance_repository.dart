import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/api_constants.dart';
import '../models/attendance_model.dart';

class AttendanceRepository {
  final Dio _dio;

  AttendanceRepository(this._dio);

  Future<Map<String, dynamic>> getAttendanceList({
    String? tanggal,
    int? kelasId,
    int? guruId,
    int page = 1,
  }) async {
    try {
      final params = <String, dynamic>{};
      if (tanggal != null && tanggal.isNotEmpty) params['tanggal'] = tanggal;
      if (kelasId != null) params['kelas_id'] = kelasId;
      if (guruId != null) params['guru_id'] = guruId;
      params['page'] = page;

      final response = await _dio.get(
        ApiConstants.attendance,
        queryParameters: params,
      );

      final data = response.data['data'] as List;
      final pagination = response.data['pagination'];

      return {
        'data': data.map((e) => AttendanceModel.fromJson(e)).toList(),
        'pagination': pagination,
      };
    } on DioException catch (e) {
      throw Exception(e.response?.data?['message'] ?? 'Gagal memuat absensi');
    }
  }

  Future<AttendanceModel> getAttendanceDetail(int id) async {
    try {
      final response = await _dio.get('${ApiConstants.attendanceDetail}/$id');
      return AttendanceModel.fromJson(response.data);
    } on DioException catch (e) {
      throw Exception(e.response?.data?['message'] ?? 'Gagal memuat detail absensi');
    }
  }

  Future<void> bulkUpdateStudents(int attendanceId, List<dynamic> students) async {
    try {
      await _dio.post(
        '${ApiConstants.bulkUpdateAttendance}/$attendanceId/students',
        data: {'students': students},
      );
    } on DioException catch (e) {
      throw Exception(e.response?.data?['message'] ?? 'Gagal memperbarui absensi');
    }
  }

  Future<AttendanceModel> createAttendance(Map<String, dynamic> data) async {
    try {
      final response = await _dio.post(
        ApiConstants.createAttendance,
        data: data,
      );
      return AttendanceModel.fromJson(response.data['data']);
    } on DioException catch (e) {
      throw Exception(e.response?.data?['message'] ?? 'Gagal membuat absensi');
    }
  }

  Future<Map<String, dynamic>> getAttendanceRekap(String tanggal) async {
    try {
      final response = await _dio.get(
        ApiConstants.attendanceRekap,
        queryParameters: {'tanggal': tanggal},
      );
      return response.data;
    } on DioException catch (e) {
      throw Exception(e.response?.data?['message'] ?? 'Gagal memuat rekap');
    }
  }
}

final attendanceRepositoryProvider = Provider<AttendanceRepository>((ref) {
  return AttendanceRepository(ref.watch(dioProvider));
});
