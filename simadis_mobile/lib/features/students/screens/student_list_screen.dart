import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/widgets/empty_state_widget.dart';
import '../../../data/models/student_model.dart';
import '../../../data/repositories/student_repository.dart';

final studentsProvider = FutureProvider.autoDispose.family<List<StudentModel>, Map<String, String?>>((ref, filters) async {
  final repo = ref.watch(studentRepositoryProvider);
  final kelasId = filters['kelas_id'];
  return repo.getStudents(
    search: filters['search'],
    kelasId: kelasId != null ? int.tryParse(kelasId) : null,
  );
});

class StudentListScreen extends ConsumerStatefulWidget {
  const StudentListScreen({super.key});

  @override
  ConsumerState<StudentListScreen> createState() => _StudentListScreenState();
}

class _StudentListScreenState extends ConsumerState<StudentListScreen> {
  final _searchController = TextEditingController();
  String? _selectedKelas;

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final studentsAsync = ref.watch(studentsProvider({
      'search': _searchController.text,
      'kelas_id': _selectedKelas,
    }));

    return Scaffold(
      appBar: AppBar(
        title: const Text('Data Siswa'),
        actions: [
          IconButton(
            icon: const Icon(Icons.filter_list),
            onPressed: () => _showFilter(context),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async => ref.invalidate(studentsProvider),
        child: studentsAsync.when(
          data: (students) {
            if (students.isEmpty) {
              return const EmptyStateWidget(
                icon: Icons.people_outline,
                title: 'Tidak ada data siswa',
                message: 'Belum ada data siswa yang tersedia.',
              );
            }
            return ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: students.length,
              itemBuilder: (context, index) {
                final siswa = students[index];
                return Card(
                  margin: const EdgeInsets.only(bottom: 8),
                  child: ListTile(
                    leading: const CircleAvatar(child: Icon(Icons.person)),
                    title: Text(siswa.nama ?? '-'),
                    subtitle: Text('NIS: ${siswa.nis ?? '-'} | ${siswa.kelas?['nama_kelas'] ?? '-'}'),
                  ),
                );
              },
            );
          },
          loading: () => const LoadingIndicator(),
          error: (e, _) => Center(
            child: EmptyStateWidget(
              icon: Icons.error_outline,
              title: 'Terjadi kesalahan',
              message: e.toString(),
              onRetry: () => ref.invalidate(studentsProvider),
            ),
          ),
        ),
      ),
    );
  }

  void _showFilter(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Filter Siswa'),
        content: TextField(
          controller: _searchController,
          decoration: const InputDecoration(labelText: 'Cari nama/NIS/NISN'),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Batal')),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              ref.invalidate(studentsProvider);
            },
            child: const Text('Terapkan'),
          ),
        ],
      ),
    );
  }
}
