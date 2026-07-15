import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/api_constants.dart';
import '../models/class_model.dart';

class ClassRepository {
  final Dio _dio;

  ClassRepository(this._dio);

  Future<List<ClassModel>> getClasses({
    String? search,
    String? tingkatKelas,
    String? jurusan,
  }) async {
    try {
      final params = <String, dynamic>{};
      if (search != null && search.isNotEmpty) params['search'] = search;
      if (tingkatKelas != null && tingkatKelas.isNotEmpty) {
        params['tingkat_kelas'] = tingkatKelas;
      }
      if (jurusan != null && jurusan.isNotEmpty) params['jurusan'] = jurusan;

      final response = await _dio.get(
        ApiConstants.classes,
        queryParameters: params,
      );

      final list = response.data['classes'] as List;
      return list.map((e) => ClassModel.fromJson(e)).toList();
    } on DioException catch (e) {
      throw Exception(e.response?.data?['message'] ?? 'Gagal memuat data kelas');
    }
  }

  Future<ClassModel> getClassDetail(int id) async {
    try {
      final response = await _dio.get('${ApiConstants.classDetail}/$id');
      return ClassModel.fromJson(response.data);
    } on DioException catch (e) {
      throw Exception(e.response?.data?['message'] ?? 'Gagal memuat detail kelas');
    }
  }

  Future<List<StudentModel>> getClassStudents(int classId) async {
    try {
      final response = await _dio.get('${ApiConstants.classStudents}/$classId/students');
      final list = response.data['students'] as List;
      return list.map((e) => StudentModel.fromJson(e)).toList();
    } on DioException catch (e) {
      throw Exception(e.response?.data?['message'] ?? 'Gagal memuat siswa');
    }
  }
}

final classRepositoryProvider = Provider<ClassRepository>((ref) {
  return ClassRepository(ref.watch(dioProvider));
});
