import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/api_constants.dart';
import '../models/student_model.dart';

class StudentRepository {
  final Dio _dio;

  StudentRepository(this._dio);

  Future<List<StudentModel>> getStudents({
    String? search,
    int? kelasId,
  }) async {
    try {
      final params = <String, dynamic>{};
      if (search != null && search.isNotEmpty) params['search'] = search;
      if (kelasId != null) params['kelas_id'] = kelasId;

      final response = await _dio.get(
        ApiConstants.students,
        queryParameters: params,
      );

      final list = response.data['students'] as List;
      return list.map((e) => StudentModel.fromJson(e)).toList();
    } on DioException catch (e) {
      throw Exception(e.response?.data?['message'] ?? 'Gagal memuat data siswa');
    }
  }

  Future<StudentModel> getStudentDetail(int id) async {
    try {
      final response = await _dio.get('${ApiConstants.studentDetail}/$id');
      return StudentModel.fromJson(response.data);
    } on DioException catch (e) {
      throw Exception(e.response?.data?['message'] ?? 'Gagal memuat detail siswa');
    }
  }
}

final studentRepositoryProvider = Provider<StudentRepository>((ref) {
  return StudentRepository(ref.watch(dioProvider));
});
