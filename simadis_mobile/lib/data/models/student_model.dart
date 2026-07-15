class StudentModel {
  final int id;
  final String? nama;
  final String? nis;
  final String? nisn;
  final int? kelasId;
  final String? jenisKelamin;
  final bool statusAktif;
  final String? jabatanKelas;
  final Map<String, dynamic>? kelas;

  StudentModel({
    required this.id,
    this.nama,
    this.nis,
    this.nisn,
    this.kelasId,
    this.jenisKelamin,
    this.statusAktif = true,
    this.jabatanKelas,
    this.kelas,
  });

  factory StudentModel.fromJson(Map<String, dynamic> json) => StudentModel(
        id: json['id'] ?? 0,
        nama: json['nama'],
        nis: json['nis'],
        nisn: json['nisn'],
        kelasId: json['kelas_id'],
        jenisKelamin: json['jenis_kelamin'],
        statusAktif: json['status_aktif'] ?? true,
        jabatanKelas: json['jabatan_kelas'],
        kelas: json['kelas'],
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'nama': nama,
        'nis': nis,
        'nisn': nisn,
        'kelas_id': kelasId,
        'jenis_kelamin': jenisKelamin,
        'status_aktif': statusAktif,
        'jabatan_kelas': jabatanKelas,
        'kelas': kelas,
      };
}
