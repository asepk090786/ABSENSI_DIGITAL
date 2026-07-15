import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../../core/utils/role_helper.dart';
import '../../../core/widgets/empty_state_widget.dart';
import '../../../core/theme/app_colors.dart';
import '../../../data/models/dashboard_model.dart';
import '../../../data/repositories/dashboard_repository.dart';

final dashboardProvider = FutureProvider.autoDispose.family<DashboardResponse, Map<String, String?>>((ref, filters) async {
  final repo = ref.watch(dashboardRepositoryProvider);
  return repo.getMobileDashboard(
    filterTanggal: filters['tanggal'],
    filterMinggu: filters['minggu'],
    filterBulan: filters['bulan'],
  );
});

class DashboardScreen extends ConsumerWidget {
  const DashboardScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(currentUserProvider);
    final roles = user?.roles ?? [];

    return Scaffold(
      appBar: AppBar(
        title: const Text('Dashboard'),
        actions: [
          IconButton(
            icon: const Icon(Icons.filter_list),
            onPressed: () => _showFilterDialog(context, ref),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async => ref.invalidate(dashboardProvider),
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              if (user != null)
                Text(
                  'Selamat datang, ${user.name ?? 'Pengguna'}',
                  style: Theme.of(context).textTheme.headlineSmall,
                ),
              const SizedBox(height: 4),
              ref.watch(dashboardProvider).when(
                data: (dashboard) {
                  return Text(
                    dashboard.tahunAjaran ?? '-',
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                          color: Theme.of(context).colorScheme.onSurfaceVariant,
                        ),
                  );
                },
                loading: () => const SizedBox(width: 100, child: LinearProgressIndicator(minHeight: 4)),
                error: (_, _) => const Text(''),
              ),
              const SizedBox(height: 16),
              ref.watch(dashboardProvider).when(
                data: (dashboard) {
                  final stats = dashboard.stats;
                  return Column(
                    children: [
                      Row(
                        children: [
                          Expanded(child: StatCard(title: 'Guru', value: stats.guru.toString(), icon: Icons.person, color: Theme.of(context).colorScheme.primary)),
                          const SizedBox(width: 12),
                          Expanded(child: StatCard(title: 'Siswa', value: stats.siswa.toString(), icon: Icons.group, color: Theme.of(context).colorScheme.secondary)),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Row(
                        children: [
                          Expanded(child: StatCard(title: 'Kelas', value: stats.kelas.toString(), icon: Icons.class_, color: Colors.orange)),
                          const SizedBox(width: 12),
                          Expanded(child: StatCard(title: 'Absensi', value: stats.absensi.toString(), icon: Icons.assignment, color: Colors.purple)),
                        ],
                      ),
                      const SizedBox(height: 24),
                      if (RoleHelper.isSiswa(roles)) ...[
                        _buildAttendanceCard(context, dashboard.attendanceSummary),
                      ] else ...[
                        if (dashboard.classBreakdown != null && dashboard.classBreakdown!.isNotEmpty)
                          _buildClassBreakdown(context, dashboard.classBreakdown!),
                        const SizedBox(height: 16),
                        if (dashboard.rekapGuruHarian != null && dashboard.rekapGuruHarian!.isNotEmpty)
                          _buildGuruRecap(context, dashboard.rekapGuruHarian!),
                      ],
                    ],
                  );
                },
                loading: () => const LoadingIndicator(),
                error: (e, _) => Center(
                  child: EmptyStateWidget(
                    icon: Icons.error_outline,
                    title: 'Terjadi kesalahan',
                    message: e.toString(),
                    onRetry: () => ref.invalidate(dashboardProvider),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
      bottomNavigationBar: BottomNavigationBar(
        type: BottomNavigationBarType.fixed,
        currentIndex: 0,
        onTap: (index) {
          switch (index) {
            case 0:
              context.go('/dashboard');
              break;
            case 1:
              context.go('/classes');
              break;
            case 2:
              context.go('/students');
              break;
            case 3:
              context.go('/attendance');
              break;
            case 4:
              context.go('/profile');
              break;
          }
        },
        items: const [
          BottomNavigationBarItem(icon: Icon(Icons.dashboard), label: 'Dashboard'),
          BottomNavigationBarItem(icon: Icon(Icons.class_), label: 'Kelas'),
          BottomNavigationBarItem(icon: Icon(Icons.people), label: 'Siswa'),
          BottomNavigationBarItem(icon: Icon(Icons.assignment), label: 'Absensi'),
          BottomNavigationBarItem(icon: Icon(Icons.person), label: 'Profil'),
        ],
      ),
    );
  }

  Widget _buildAttendanceCard(BuildContext context, AttendanceSummary? summary) {
    if (summary == null) return const SizedBox.shrink();

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Ringkasan Kehadiran', style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(child: _buildStat(context, 'Hadir', summary.hadir.toString(), AppColors.success)),
                Expanded(child: _buildStat(context, 'Terlambat', summary.terlambat.toString(), AppColors.warning)),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                Expanded(child: _buildStat(context, 'Izin', summary.izin.toString(), AppColors.info)),
                Expanded(child: _buildStat(context, 'Sakit', summary.sakit.toString(), AppColors.info)),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                Expanded(child: _buildStat(context, 'Alpa', summary.alpha.toString(), AppColors.danger)),
                Expanded(child: _buildStat(context, 'Total', summary.total.toString(), Theme.of(context).colorScheme.primary)),
              ],
            ),
            const SizedBox(height: 16),
            LinearProgressIndicator(
              value: summary.presentPercent / 100,
              minHeight: 8,
              borderRadius: BorderRadius.circular(4),
            ),
            const SizedBox(height: 8),
            Text('Kehadiran: ${NumberFormat('##0.00').format(summary.presentPercent)}%'),
          ],
        ),
      ),
    );
  }

  Widget _buildStat(BuildContext context, String label, String value, Color color) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 4),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        children: [
          Text(value, style: Theme.of(context).textTheme.titleLarge?.copyWith(color: color, fontWeight: FontWeight.bold)),
          Text(label, style: Theme.of(context).textTheme.bodySmall),
        ],
      ),
    );
  }

  Widget _buildClassBreakdown(BuildContext context, List<ClassBreakdownItem> items) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Rekap Kehadiran Siswa per Kelas', style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 12),
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: DataTable(
                columns: const [
                  DataColumn(label: Text('Kelas')),
                  DataColumn(label: Text('H'), numeric: true),
                  DataColumn(label: Text('TL'), numeric: true),
                  DataColumn(label: Text('I'), numeric: true),
                  DataColumn(label: Text('S'), numeric: true),
                  DataColumn(label: Text('A'), numeric: true),
                  DataColumn(label: Text('Total'), numeric: true),
                ],
                rows: items.map((item) {
                  return DataRow(
                    cells: [
                      DataCell(Text(item.namaKelas ?? '-')),
                      DataCell(Text(item.hadir.toString())),
                      DataCell(Text(item.terlambat.toString())),
                      DataCell(Text(item.izin.toString())),
                      DataCell(Text(item.sakit.toString())),
                      DataCell(Text(item.alpha.toString())),
                      DataCell(Text(item.totalEntri.toString())),
                    ],
                  );
                }).toList(),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildGuruRecap(BuildContext context, List<RekapGuruHarian> items) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Rekap Kehadiran Guru per Hari', style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 12),
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: DataTable(
                columns: const [
                  DataColumn(label: Text('Tanggal')),
                  DataColumn(label: Text('H'), numeric: true),
                  DataColumn(label: Text('I'), numeric: true),
                  DataColumn(label: Text('S'), numeric: true),
                  DataColumn(label: Text('TH'), numeric: true),
                  DataColumn(label: Text('Total'), numeric: true),
                ],
                rows: items.map((item) {
                  final date = item.tanggal.isNotEmpty ? DateFormat('dd MMM yyyy').format(DateTime.parse(item.tanggal)) : '-';
                  return DataRow(
                    cells: [
                      DataCell(Text(date)),
                      DataCell(Text(item.hadir.toString())),
                      DataCell(Text(item.izin.toString())),
                      DataCell(Text(item.sakit.toString())),
                      DataCell(Text(item.tidakHadir.toString())),
                      DataCell(Text(item.totalEntri.toString())),
                    ],
                  );
                }).toList(),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showFilterDialog(BuildContext context, WidgetRef ref) {
    final filterTanggal = TextEditingController();
    final filterMinggu = TextEditingController();
    final filterBulan = TextEditingController();

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Filter Periode'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(controller: filterTanggal, decoration: const InputDecoration(labelText: 'Tanggal')),
            TextField(controller: filterMinggu, decoration: const InputDecoration(labelText: 'Minggu (YYYY-Www)')),
            TextField(controller: filterBulan, decoration: const InputDecoration(labelText: 'Bulan (YYYY-MM)')),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Batal')),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              ref.invalidate(dashboardProvider);
            },
            child: const Text('Filter'),
          ),
        ],
      ),
    );
  }
}
