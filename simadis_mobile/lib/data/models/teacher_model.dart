class TeacherModel {
  final int id;
  final String? nama;
  final String? nip;
  final String? jenisKelamin;
  final bool statusAktif;
  final String? telepon;
  final String? alamat;
  final String? tanggalLahir;
  final String? email;
  final Map<String, dynamic>? user;

  TeacherModel({
    required this.id,
    this.nama,
    this.nip,
    this.jenisKelamin,
    this.statusAktif = true,
    this.telepon,
    this.alamat,
    this.tanggalLahir,
    this.email,
    this.user,
  });

  factory TeacherModel.fromJson(Map<String, dynamic> json) => TeacherModel(
        id: json['id'] ?? 0,
        nama: json['nama'],
        nip: json['nip'],
        jenisKelamin: json['jenis_kelamin'],
        statusAktif: json['status_aktif'] ?? true,
        telepon: json['telepon'],
        alamat: json['alamat'],
        tanggalLahir: json['tanggal_lahir'],
        email: json['email'],
        user: json['user'],
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'nama': nama,
        'nip': nip,
        'jenis_kelamin': jenisKelamin,
        'status_aktif': statusAktif,
        'telepon': telepon,
        'alamat': alamat,
        'tanggal_lahir': tanggalLahir,
        'email': email,
        'user': user,
      };
}
