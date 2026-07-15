class UserModel {
  final int id;
  final String? name;
  final String? username;
  final String? email;
  final String? role;
  final List<String>? roles;
  final int? guruId;
  final int? siswaId;
  final bool isActive;
  final String? foto;

  UserModel({
    required this.id,
    this.name,
    this.username,
    this.email,
    this.role,
    this.roles,
    this.guruId,
    this.siswaId,
    this.isActive = true,
    this.foto,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) => UserModel(
        id: json['id'] ?? 0,
        name: json['name'],
        username: json['username'],
        email: json['email'],
        role: json['role'],
        roles: json['roles'] != null ? List<String>.from(json['roles']) : null,
        guruId: json['guru_id'],
        siswaId: json['siswa_id'],
        isActive: json['is_active'] ?? true,
        foto: json['foto'],
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'username': username,
        'email': email,
        'role': role,
        'roles': roles,
        'guru_id': guruId,
        'siswa_id': siswaId,
        'is_active': isActive,
        'foto': foto,
      };
}
