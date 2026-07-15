import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/api_constants.dart';
import '../models/teacher_model.dart';

class TeacherRepository {
  final Dio _dio;

  TeacherRepository(this._dio);

  Future<List<TeacherModel>> getTeachers({String? search}) async {
    try {
      final params = <String, dynamic>{};
      if (search != null && search.isNotEmpty) params['search'] = search;

      final response = await _dio.get(
        ApiConstants.teachers,
        queryParameters: params,
      );

      final list = response.data['teachers'] as List;
      return list.map((e) => TeacherModel.fromJson(e)).toList();
    } on DioException catch (e) {
      throw Exception(e.response?.data?['message'] ?? 'Gagal memuat data guru');
    }
  }

  Future<TeacherModel> getTeacherDetail(int id) async {
    try {
      final response = await _dio.get('${ApiConstants.teacherDetail}/$id');
      return TeacherModel.fromJson(response.data);
    } on DioException catch (e) {
      throw Exception(e.response?.data?['message'] ?? 'Gagal memuat detail guru');
    }
  }
}

final teacherRepositoryProvider = Provider<TeacherRepository>((ref) {
  return TeacherRepository(ref.watch(dioProvider));
});
