import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/widgets/loading_indicator.dart';
import '../../../data/models/attendance_model.dart';
import '../../../data/repositories/attendance_repository.dart';

final attendanceDetailProvider = FutureProvider.autoDispose.family<AttendanceModel, int>((ref, id) async {
  final repo = ref.watch(attendanceRepositoryProvider);
  return repo.getAttendanceDetail(id);
});

class AttendanceInputScreen extends ConsumerWidget {
  final int attendanceId;
  const AttendanceInputScreen({super.key, required this.attendanceId});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final detailAsync = ref.watch(attendanceDetailProvider(attendanceId));

    return Scaffold(
      appBar: AppBar(
        title: const Text('Input Absensi'),
        actions: [
          IconButton(
            icon: const Icon(Icons.save),
            onPressed: detailAsync.value != null ? () => _save(context, ref, detailAsync.value!) : null,
          ),
        ],
      ),
      body: detailAsync.when(
        data: (attendance) {
          final students = attendance.students ?? [];
          return ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: students.length,
            itemBuilder: (context, index) {
              final student = students[index];
              return Card(
                margin: const EdgeInsets.only(bottom: 8),
                child: ListTile(
                  leading: const CircleAvatar(child: Icon(Icons.person)),
                  title: Text(student.nama ?? '-'),
                  subtitle: Text('NIS: ${student.nis ?? '-'}'),
                  trailing: DropdownButton<String>(
                    value: student.status,
                    items: const [
                      DropdownMenuItem(value: 'hadir', child: Text('Hadir')),
                      DropdownMenuItem(value: 'terlambat', child: Text('Terlambat')),
                      DropdownMenuItem(value: 'izin', child: Text('Izin')),
                      DropdownMenuItem(value: 'sakit', child: Text('Sakit')),
                      DropdownMenuItem(value: 'alpha', child: Text('Alpha')),
                    ],
                    onChanged: (v) {
                      // Update local state would need a stateful provider, omitted for brevity
                    },
                  ),
                ),
              );
            },
          );
        },
        loading: () => const LoadingIndicator(),
        error: (e, _) => Center(
          child: Text('Error: ${e.toString()}'),
        ),
      ),
    );
  }

  Future<void> _save(BuildContext context, WidgetRef ref, AttendanceModel attendance) async {
    final students = (attendance.students ?? []).map((s) => {
      'absensi_siswa_id': s.id,
      'status': s.status,
      'keterangan': s.keterangan ?? '',
    }).toList();

    try {
      await ref.read(attendanceRepositoryProvider).bulkUpdateStudents(attendanceId, students);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Absensi berhasil disimpan')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal: ${e.toString()}')),
        );
      }
    }
  }
}
