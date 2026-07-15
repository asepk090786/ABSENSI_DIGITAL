class ClassModel {
  final int id;
  final String? namaKelas;
  final String? kodeKelas;
  final String? tingkatKelas;
  final String? jurusan;
  final int? waliKelasId;
  final int? totalSiswa;
  final Map<String, dynamic>? waliKelas;

  ClassModel({
    required this.id,
    this.namaKelas,
    this.kodeKelas,
    this.tingkatKelas,
    this.jurusan,
    this.waliKelasId,
    this.totalSiswa,
    this.waliKelas,
  });

  factory ClassModel.fromJson(Map<String, dynamic> json) => ClassModel(
        id: json['id'] ?? 0,
        namaKelas: json['nama_kelas'],
        kodeKelas: json['kode_kelas'],
        tingkatKelas: json['tingkat_kelas'],
        jurusan: json['jurusan'],
        waliKelasId: json['wali_kelas_id'],
        totalSiswa: json['total_siswa'],
        waliKelas: json['wali_kelas'],
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'nama_kelas': namaKelas,
        'kode_kelas': kodeKelas,
        'tingkat_kelas': tingkatKelas,
        'jurusan': jurusan,
        'wali_kelas_id': waliKelasId,
        'total_siswa': totalSiswa,
        'wali_kelas': waliKelas,
      };
}
