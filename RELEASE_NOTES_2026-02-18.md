# Release Notes - 2026-02-18

## Commit
- Hash: `0d97fcf`
- Branch: `main`
- Repo: `asepk090786/ABSENSI_DIGITAL`

## Ringkasan Perubahan
1. **Agenda Guru Export (PDF/Print)**
   - Menambahkan kolom **Jenis Kegiatan**.
   - Menggabungkan kolom kehadiran + keterangan menjadi **Keterangan/Uraian Kegiatan**.
   - Kehadiran otomatis kosong untuk kegiatan selain KBM.
   - Layout print dipadatkan agar lebih optimal untuk 1 lembar.

2. **Agenda Kegiatan Non-KBM**
   - Menambahkan tombol **Tambah Kegiatan** pada halaman Agenda Guru.
   - Menambahkan daftar kegiatan umum guru (autocomplete) saat input Pengembangan Diri.
   - Daftar kegiatan umum dipusatkan ke config baru: `config/kegiatan_guru.php`.

3. **Navigasi Sidebar**
   - Menambahkan menu **Rencana Pembelajaran** di grup menu Pembelajaran.

4. **Rencana Pembelajaran (Listing)**
   - Judul yang sama pada tingkat/mapel yang sama ditampilkan sebagai **satu baris**.
   - Menampilkan gabungan kelas pada baris tersebut.
   - Aksi hapus baris menghapus seluruh entri terkait judul tersebut.

5. **Nilai Harian (Tambah/Import)**
   - Memperbaiki sumber dropdown rencana pembelajaran agar berbasis **kelas + mapel** (bukan mapel saja).
   - Mencegah duplikasi judul rencana pada dropdown.
   - Memperbaiki error `Undefined array key "id"` di modal Import Nilai setelah perubahan struktur data.

## File Utama yang Berubah
- `app/Http/Controllers/NilaiController.php`
- `app/Http/Controllers/RencanaPembelajaranController.php`
- `resources/views/nilai/index.blade.php`
- `resources/views/rencana_pembelajaran/index.blade.php`
- `resources/views/agenda_guru/export.blade.php`
- `resources/views/agenda_guru/index.blade.php`
- `resources/views/agenda_kelas/create.blade.php`
- `resources/views/agenda_kelas/show.blade.php`
- `resources/views/layouts/app.blade.php`
- `config/kegiatan_guru.php`

## Catatan Deploy
Jika konfigurasi belum terbaca pada environment server produksi:

```bash
php artisan optimize:clear
```
