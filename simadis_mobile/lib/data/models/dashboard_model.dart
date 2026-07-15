class DashboardStats {
  final int guru;
  final int siswa;
  final int kelas;
  final int absensi;

  DashboardStats({
    required this.guru,
    required this.siswa,
    required this.kelas,
    required this.absensi,
  });

  factory DashboardStats.fromJson(Map<String, dynamic> json) => DashboardStats(
        guru: json['guru'] ?? 0,
        siswa: json['siswa'] ?? 0,
        kelas: json['kelas'] ?? 0,
        absensi: json['absensi'] ?? 0,
      );

  Map<String, dynamic> toJson() => {
        'guru': guru,
        'siswa': siswa,
        'kelas': kelas,
        'absensi': absensi,
      };
}

class AttendanceSummary {
  final int hadir;
  final int terlambat;
  final int izin;
  final int sakit;
  final int alpha;
  final int total;
  final double presentPercent;

  AttendanceSummary({
    required this.hadir,
    required this.terlambat,
    required this.izin,
    required this.sakit,
    required this.alpha,
    required this.total,
    required this.presentPercent,
  });

  factory AttendanceSummary.fromJson(Map<String, dynamic> json) =>
      AttendanceSummary(
        hadir: json['hadir'] ?? 0,
        terlambat: json['terlambat'] ?? 0,
        izin: json['izin'] ?? 0,
        sakit: json['sakit'] ?? 0,
        alpha: json['alpha'] ?? 0,
        total: json['total'] ?? 0,
        presentPercent: (json['present_percent'] ?? 0).toDouble(),
      );

  Map<String, dynamic> toJson() => {
        'hadir': hadir,
        'terlambat': terlambat,
        'izin': izin,
        'sakit': sakit,
        'alpha': alpha,
        'total': total,
        'present_percent': presentPercent,
      };
}

class ClassBreakdownItem {
  final int kelasId;
  final String namaKelas;
  final int totalEntri;
  final int hadir;
  final int terlambat;
  final int izin;
  final int sakit;
  final int alpha;
  final String? tanggalTerakhir;

  ClassBreakdownItem({
    required this.kelasId,
    required this.namaKelas,
    required this.totalEntri,
    required this.hadir,
    required this.terlambat,
    required this.izin,
    required this.sakit,
    required this.alpha,
    this.tanggalTerakhir,
  });

  factory ClassBreakdownItem.fromJson(Map<String, dynamic> json) =>
      ClassBreakdownItem(
        kelasId: json['kelas_id'] ?? 0,
        namaKelas: json['nama_kelas'] ?? '',
        totalEntri: json['total_entri'] ?? 0,
        hadir: json['hadir'] ?? 0,
        terlambat: json['terlambat'] ?? 0,
        izin: json['izin'] ?? 0,
        sakit: json['sakit'] ?? 0,
        alpha: json['alpha'] ?? 0,
        tanggalTerakhir: json['tanggal_terakhir'],
      );

  Map<String, dynamic> toJson() => {
        'kelas_id': kelasId,
        'nama_kelas': namaKelas,
        'total_entri': totalEntri,
        'hadir': hadir,
        'terlambat': terlambat,
        'izin': izin,
        'sakit': sakit,
        'alpha': alpha,
        'tanggal_terakhir': tanggalTerakhir,
      };
}

class RekapGuruHarian {
  final String tanggal;
  final int totalEntri;
  final int hadir;
  final int izin;
  final int sakit;
  final int tidakHadir;

  RekapGuruHarian({
    required this.tanggal,
    required this.totalEntri,
    required this.hadir,
    required this.izin,
    required this.sakit,
    required this.tidakHadir,
  });

  factory RekapGuruHarian.fromJson(Map<String, dynamic> json) => RekapGuruHarian(
        tanggal: json['tanggal'] ?? '',
        totalEntri: json['total_entri'] ?? 0,
        hadir: json['hadir'] ?? 0,
        izin: json['izin'] ?? 0,
        sakit: json['sakit'] ?? 0,
        tidakHadir: json['tidak_hadir'] ?? 0,
      );

  Map<String, dynamic> toJson() => {
        'tanggal': tanggal,
        'total_entri': totalEntri,
        'hadir': hadir,
        'izin': izin,
        'sakit': sakit,
        'tidak_hadir': tidakHadir,
      };
}

class DashboardResponse {
  final UserModel user;
  final DashboardStats stats;
  final String? tahunAjaran;
  final String? semester;
  final AttendanceSummary? attendanceSummary;
  final List<ClassBreakdownItem>? classBreakdown;
  final List<RekapGuruHarian>? rekapGuruHarian;
  final Map<String, dynamic>? guruStats;
  final String? labelPeriode;

  DashboardResponse({
    required this.user,
    required this.stats,
    this.tahunAjaran,
    this.semester,
    this.attendanceSummary,
    this.classBreakdown,
    this.rekapGuruHarian,
    this.guruStats,
    this.labelPeriode,
  });

  factory DashboardResponse.fromJson(Map<String, dynamic> json) =>
      DashboardResponse(
        user: UserModel.fromJson(json['user'] ?? {}),
        stats: DashboardStats.fromJson(json['stats'] ?? {}),
        tahunAjaran: json['tahun_ajaran'],
        semester: json['semester'],
        attendanceSummary: json['attendance_summary'] != null
            ? AttendanceSummary.fromJson(json['attendance_summary'])
            : null,
        classBreakdown: json['class_breakdown'] != null
            ? (json['class_breakdown'] as List)
                .map((e) => ClassBreakdownItem.fromJson(e))
                .toList()
            : null,
        rekapGuruHarian: json['rekap_guru_harian'] != null
            ? (json['rekap_guru_harian'] as List)
                .map((e) => RekapGuruHarian.fromJson(e))
                .toList()
            : null,
        guruStats: json['guru_stats'],
        labelPeriode: json['labelPeriode'],
      );
}
