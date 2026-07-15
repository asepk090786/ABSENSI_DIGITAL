import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/widgets/empty_state_widget.dart';
import '../../../core/theme/app_colors.dart';
import '../../../data/repositories/class_repository.dart';

final classesProvider = FutureProvider.autoDispose.family<List<ClassModel>, Map<String, String?>>((ref, filters) async {
  final repo = ref.watch(classRepositoryProvider);
  return repo.getClasses(
    search: filters['search'],
    tingkatKelas: filters['tingkatKelas'],
    jurusan: filters['jurusan'],
  );
});

class ClassListScreen extends ConsumerStatefulWidget {
  const ClassListScreen({super.key});

  @override
  ConsumerState<ClassListScreen> createState() => _ClassListScreenState();
}

class _ClassListScreenState extends ConsumerState<ClassListScreen> {
  final _searchController = TextEditingController();
  final _tingkatController = TextEditingController();
  final _jurusanController = TextEditingController();

  @override
  void dispose() {
    _searchController.dispose();
    _tingkatController.dispose();
    _jurusanController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final classesAsync = ref.watch(classesProvider({}));

    return Scaffold(
      appBar: AppBar(
        title: const Text('Data Kelas'),
        actions: [
          IconButton(
            icon: const Icon(Icons.filter_list),
            onPressed: () => _showFilter(context),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async => ref.invalidate(classesProvider),
        child: classesAsync.when(
          data: (classes) {
            if (classes.isEmpty) {
              return const EmptyStateWidget(
                icon: Icons.class_,
                title: 'Tidak ada data kelas',
                message: 'Belum ada data kelas yang tersedia.',
              );
            }
            return ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: classes.length,
              itemBuilder: (context, index) {
                final item = classes[index];
                return Card(
                  margin: const EdgeInsets.only(bottom: 12),
                  child: ListTile(
                    leading: CircleAvatar(
                      backgroundColor: AppColors.primaryContainer,
                      child: Text(item.namaKelas ?? '?'),
                    ),
                    title: Text(item.namaKelas ?? '-'),
                    subtitle: Text('${item.kodeKelas ?? ''} ${item.jurusan ?? ''}'),
                    trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                    onTap: () => context.push('/classes/${item.id}'),
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
              onRetry: () => ref.invalidate(classesProvider),
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
        title: const Text('Filter Kelas'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: _searchController,
              decoration: const InputDecoration(labelText: 'Cari nama/kode/jurusan'),
            ),
            TextField(
              controller: _tingkatController,
              decoration: const InputDecoration(labelText: 'Tingkat Kelas'),
            ),
            TextField(
              controller: _jurusanController,
              decoration: const InputDecoration(labelText: 'Jurusan'),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Batal')),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              ref.invalidate(classesProvider);
            },
            child: const Text('Terapkan'),
          ),
        ],
      ),
    );
  }
}
