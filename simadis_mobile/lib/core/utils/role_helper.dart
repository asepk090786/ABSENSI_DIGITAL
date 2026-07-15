class RoleHelper {
  static bool isAdmin(List<String> roles) =>
      roles.any((r) => r.toLowerCase() == 'admin');

  static bool isGuru(List<String> roles) => roles.any((r) =>
      r.toLowerCase().contains('guru') &&
      r.toLowerCase() != 'guru bk' &&
      r.toLowerCase() != 'guru piket');

  static bool isGuruBk(List<String> roles) =>
      roles.any((r) => r.toLowerCase() == 'guru bk');

  static bool isGuruPiket(List<String> roles) =>
      roles.any((r) => r.toLowerCase() == 'guru piket');

  static bool isWaliKelas(List<String> roles) =>
      roles.any((r) => r.toLowerCase() == 'wali kelas');

  static bool isSiswa(List<String> roles) =>
      roles.any((r) => r.toLowerCase() == 'siswa');

  static bool isKepalaSekolah(List<String> roles) =>
      roles.any((r) => r.toLowerCase() == 'kepala sekolah');

  static bool canManageAttendance(List<String> roles) =>
      isAdmin(roles) || isGuru(roles) || isGuruBk(roles) || isGuruPiket(roles) || isWaliKelas(roles) || isKepalaSekolah(roles);

  static bool canManageStudents(List<String> roles) =>
      isAdmin(roles) || isWaliKelas(roles) || isKepalaSekolah(roles);

  static bool canManageTeachers(List<String> roles) =>
      isAdmin(roles) || isKepalaSekolah(roles);

  static bool canManageClasses(List<String> roles) =>
      isAdmin(roles) || isKepalaSekolah(roles);
}
