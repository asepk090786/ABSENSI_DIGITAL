# SIMADIS - Aplikasi Android

Aplikasi mobile Android SIMADIS yang merupakan padanan dari aplikasi web Laravel SIMADIS.

## Fitur

- **Login** - Autentikasi dengan username/email dan password
- **Dashboard** - Statistik kehadiran siswa/guru dengan filter tanggal, minggu, bulan
- **Data Kelas** - CRUD data kelas (hanya lihat untuk mobile)
- **Data Siswa** - Lihat dan cari data siswa
- **Data Guru** - Lihat dan cari data guru
- **Input Absensi** - Input dan lihat daftar absensi
- **Rekap Absensi** - Rekap kehadiran guru per hari
- **Laporan** - Akses laporan kehadiran
- **Profil** - Lihat dan edit profil pengguna

## Hak Akses

- Admin - akses penuh
- Guru / Guru Mapel / Guru Kelas / Wali Kelas / Guru BK / Guru Piket - akses sesuai role
- Siswa - lihat data diri dan jadwal

## Teknologi

- Flutter 3.x+
- Riverpod (state management)
- Dio (HTTP client)
- GoRouter (navigation)
- Shared Preferences (local storage)
- Material Design 3

## Setup

### 1. Prasyarat

- Flutter SDK >= 3.0.0
- Android Studio / VS Code
- Device/emulator

### 2. Proyek Baru

```bash
flutter create simadis_mobile
cd simadis_mobile
```

### 3. Ganti Folder `lib`

Ganti seluruh isi folder `lib/` di proyek Flutter dengan folder `lib/` dari repository ini.

### 4. Update `pubspec.yaml`

Ganti isi `pubspec.yaml` dengan yang ada di repository ini, lalu jalankan:

```bash
flutter pub get
```

### 5. Konfigurasi API Base URL

Buka `lib/services/api_service.dart` dan ubah `baseUrl` sesuai URL server Laravel:

```dart
static const String baseUrl = 'http://10.0.2.2:8000/api'; // Emulator Android
// static const String baseUrl = 'http://192.168.x.x:8000/api'; // Real device
```

Pastikan Laravel app berjalan dan API dapat diakses dari device/emulator.

### 6. Konfigurasi Android Network Security (untuk HTTP)

Jika server menggunakan HTTP (bukan HTTPS), untuk Android 9+ perlu menambahkan network security config:

Buat `android/app/src/main/res/xml/network_security_config.xml`:

```xml
<?xml version="1.0" encoding="utf-8"?>
<network-security-config>
    <domain-config cleartextTrafficPermitted="true">
        <domain includeSubdomains="true">10.0.2.2</domain>
        <domain includeSubdomains="true">192.168.x.x</domain>
    </domain-config>
</network-security-config>
```

Update `android/app/src/main/AndroidManifest.xml`:

```xml
<application
    android:usesCleartextTraffic="true"
    ...>
```

### 7. Build dan Run

```bash
flutter run
```

## Struktur Project

```
lib/
  app.dart                          - Konfigurasi MaterialApp dan tema
  main.dart                         - Entry point
  core/
    constants/
      api_constants.dart            - Endpoint API
      app_constants.dart            - Konstanta umum
    theme/
      app_colors.dart               - Warna tema light/dark
      app_theme.dart                - ThemeData dan ThemeMode
    utils/
      date_formatter.dart           - Format tanggal
      role_helper.dart              - Helper role pengguna
    widgets/
      loading_indicator.dart        - Widget loading
      empty_state_widget.dart       - Widget empty state
      stat_card.dart                - Kartu statistik
      attendance_table.dart         - Tabel absensi
  data/
    datasources/
      api_client.dart               - Konfigurasi Dio
      storage_service.dart          - SharedPreferences wrapper
    models/
      user_model.dart               - Model pengguna
      class_model.dart              - Model kelas
      student_model.dart            - Model siswa
      teacher_model.dart            - Model guru
      attendance_model.dart         - Model absensi
      dashboard_model.dart          - Model dashboard
    repositories/
      auth_repository.dart          - Repository autentikasi
      dashboard_repository.dart     - Repository dashboard
      class_repository.dart         - Repository kelas
      student_repository.dart       - Repository siswa
      teacher_repository.dart       - Repository guru
      attendance_repository.dart    - Repository absensi
      profile_repository.dart       - Repository profil
      schedule_repository.dart      - Repository jadwal
  features/
    auth/screens/
      login_screen.dart             - Halaman login
      splash_screen.dart            - Splash screen
    dashboard/screens/
      dashboard_screen.dart         - Halaman dashboard
    classes/screens/
      class_list_screen.dart        - Daftar kelas
      class_detail_screen.dart      - Detail kelas
    students/screens/
      student_list_screen.dart      - Daftar siswa
    teachers/screens/
      teacher_list_screen.dart      - Daftar guru
    attendance/screens/
      attendance_list_screen.dart   - Daftar absensi
      attendance_input_screen.dart  - Input absensi
      attendance_recap_screen.dart  - Rekap absensi guru
    reports/screens/
      report_screen.dart            - Laporan
    profile/screens/
      profile_screen.dart           - Profil pengguna
  services/
    api_service.dart                - Provider Dio, StorageService
  shared/
    providers/
      auth_state.dart               - State autentikasi
      theme_provider.dart           - State tema
    routes/
      app_router.dart               - Konfigurasi GoRouter
```

## Catatan

- Fitur belum 100% lengkap (misal: export Excel belum, import belum, beberapa fitur guru BK belum)
- Base URL perlu disesuaikan dengan server Laravel masing-masing
- Model dan endpoint dapat ditambah sesuai kebutuhan
