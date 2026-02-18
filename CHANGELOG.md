# Changelog

Semua perubahan penting pada proyek ini dicatat di file ini.

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
