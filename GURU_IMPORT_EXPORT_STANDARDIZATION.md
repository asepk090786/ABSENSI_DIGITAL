# Standardisasi Template Import/Export Data Guru

## Perubahan yang Dilakukan

Ketiga file sudah disamakan untuk menggunakan header dan format data yang konsisten:

### 1. Header Standar (13 Kolom)
```
no_id, id_guru, nama, kode_guru, nip, pangkat_golongan, email, telepon, tanggal_lahir, jenis_kelamin, alamat, username, password
```

### 2. File yang Diubah

#### GuruExport.php (Export Data)
- **Tambahan**: Kolom `id_guru` (id guru dari database) dan `pangkat_golongan`
- **Format Konsisten**:
  - `jenis_kelamin`: Output `L` atau `P` (bukan "Laki-laki"/"Perempuan")
  - `tanggal_lahir`: Format `Y-m-d` (ISO format)
  - `id_guru`: Ditampilkan sehingga memudahkan update

#### GuruTemplateExport.php (Template Kosong)
- **Perubahan Data Sample**: `id_guru` disesuaikan (kosong untuk create, isi untuk update)
- **Petunjuk Diperbarui**: Lebih detail tentang mode create vs update
- **Column Widths**: Disesuaikan untuk kolom `id_guru`

#### GuruImport.php (Logic Import/Update)
- **Tidak ada perubahan**: Sudah support format lengkap dengan semua kolom
- **Parsing Format**:
  - `jenis_kelamin`: Bisa parse "L", "P", "Laki-laki", "Perempuan" (ambil karakter pertama)
  - `tanggal_lahir`: Bisa parse format `Y-m-d`, `d/m/Y`, `d-m-Y`, `m/d/Y`

### 3. Cara Menggunakan

#### Mode Create (Tambah Baru)
```
1. Download template dari: GET /guru-template
2. Kosongkan kolom `id_guru`
3. Isi nama, email, username, password, jenis_kelamin, dan field lainnya
4. Upload dengan tombol "Upload & Tambah Baru" (mode=create)
```

#### Mode Update (Ubah Data Existing)
```
1. Download template update dari: GET /guru-template?mode=update
2. Export data existing dari: GET /guru/export atau copy dari tabel
3. Isi kolom `id_guru` dengan ID guru yang akan diubah
   (Atau gunakan: kode_guru, nip, atau email yang match)
4. Isi ulang nama, email, jenis_kelamin (field required)
5. Optionally isi username/password untuk update akun
6. Upload dengan tombol "Upload & Update" (mode=update)
```

### 4. Field Requirement

#### Untuk Create:
- **Wajib**: nama, email, jenis_kelamin, username, password
- **Opsional**: kode_guru, nip, pangkat_golongan, telepon, tanggal_lahir, alamat

#### Untuk Update:
- **Wajib**: id_guru (atau identitas: kode_guru/nip/email), nama, email, jenis_kelamin
- **Opsional**: username, password, kode_guru, nip, pangkat_golongan, telepon, tanggal_lahir, alamat

### 5. Validasi & Aturan

| Field | Tipe | Unique | Validasi |
|-------|------|--------|----------|
| nama | String (255) | Tidak | Required, max 255 char |
| kode_guru | String (50) | Ya | Opsional, unique |
| nip | String (50) | Ya | Opsional, unique |
| email | Email | Ya | Required, valid email, unique |
| username | String (255) | Ya | Required create, unique |
| password | String | Tidak | Required create, min 6 char |
| jenis_kelamin | Char(1) | Tidak | L atau P |
| tanggal_lahir | Date | Tidak | Format: YYYY-MM-DD |
| telepon | String (30) | Tidak | Opsional |
| alamat | Text | Tidak | Opsional |
| pangkat_golongan | String (100) | Tidak | Opsional |

### 6. Testing

Test sudah dilakukan dan passed:
```bash
php artisan test tests/Feature/GuruImportUpdateTest.php
# PASS: it updates existing guru when matching identifier is provided in update mode
```

---

## Catatan Penting

1. **Excel & Format Angka**: Jika menggunakan NIP panjang, set format kolom sebagai `Text` di Excel agar tidak berubah ke notasi ilmiah.

2. **Duplikasi**: Email dan username harus unik. Jika duplikat, import akan gagal dengan error.

3. **Mode Upload**: Pastikan menggunakan tombol yang benar:
   - "Upload & Tambah Baru" untuk CREATE
   - "Upload & Update" untuk UPDATE
   
4. **Update Partial**: Di mode update, hanya isi kolom yang ingin diubah. Kolom kosong akan skip dan tetap gunakan data lama.

5. **User Account**: Saat update:
   - Jika `username`/`password` diisi, account akan di-update atau dibuat baru
   - Jika kosong, account tidak berubah (untuk update guru tanpa ubah password)
