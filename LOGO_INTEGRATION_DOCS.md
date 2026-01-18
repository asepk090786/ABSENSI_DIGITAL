# Logo Sekolah - Dokumentasi Integrasi

## Deskripsi

Logo sekolah sekarang saling berhubungan antara halaman login dan halaman edit data sekolah. Ketika logo diubah di halaman "Edit Data Sekolah", logo di halaman login akan otomatis berubah juga karena keduanya menggunakan sumber data yang sama dari database.

## Perubahan yang Dilakukan

### 1. Unified Storage System
- **Sebelum**: Logo di halaman edit disimpan ke `public/images/` dengan nama file saja
- **Sesudah**: Semua logo disimpan ke `storage/app/public/logos/` dengan path lengkap
- **Manfaat**: Keduanya sekarang menggunakan sistem yang sama, lebih terorganisir, dan mudah dikelola

### 2. Database Field
- Field `logo` di database sekarang menyimpan path lengkap: `logos/filename.jpg`
- Contoh: Sebelumnya `696ce2a1d0a73.jpg`, sekarang `logos/zSSNHMkuuafO4CpuBcxDg7yCVXSmAqMplCV4fQjw.jpg`

### 3. File yang Dimodifikasi

#### SekolahController.php
- Metode `store()`: Menggunakan `Storage::disk('public')->put('logos', $file)` untuk upload
- Metode `update()`: 
  - Hapus logo lama dari storage jika ada
  - Upload logo baru ke `logos` folder
  - Update database dengan path baru

#### sekolah/edit.blade.php
- Preview logo sekarang mengambil dari `asset('storage/' . $sekolah->logo)` bukan `asset('images/' . $sekolah->logo)`

#### login.blade.php
- Tidak ada perubahan di template, sudah benar menggunakan storage
- Otomatis akan menampilkan logo yang baru setelah update

## Script Migration

### migrate_logo_storage.php
Script ini membantu migrate logo lama dari format `public/images/` ke format `storage/app/public/logos/`:

```bash
php migrate_logo_storage.php
```

Fungsi:
- Deteksi logo lama format `public/images/`
- Copy file ke `storage/app/public/logos/`
- Update path di database
- Hapus file lama

## Cara Menggunakan

### Upload/Ubah Logo Sekolah
1. Buka menu **Pengaturan → Data Sekolah**
2. Klik tombol **Edit**
3. Upload logo baru via input field "Logo Sekolah"
4. Preview akan muncul di bawah input
5. Klik **Simpan**
6. Logo akan otomatis muncul di halaman login

### Hasil yang Diharapkan

**Halaman Login** - Logo sekolah akan tampil di sebelah logo SIMADIS:
```
[SIMADIS Logo]    [School Logo]
      SMA NEGERI 1 PONTANG
   Jl Kubang Puji Ds. Pontang...
```

**Halaman Edit Sekolah** - Logo yang sama akan tampil sebagai preview:
```
Logo Sekolah
[ Choose File ] [School Logo]
```

## Spesifikasi File

- **Format**: JPEG, PNG, JPG
- **Ukuran Maksimal**: 2MB
- **Lokasi**: `storage/app/public/logos/`
- **Akses URL**: `https://domain.com/storage/logos/filename.jpg`

## Backup

Jika Anda masih memiliki file logo lama di `public/images/`, file tersebut akan otomatis dihapus setelah migration. Pastikan Anda sudah backup jika diperlukan.

## Troubleshooting

### Logo tidak muncul di login
- Pastikan symbolic link `public/storage` terbuat dengan benar
- Jalankan: `php artisan storage:link`
- Verifikasi file ada di: `storage/app/public/logos/`

### Upload logo gagal
- Pastikan folder `storage/app/public/logos/` bisa ditulis
- Periksa setting permission folder
- Pastikan file size tidak melebihi 2MB

### Path logo masih format lama
- Jalankan script migration: `php migrate_logo_storage.php`
- Verifikasi database telah ter-update
- Clear cache jika perlu: `php artisan cache:clear`

## Database Query

Untuk melihat logo yang tersimpan di database:
```sql
SELECT id, nama_sekolah, logo FROM sekolah;
```

Untuk update manual jika diperlukan:
```sql
UPDATE sekolah SET logo = 'logos/newfilename.jpg' WHERE id = 1;
```

---

**Dibuat pada**: Januari 18, 2026
**Versi**: 1.0
**Status**: Production Ready
