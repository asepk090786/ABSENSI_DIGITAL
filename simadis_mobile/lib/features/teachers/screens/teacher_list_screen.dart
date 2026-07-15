import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/widgets/empty_state_widget.dart';
import '../../../data/models/teacher_model.dart';
import '../../../data/repositories/teacher_repository.dart';

final teachersProvider = FutureProvider.autoDispose.family<List<TeacherModel>, String?>((ref, search) async {
  final repo = ref.watch(teacherRepositoryProvider);
  return repo.getTeachers(search: search);
});

class TeacherListScreen extends ConsumerStatefulWidget {
  const TeacherListScreen({super.key});

  @override
  ConsumerState<TeacherListScreen> createState() => _TeacherListScreenState();
}

class _TeacherListScreenState extends ConsumerState<TeacherListScreen> {
  final _searchController = TextEditingController();

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final teachersAsync = ref.watch(teachersProvider(_searchController.text));

    return Scaffold(
      appBar: AppBar(title: const Text('Data Guru')),
      body: RefreshIndicator(
        onRefresh: () async => ref.invalidate(teachersProvider),
        child: teachersAsync.when(
          data: (teachers) {
            if (teachers.isEmpty) {
              return const EmptyStateWidget(
                icon: Icons.person_outline,
                title: 'Tidak ada data guru',
                message: 'Belum ada data guru yang tersedia.',
              );
            }
            return ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: teachers.length,
              itemBuilder: (context, index) {
                final guru = teachers[index];
                return Card(
                  margin: const EdgeInsets.only(bottom: 8),
                  child: ListTile(
                    leading: CircleAvatar(
                      backgroundColor: Theme.of(context).colorScheme.primaryContainer,
                      child: Text(guru.nama?[0] ?? '?'),
                    ),
                    title: Text(guru.nama ?? '-'),
                    subtitle: Text('NIP: ${guru.nip ?? '-'}'),
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
              onRetry: () => ref.invalidate(teachersProvider),
            ),
          ),
        ),
      ),
    );
  }
}
