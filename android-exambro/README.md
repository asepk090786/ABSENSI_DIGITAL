# ExamBro Client – Android

Aplikasi Android sebagai *secure exam browser* (browser ujian terkunci) untuk sistem **Absensi Digital Sekolah**.

## Tentang Aplikasi

**ExamBro Client** adalah aplikasi Android yang memuat halaman ujian dari server **Absensi Digital** (Laravel) di dalam sebuah *WebView* yang berjalan dalam mode *kiosk*.  
Selama sesi ujian berlangsung, siswa tidak dapat:

- Berpindah ke aplikasi lain
- Menggunakan tombol *Home*, *Recents*, atau *Back*
- Mengambil tangkapan layar (*screenshot*)
- Keluar dari aplikasi tanpa memasukkan PIN admin

---

## Fitur Utama

| Fitur | Keterangan |
|---|---|
| 🔒 Kiosk / Lock-Task Mode | Mengunci perangkat ke satu aplikasi selama ujian |
| 🌐 WebView Aman | Memuat halaman ujian dari server sekolah |
| 📵 Blokir Navigasi | Menonaktifkan tombol *Home*, *Back*, tangkapan layar |
| 🔑 PIN Admin | Keluar kiosk hanya dengan PIN yang dikonfigurasi admin |
| 🔄 Auto-Reconnect | Tombol *Muat Ulang* jika koneksi terputus |
| ⚙️ Konfigurasi Mudah | Admin mengatur URL server & PIN sekali, tersimpan permanen |
| 🍪 Session Cookie | Cookie Laravel dikelola otomatis untuk sesi login siswa |

---

## Persyaratan

- Android **7.0 (API 24)** atau lebih baru
- Koneksi jaringan (Wi-Fi) ke server Absensi Digital
- Server Absensi Digital (Laravel) yang sudah berjalan

---

## Struktur Proyek

```
android-exambro/
├── app/
│   ├── src/main/
│   │   ├── java/com/sekolah/exambro/
│   │   │   ├── SplashActivity.kt       # Layar pembuka
│   │   │   ├── ConfigActivity.kt       # Konfigurasi admin (URL + PIN)
│   │   │   ├── LoginActivity.kt        # Layar sebelum masuk kiosk
│   │   │   ├── ExamActivity.kt         # Browser ujian berkunci (kiosk)
│   │   │   ├── ExitPinActivity.kt      # Dialog PIN keluar kiosk
│   │   │   └── utils/
│   │   │       ├── KioskModeHelper.kt  # Lock-task & immersive mode
│   │   │       └── PreferenceManager.kt# SharedPreferences helper
│   │   ├── res/
│   │   │   ├── layout/                 # XML layout semua Activity
│   │   │   ├── values/                 # strings, colors, themes
│   │   │   ├── drawable/               # Ikon launcher
│   │   │   ├── menu/                   # Menu action bar
│   │   │   └── xml/                    # network_security_config.xml
│   │   └── AndroidManifest.xml
│   ├── build.gradle.kts
│   └── proguard-rules.pro
├── gradle/
│   ├── libs.versions.toml              # Version catalog
│   └── wrapper/
│       └── gradle-wrapper.properties
├── build.gradle.kts
├── settings.gradle.kts
├── gradle.properties
└── README.md                           # File ini
```

---

## Cara Build

### Menggunakan Android Studio

1. Buka Android Studio → **File → Open** → pilih folder `android-exambro/`
2. Tunggu Gradle sync selesai
3. Sambungkan perangkat Android (atau buat AVD)
4. Tekan tombol **Run ▶**

### Menggunakan Command Line

```bash
cd android-exambro
./gradlew assembleDebug          # APK debug
./gradlew assembleRelease        # APK release (perlu keystore)
```

APK dihasilkan di `app/build/outputs/apk/`.

---

## Cara Penggunaan

### Langkah 1 – Konfigurasi Admin (sekali saja)

Saat pertama kali dibuka, aplikasi menampilkan layar **Konfigurasi Admin**:

1. Masukkan **URL Server** Absensi Digital.  
   Contoh: `http://192.168.1.10` atau `https://sekolah.sch.id`
2. Buat **PIN Admin** (4–6 digit) — digunakan untuk keluar dari mode kiosk.
3. Tekan **Simpan Konfigurasi**.

> PIN admin harus dijaga kerahasiaannya. Hanya guru pengawas/proktor yang boleh mengetahuinya.

### Langkah 2 – Mulai Ujian

1. Perangkat diserahkan kepada siswa dalam kondisi sudah terbuka di layar **Mulai Ujian**.
2. Siswa menekan tombol **Mulai Ujian**.
3. Aplikasi masuk ke mode kiosk dan memuat halaman login server Absensi Digital.
4. Siswa login menggunakan NIS & kata sandi, lalu mengerjakan soal ujian seperti biasa.

### Langkah 3 – Akhiri Sesi

1. Setelah ujian selesai, guru/proktor menekan tombol **Keluar** (pojok kanan bawah).
2. Muncul layar **Masukkan PIN Admin**.
3. Guru memasukkan PIN yang telah dikonfigurasi.
4. Aplikasi keluar dari kiosk, menghapus sesi cookie, dan kembali ke layar **Mulai Ujian**.

---

## Konfigurasi Server (Absensi Digital)

Tidak diperlukan konfigurasi tambahan di sisi server.  
Aplikasi menggunakan halaman web yang sudah ada (login, ujian/kuis) melalui WebView.

Pastikan:
- Server Absensi Digital dapat diakses dari jaringan Wi-Fi sekolah
- Jika menggunakan HTTP (bukan HTTPS), pastikan alamat IP server termasuk dalam rentang private (`192.168.x.x`, `10.x.x.x`, atau `172.16.x.x`) — sudah diizinkan di `network_security_config.xml`

---

## Keamanan

- **FLAG_SECURE** mencegah tangkapan layar dan perekaman layar
- **Lock Task Mode** mengunci perangkat ke aplikasi ini (membutuhkan Device Owner policy untuk efek penuh)
- **PIN Lockout**: setelah 3 kali salah memasukkan PIN, sistem dikunci 30 detik
- **Cookie** sesi dihapus otomatis saat ujian berakhir sehingga data siswa sebelumnya tidak bocor
- Semua navigasi ke URL eksternal (di luar server sekolah) diblokir
- Skema URL non-HTTP/HTTPS diblokir

---

## Teknologi

- **Bahasa**: Kotlin
- **Min SDK**: Android 7.0 (API 24)
- **Target SDK**: Android 15 (API 35)
- **Libraries**: AndroidX, Material Components
- **Build System**: Gradle 8.9 + Kotlin DSL

---

## Lisensi

MIT License — lihat file `LICENSE` di root repositori induk.
