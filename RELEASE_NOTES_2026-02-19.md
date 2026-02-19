# Release Notes - 2026-02-19

## Commit
- Hash utama: `938ffc8`
- Changelog update: `1894794`
- Branch: `main`
- Repo: `asepk090786/ABSENSI_DIGITAL`

## Ringkasan Perubahan
1. **Panel Nilai Admin Disederhanakan**
   - Menu cepat penilaian yang terlalu padat disembunyikan untuk role admin/kepala sekolah.
   - Ditambahkan panel **Lihat Nilai (Sederhana)** dengan filter **Kelas** dan **Mata Pelajaran**.

2. **Daftar Nilai Terinput Guru (Admin)**
   - Ditambahkan panel **Daftar Nilai Sudah Diinput Guru**.
   - Menampilkan data detail nilai terisi: tanggal, guru, kelas, mata pelajaran, siswa, komponen, dan nilai.
   - Data dibatasi maksimal 300 baris terbaru agar tetap ringan.

3. **Perbaikan Import XML Jadwal**
   - Memperbaiki error:
     - `Call to undefined method Illuminate\Database\Query\Builder::updateOrCreate()`
   - Perbaikan dilakukan dengan mengganti pemanggilan ke Eloquent model:
     - dari `DB::table('tugas_guru')->updateOrCreate(...)`
     - menjadi `TugasGuru::updateOrCreate(...)`

4. **Perbaikan Import Nilai Harian**
   - Dropdown rencana pembelajaran di modal Import Nilai kini mengikuti kombinasi **Kelas + Mata Pelajaran**.
   - Mencegah tampilan judul rencana yang berulang lintas kelas.

## File Utama yang Berubah
- `app/Http/Controllers/AscTimetableController.php`
- `app/Http/Controllers/NilaiController.php`
- `resources/views/nilai/index.blade.php`
- `CHANGELOG.md`

## Catatan Deploy
Jika ada cache lama pada server produksi:

```bash
php artisan optimize:clear
```
