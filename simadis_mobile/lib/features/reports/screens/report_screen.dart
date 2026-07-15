import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/theme/app_colors.dart';
import '../screens/attendance_list_screen.dart';

class ReportScreen extends ConsumerStatefulWidget {
  const ReportScreen({super.key});

  @override
  ConsumerState<ReportScreen> createState() => _ReportScreenState();
}

class _ReportScreenState extends ConsumerState<ReportScreen> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Laporan')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _buildReportCard(
            context: context,
            icon: Icons.assignment,
            title: 'Rekap Absensi Siswa',
            subtitle: 'Lihat rekap absensi per kelas',
            color: AppColors.success,
            onTap: () => context.push('/dashboard'),
          ),
          const SizedBox(height: 12),
          _buildReportCard(
            context: context,
            icon: Icons.people,
            title: 'Rekap Absensi Guru',
            subtitle: 'Lihat rekap kehadiran guru harian',
            color: AppColors.warning,
            onTap: () => context.push('/attendance/rekap'),
          ),
          const SizedBox(height: 12),
          _buildReportCard(
            context: context,
            icon: Icons.calculate,
            title: 'Rekap Nilai',
            subtitle: 'Lihat rekap nilai per kelas dan mapel',
            color: AppColors.info,
            onTap: () => ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Fitur dalam pengembangan'))),
          ),
        ],
      ),
    );
  }

  Widget _buildReportCard({
    required BuildContext context,
    required IconData icon,
    required String title,
    required String subtitle,
    required Color color,
    required VoidCallback onTap,
  }) {
    return Card(
      child: ListTile(
        leading: Container(
          width: 48,
          height: 48,
          decoration: BoxDecoration(
            color: color.withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(12),
          ),
          child: Icon(icon, color: color),
        ),
        title: Text(title),
        subtitle: Text(subtitle),
        trailing: const Icon(Icons.arrow_forward_ios, size: 16),
        onTap: onTap,
      ),
    );
  }
}
