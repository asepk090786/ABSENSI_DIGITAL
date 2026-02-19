# Changelog

Semua perubahan penting pada proyek ini dicatat di file ini.

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
