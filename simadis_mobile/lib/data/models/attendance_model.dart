class AttendanceModel {
  final int id;
  final int kelasId;
  final int? guruId;
  final String? tanggal;
  final String? statusKelas;
  final int? tahunAjaranId;
  final int? semesterId;
  final Map<String, dynamic>? kelas;
  final Map<String, dynamic>? guru;
  final List<StudentAttendance>? students;

  AttendanceModel({
    required this.id,
    required this.kelasId,
    this.guruId,
    this.tanggal,
    this.statusKelas,
    this.tahunAjaranId,
    this.semesterId,
    this.kelas,
    this.guru,
    this.students,
  });

  factory AttendanceModel.fromJson(Map<String, dynamic> json) =>
      AttendanceModel(
        id: json['id'] ?? 0,
        kelasId: json['kelas_id'] ?? 0,
        guruId: json['guru_id'],
        tanggal: json['tanggal'],
        statusKelas: json['status_kelas'],
        tahunAjaranId: json['tahun_ajaran_id'],
        semesterId: json['semester_id'],
        kelas: json['kelas'],
        guru: json['guru'],
        students: json['students'] != null
            ? (json['students'] as List)
                .map((e) => StudentAttendance.fromJson(e))
                .toList()
            : null,
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'kelas_id': kelasId,
        'guru_id': guruId,
        'tanggal': tanggal,
        'status_kelas': statusKelas,
        'tahun_ajaran_id': tahunAjaranId,
        'semester_id': semesterId,
        'kelas': kelas,
        'guru': guru,
        'students': students?.map((e) => e.toJson()).toList(),
      };
}

class StudentAttendance {
  final int? id;
  final int siswaId;
  final String? nama;
  final String? nis;
  final String status;
  final String? keterangan;

  StudentAttendance({
    this.id,
    required this.siswaId,
    this.nama,
    this.nis,
    required this.status,
    this.keterangan,
  });

  factory StudentAttendance.fromJson(Map<String, dynamic> json) =>
      StudentAttendance(
        id: json['id'],
        siswaId: json['siswa_id'] ?? 0,
        nama: json['nama'],
        nis: json['nis'],
        status: json['status'] ?? 'hadir',
        keterangan: json['keterangan'],
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'siswa_id': siswaId,
        'nama': nama,
        'nis': nis,
        'status': status,
        'keterangan': keterangan,
      };
}
