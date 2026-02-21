# Changelog

Semua perubahan penting pada proyek ini dicatat di file ini.

## v2026.02.20-wali-kelas-laporan-ke-bk - 2026-02-20

### Added
- Form **input laporan wali kelas** pada menu `Wali Kelas > Laporan Guru` untuk mengirim kasus siswa langsung ke alur tindak lanjut Guru BK.
- Route baru `POST /wali-kelas/laporan-guru` (`wali_kelas.laporan_guru.store`) untuk penyimpanan laporan wali kelas.
- Penanda sumber laporan di halaman wali kelas (`Wali Kelas` vs `Guru Mapel`) agar riwayat laporan lebih jelas.

### Changed
- Halaman **Pembinaan BK** kini otomatis mengisi kolom `Laporan Wali Kelas` saat siswa dipilih.
- Ringkasan laporan pada pembinaan BK dipisahkan antara `Laporan Guru` (dari absensi) dan `Laporan Wali Kelas` (input langsung wali kelas).

### Files Updated
- `app/Http/Controllers/WaliKelasController.php`
- `app/Http/Controllers/GuruBkLayananController.php`
- `resources/views/wali_kelas/laporan_guru.blade.php`
- `resources/views/guru_bk_layanan/pembinaan.blade.php`
- `routes/web.php`

## v2026.02.20-bk-layanan-pembinaan-laporan - 2026-02-20

### Added
- Modul **Guru BK per Kelas Binaan**: menu kelas binaan, submenu Layanan BK, Daftar Hadir Layanan BK, Pembinaan BK, dan Tindak Lanjut.
- Form input **Layanan BK** dan sinkron otomatis ke **Daftar Hadir Layanan BK**.
- Fitur **Print Output + Preview Popup** untuk Layanan BK dan Daftar Hadir Layanan BK dengan header sekolah dinamis (logo kiri/kanan, header text/html).
- Fitur **Pembinaan BK** lengkap: input siswa kelas binaan, rekap absensi otomatis (hadir/sakit/izin/alpa/terlambat), laporan guru/wali kelas, dan upload bukti dukung gambar (file + kamera).
- Fitur **Laporan Guru dari Absensi**: guru mapel dapat melaporkan permasalahan siswa ke wali kelas dan Guru BK langsung dari detail absensi.
- Halaman **Laporan Guru** pada akun Wali Kelas untuk menerima laporan dari guru.

### Changed
- Dashboard dan menu Guru BK disesuaikan untuk menampilkan kelas binaan dan monitoring siswa terlambat/tidak masuk.
- Integrasi pembinaan BK diperluas agar kolom **Laporan Guru** otomatis menarik data laporan guru terbaru untuk siswa terpilih.

### Database
- Penambahan kolom relasi `guru_bk_id` pada tabel `kelas`.
- Penambahan tabel `layanan_bk` untuk data layanan konseling BK.
- Penambahan tabel `pembinaan_bk` untuk data pembinaan dan tindak lanjut BK.
- Penambahan tabel `laporan_siswa_guru` untuk alur laporan guru ke wali kelas & BK.

### Files Updated
- `routes/web.php`
- `app/Http/Controllers/AbsensiController.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Controllers/GuruBkController.php`
- `app/Http/Controllers/GuruBkLayananController.php`
- `app/Http/Controllers/WaliKelasController.php`
- `app/Models/Kelas.php`
- `app/Models/Guru.php`
- `app/Models/LayananBk.php`
- `app/Models/PembinaanBk.php`
- `app/Models/LaporanSiswaGuru.php`
- `app/Exports/AbsensiBkMonitoringExport.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/dashboard/guru.blade.php`
- `resources/views/absensi/index.blade.php`
- `resources/views/absensi/show.blade.php`
- `resources/views/guru_bk/*.blade.php`
- `resources/views/guru_bk_layanan/*.blade.php`
- `resources/views/wali_kelas/laporan_guru.blade.php`
- `database/migrations/2026_02_20_000001_add_guru_bk_id_to_kelas_table.php`
- `database/migrations/2026_02_20_000002_create_layanan_bk_table.php`
- `database/migrations/2026_02_20_000003_create_pembinaan_bk_table.php`
- `database/migrations/2026_02_20_000004_create_laporan_siswa_guru_table.php`

## v2026.02.19-admin-nilai-xml-fix - 2026-02-19

### Added
- Panel admin **Lihat Nilai (Sederhana)** dengan filter Kelas dan Mata Pelajaran agar akses data nilai lebih ringkas.
- Panel admin **Daftar Nilai Sudah Diinput Guru** untuk melihat detail nilai terisi (guru, kelas, mapel, siswa, komponen, nilai).

### Changed
- Tampilan halaman Nilai untuk admin disederhanakan dengan menghilangkan **Menu Cepat Penilaian** yang terlalu padat.
- Opsi filter kelas/mapel untuk admin disiapkan dari jadwal aktif secara unik dan terurut.
- Dropdown rencana pembelajaran pada modal Import Nilai dibuat dinamis mengikuti kombinasi **kelas + mapel**.

### Fixed
- Perbaikan error import XML: `Call to undefined method Illuminate\\Database\\Query\\Builder::updateOrCreate()`.
- Proses auto-generate `tugas_guru` saat import XML kini memakai `TugasGuru::updateOrCreate()` (Eloquent) sehingga import berjalan normal.

### Files Updated
- `app/Http/Controllers/AscTimetableController.php`
- `app/Http/Controllers/NilaiController.php`
- `resources/views/nilai/index.blade.php`

## v2026.02.18-nilai-dinamis - 2026-02-18

### Added
- Tag rilis `v2026.02.18-nilai-dinamis` untuk menandai versi stabil perubahan fitur nilai.
- Tampilan pivot dinamis pada tabel Rekap Nilai: kolom komponen penilaian otomatis, plus kolom **JUMLAH** dan **RATA-RATA**.
- Tampilan pivot dinamis pada tabel Nilai Harian: baris per siswa dengan kolom komponen penilaian.

### Changed
- Alur download template import nilai agar mengikuti kelas yang dipilih di modal import.
- Proses import nilai harian agar pencocokan siswa lebih robust (normalisasi NIS/NISN dan fallback nama unik per kelas).
- Query rekap nilai disesuaikan agar konsisten terhadap periode aktif dan kompatibel dengan data legacy.
- Tabel Nilai Harian disesuaikan untuk menggunakan komponen dari data master `komponen_nilai`.

### Fixed
- Perbaikan kasus import menampilkan error massal "Siswa tidak ditemukan pada kelas ini" pada data valid.
- Perbaikan kasus rekap menampilkan data sebagian setelah proses upload/import nilai.
- Pembersihan path invalid pada repository agar status git clean di Windows.

### Files Updated
- `app/Http/Controllers/NilaiController.php`
- `app/Http/Controllers/RekapNilaiController.php`
- `app/Imports/NilaiHarianImport.php`
- `resources/views/nilai/index.blade.php`
- `resources/views/rekap_nilai/index.blade.php`
