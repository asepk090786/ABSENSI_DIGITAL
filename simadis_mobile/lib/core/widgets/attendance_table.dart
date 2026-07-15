import 'package:flutter/material.dart';

class AttendanceDataTable extends StatelessWidget {
  final List<Map<String, dynamic>> rows;
  final List<String> columns;

  const AttendanceDataTable({
    super.key,
    required this.rows,
    required this.columns,
  });

  @override
  Widget build(BuildContext context) {
    if (rows.isEmpty) {
      return const EmptyStateWidget(
        icon: Icons.table_chart_outlined,
        title: 'Tidak ada data',
        message: 'Belum ada data kehadiran untuk ditampilkan.',
      );
    }

    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: DataTable(
        columns: columns
            .map((col) => DataColumn(
                  label: Text(
                    col,
                    style: const TextStyle(fontWeight: FontWeight.bold),
                  ),
                ))
            .toList(),
        rows: rows
            .map((row) => DataRow(
                  cells: columns
                      .map((col) => DataCell(
                            Text(row[col]?.toString() ?? '-'),
                          ))
                      .toList(),
                ))
            .toList(),
      ),
    );
  }
}
