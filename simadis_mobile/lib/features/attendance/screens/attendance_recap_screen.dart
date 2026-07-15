import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/widgets/empty_state_widget.dart';
import '../../../data/repositories/attendance_repository.dart';

final attendanceRekapProvider = FutureProvider.autoDispose.family<Map<String, dynamic>, String>((ref, tanggal) async {
  final repo = ref.watch(attendanceRepositoryProvider);
  return repo.getAttendanceRekap(tanggal);
});

class AttendanceRecapScreen extends ConsumerStatefulWidget {
  const AttendanceRecapScreen({super.key});

  @override
  ConsumerState<AttendanceRecapScreen> createState() => _AttendanceRecapScreenState();
}

class _AttendanceRecapScreenState extends ConsumerState<AttendanceRecapScreen> {
  late TextEditingController _tanggalController;

  @override
  void initState() {
    super.initState();
    _tanggalController = TextEditingController(text: DateFormat('yyyy-MM-dd').format(DateTime.now()));
  }

  @override
  void dispose() {
    _tanggalController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final rekapAsync = ref.watch(attendanceRekapProvider(_tanggalController.text));

    return Scaffold(
      appBar: AppBar(
        title: const Text('Rekap Absensi Guru'),
        actions: [
          IconButton(
            icon: const Icon(Icons.search),
            onPressed: () => ref.invalidate(attendanceRekapProvider),
          ),
        ],
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: TextField(
              controller: _tanggalController,
              decoration: const InputDecoration(
                labelText: 'Tanggal',
                hintText: 'YYYY-MM-DD',
                prefixIcon: Icon(Icons.calendar_today),
              ),
              onSubmitted: (v) => ref.invalidate(attendanceRekapProvider),
            ),
          ),
          Expanded(
            child: RefreshIndicator(
              onRefresh: () async => ref.invalidate(attendanceRekapProvider),
              child: rekapAsync.when(
                data: (rekap) {
                  return SingleChildScrollView(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      children: [
                        Row(
                          children: [
                            Expanded(child: _buildStatCard(context, 'Total', (rekap['total_entri'] ?? 0).toString(), AppColors.primary)),
                            const SizedBox(width: 12),
                            Expanded(child: _buildStatCard(context, 'Hadir', (rekap['hadir'] ?? 0).toString(), AppColors.success)),
                          ],
                        ),
                        const SizedBox(height: 12),
                        Row(
                          children: [
                            Expanded(child: _buildStatCard(context, 'Izin', (rekap['izin'] ?? 0).toString(), AppColors.info)),
                            const SizedBox(width: 12),
                            Expanded(child: _buildStatCard(context, 'Sakit', (rekap['sakit'] ?? 0).toString(), AppColors.warning)),
                          ],
                        ),
                        const SizedBox(height: 12),
                        _buildStatCard(context, 'Tidak Hadir', (rekap['tidak_hadir'] ?? 0).toString(), AppColors.danger, fullWidth: true),
                      ],
                    ),
                  );
                },
                loading: () => const LoadingIndicator(),
                error: (e, _) => Center(
                  child: EmptyStateWidget(
                    icon: Icons.error_outline,
                    title: 'Terjadi kesalahan',
                    message: e.toString(),
                    onRetry: () => ref.invalidate(attendanceRekapProvider),
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatCard(BuildContext context, String label, String value, Color color, {bool fullWidth = false}) {
    return Container(
      width: fullWidth ? double.infinity : null,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Column(
        crossAxisAlignment: fullWidth ? CrossAxisAlignment.center : CrossAxisAlignment.start,
        children: [
          Text(value, style: Theme.of(context).textTheme.headlineMedium?.copyWith(color: color, fontWeight: FontWeight.bold)),
          const SizedBox(height: 4),
          Text(label, style: Theme.of(context).textTheme.bodySmall),
        ],
      ),
    );
  }
}
