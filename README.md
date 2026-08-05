# Sistem Absensi Digital Sekolah

Aplikasi manajemen absensi digital untuk sekolah yang dibangun dengan Laravel. Sistem ini mengelola data siswa, guru, kelas, jadwal, absensi, dan nilai.

## Dokumentasi

- Catatan perubahan versi tersedia di [CHANGELOG.md](CHANGELOG.md)

## Fitur Utama

- 👥 Manajemen Pengguna (Admin, Kepala Sekolah, Guru, Siswa)
- 📚 Manajemen Kelas dan Siswa
- 👨‍🏫 Manajemen Guru dan Mata Pelajaran
- 📅 Jadwal KBM (Kegiatan Belajar Mengajar)
- ✅ Sistem Absensi
- 📝 Manajemen Agenda & Kegiatan
- 📊 Manajemen Nilai
- 🏫 Pengaturan Data Sekolah
- 📥 Import/Export Excel untuk data bulk

## Requirements

- PHP >= 8.1
- Composer
- MySQL >= 5.7 atau MariaDB >= 10.3
- Node.js & NPM (untuk asset compilation)
- Extensions PHP yang diperlukan:
  - BCMath
  - Ctype
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML
  - GD
  - ZIP

## Instalasi

### 1. Clone Repository

```bash
git clone <repository-url>
cd absensi_digital
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 2.a (Optional) Auto-install on git pull

To enable automatic dependency installation when someone pulls updates, install the repository git hooks once on the remote server:

```bash
# run once on the server (project root)
bash scripts/install-git-hooks.sh
# or alternatively:
git config core.hooksPath .githooks
chmod +x .githooks/*
```

The hook will run `composer install` and `npm ci` (if `package.json` exists) after a `git pull` / `git merge`.


### 3. Konfigurasi Environment

```bash
cp .env.example .env
```

Edit file `.env` dan sesuaikan konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absensi_digital
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Jalankan Migration

```bash
php artisan migrate
```

atau jika ingin reset database:

```bash
php artisan migrate:fresh
```

### 6. Jalankan Seeder (Opsional)

```bash
php artisan db:seed
```

### 7. Storage Link

```bash
php artisan storage:link
```

### 8. Compile Assets

```bash
npm run dev
```

atau untuk production:

```bash
npm run build
```

### 9. Jalankan Server

```bash
php artisan serve
```

Aplikasi akan berjalan di `http://localhost:8000`

## Troubleshooting

### Error: "Specified key was too long"

Jika Anda mengalami error `SQLSTATE[42000]: Syntax error or access violation: 1071 Specified key was too long`, ini sudah diperbaiki di `AppServiceProvider.php` dengan setting `Schema::defaultStringLength(191)`.

Pastikan file `app/Providers/AppServiceProvider.php` sudah mengandung kode berikut:

```php
use Illuminate\Support\Facades\Schema;

public function boot(): void
{
    Schema::defaultStringLength(191);
}
```

### Database Character Set

Pastikan database MySQL Anda menggunakan charset `utf8mb4` dan collation `utf8mb4_unicode_ci` untuk support karakter Unicode penuh.

## Struktur Database

Aplikasi ini memiliki 56+ tabel yang mengelola:
- Users & Roles
- Data Guru & Siswa
- Kelas & Kurikulum
- Mata Pelajaran & Jam Belajar
- Jadwal KBM
- Absensi & Izin
- Agenda & Kegiatan
- Nilai
- Data Sekolah

## Teknologi

- **Framework**: Laravel 10.x
- **Database**: MySQL
- **Frontend**: Blade Templates + Bootstrap
- **Excel**: Maatwebsite/Laravel-Excel
- **PDF**: DomPDF

## License

Aplikasi ini adalah open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
