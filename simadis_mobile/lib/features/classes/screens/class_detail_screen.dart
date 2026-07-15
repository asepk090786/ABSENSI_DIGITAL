import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../../core/theme/app_colors.dart';
import '../../../data/models/class_model.dart';
import '../../../data/models/student_model.dart';
import '../../../data/repositories/class_repository.dart';

final classDetailProvider = FutureProvider.autoDispose.family<Map<String, dynamic>, int>((ref, classId) async {
  final repo = ref.watch(classRepositoryProvider);
  final detail = await repo.getClassDetail(classId);
  final students = await repo.getClassStudents(classId);
  return {
    'class': detail,
    'students': students,
  };
});

class ClassDetailScreen extends ConsumerWidget {
  final int classId;
  const ClassDetailScreen({super.key, required this.classId});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final detailAsync = ref.watch(classDetailProvider(classId));

    return Scaffold(
      appBar: AppBar(title: const Text('Detail Kelas')),
      body: detailAsync.when(
        data: (data) {
          final kelas = data['class'] as ClassModel;
          final students = data['students'] as List<StudentModel>;

          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(kelas.namaKelas ?? '-', style: Theme.of(context).textTheme.headlineSmall),
                      const SizedBox(height: 8),
                      Text('Kode: ${kelas.kodeKelas ?? '-'}'),
                      Text('Tingkat: ${kelas.tingkatKelas ?? '-'}'),
                      Text('Jurusan: ${kelas.jurusan ?? '-'}'),
                      Text('Total Siswa: ${students.length}'),
                      if (kelas.waliKelas != null)
                        Text('Wali Kelas: ${kelas.waliKelas?['nama'] ?? '-'}'),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),
              Text('Daftar Siswa', style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 12),
              if (students.isEmpty)
                const EmptyStateWidget(
                  icon: Icons.people_outline,
                  title: 'Tidak ada siswa',
                  message: 'Kelas ini belum memiliki siswa.',
                )
              else
                ListView.builder(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: students.length,
                  itemBuilder: (context, index) {
                    final siswa = students[index];
                    return Card(
                      margin: const EdgeInsets.only(bottom: 8),
                      child: ListTile(
                        leading: const CircleAvatar(child: Icon(Icons.person)),
                        title: Text(siswa.nama ?? '-'),
                        subtitle: Text('NIS: ${siswa.nis ?? '-'}'),
                      ),
                    );
                  },
                ),
            ],
          );
        },
        loading: () => const LoadingIndicator(),
        error: (e, _) => Center(
          child: EmptyStateWidget(
            icon: Icons.error_outline,
            title: 'Terjadi kesalahan',
            message: e.toString(),
            onRetry: () => ref.invalidate(classDetailProvider(classId)),
          ),
        ),
      ),
    );
  }
}
