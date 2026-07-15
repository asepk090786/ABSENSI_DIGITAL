import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../../core/theme/app_colors.dart';
import '../../../data/models/class_model.dart';
import '../../../data/repositories/attendance_repository.dart';
import 'package:intl/intl.dart';

final attendanceListProvider = FutureProvider.autoDispose.family<Map<String, dynamic>, Map<String, String?>>((ref, filters) async {
  final repo = ref.watch(attendanceRepositoryProvider);
  return repo.getAttendanceList(
    tanggal: filters['tanggal'],
    kelasId: filters['kelas_id'] != null ? int.tryParse(filters['kelas_id']!) : null,
    guruId: filters['guru_id'] != null ? int.tryParse(filters['guru_id']!) : null,
    page: int.tryParse(filters['page'] ?? '1') ?? 1,
  );
});

class AttendanceListScreen extends ConsumerStatefulWidget {
  const AttendanceListScreen({super.key});

  @override
  ConsumerState<AttendanceListScreen> createState() => _AttendanceListScreenState();
}

class _AttendanceListScreenState extends ConsumerState<AttendanceListScreen> {
  final _tanggalController = TextEditingController();
  String? _selectedKelasId;

  @override
  void dispose() {
    _tanggalController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final attendanceAsync = ref.watch(attendanceListProvider({
      'tanggal': _tanggalController.text,
      'kelas_id': _selectedKelasId,
    }));

    return Scaffold(
      appBar: AppBar(
        title: const Text('Input Absensi'),
        actions: [
          IconButton(
            icon: const Icon(Icons.filter_list),
            onPressed: () => _showFilter(context),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async => ref.invalidate(attendanceListProvider),
        child: attendanceAsync.when(
          data: (result) {
            final data = result['data'] as List;
            final pagination = result['pagination'] as Map<String, dynamic>;

            if (data.isEmpty) {
              return const EmptyStateWidget(
                icon: Icons.assignment_outlined,
                title: 'Tidak ada data absensi',
                message: 'Belum ada data absensi untuk ditampilkan.',
              );
            }

            return Column(
              children: [
                Expanded(
                  child: ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: data.length,
                    itemBuilder: (context, index) {
                      final item = data[index] as Map<String, dynamic>;
                      final kelas = item['kelas'] ?? {};
                      final guru = item['guru'] ?? {};
                      final tanggal = item['tanggal'] ?? '-';
                      final formattedDate = tanggal.isNotEmpty && tanggal != '-' ? DateFormat('dd MMM yyyy').format(DateTime.parse(tanggal)) : tanggal;

                      return Card(
                        margin: const EdgeInsets.only(bottom: 12),
                        child: ListTile(
                          leading: Container(
                            width: 48,
                            height: 48,
                            decoration: BoxDecoration(
                              color: AppColors.primaryContainer,
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: const Icon(Icons.assignment, color: AppColors.primary),
                          ),
                          title: Text(kelas['nama_kelas'] ?? '-'),
                          subtitle: Text('$formattedDate | ${guru['nama'] ?? '-'}'),
                          trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                          onTap: () => context.push('/attendance/${item['id']}'),
                        ),
                      );
                    },
                  ),
                ),
                if ((pagination['last_page'] ?? 1) > 1)
                  Padding(
                    padding: const EdgeInsets.all(16),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text('Halaman ${pagination['current_page']} dari ${pagination['last_page']}'),
                        Row(
                          children: [
                            if ((pagination['current_page'] ?? 1) > 1)
                              IconButton(
                                onPressed: () {
                                  // pagination logic
                                },
                                icon: const Icon(Icons.chevron_left),
                              ),
                            if ((pagination['current_page'] ?? 1) < (pagination['last_page'] ?? 1))
                              IconButton(
                                onPressed: () {
                                  // pagination logic
                                },
                                icon: const Icon(Icons.chevron_right),
                              ),
                          ],
                        ),
                      ],
                    ),
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
              onRetry: () => ref.invalidate(attendanceListProvider),
            ),
          ),
        ),
      ),
    );
  }

  void _showFilter(BuildContext context) {
    final kelasController = TextEditingController();

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Filter Absensi'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: _tanggalController..text = _tanggalController.text,
              decoration: const InputDecoration(labelText: 'Tanggal (YYYY-MM-DD)'),
            ),
            TextField(
              controller: kelasController,
              decoration: const InputDecoration(labelText: 'ID Kelas'),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Batal')),
          TextButton(
            onPressed: () {
              setState(() => _selectedKelasId = kelasController.text);
              Navigator.pop(context);
              ref.invalidate(attendanceListProvider);
            },
            child: const Text('Terapkan'),
          ),
        ],
      ),
    );
  }
}
