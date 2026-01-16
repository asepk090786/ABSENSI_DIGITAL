# Panduan Instalasi Sistem Absensi Digital

## Persiapan

Sebelum memulai instalasi, pastikan Anda sudah menginstall:

1. **PHP 8.1 atau lebih tinggi**
   ```bash
   php -v
   ```

2. **Composer**
   ```bash
   composer -v
   ```

3. **MySQL atau MariaDB**
   ```bash
   mysql --version
   ```

4. **Node.js & NPM**
   ```bash
   node -v
   npm -v
   ```

## Langkah-langkah Instalasi

### 1. Clone atau Download Project

```bash
git clone <repository-url>
cd absensi_digital
```

### 2. Install Dependencies PHP

```bash
composer install
```

Jika ada error, coba jalankan:
```bash
composer install --ignore-platform-reqs
```

### 3. Install Dependencies JavaScript

```bash
npm install
```

### 4. Konfigurasi Environment

Salin file `.env.example` menjadi `.env`:

**Windows (PowerShell):**
```powershell
Copy-Item .env.example .env
```

**Linux/Mac:**
```bash
cp .env.example .env
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Buat Database

Buat database baru di MySQL:

```sql
CREATE DATABASE absensi_digital CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 7. Konfigurasi Database di .env

Edit file `.env` dan sesuaikan dengan konfigurasi database Anda:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absensi_digital
DB_USERNAME=root
DB_PASSWORD=your_password_here
```

### 8. Jalankan Migration

```bash
php artisan migrate
```

**Catatan Penting:**
- Fix untuk error "Specified key was too long" sudah diterapkan di `app/Providers/AppServiceProvider.php`
- Jika masih ada error, pastikan database menggunakan charset `utf8mb4`

### 9. (Opsional) Jalankan Seeder

Jika ada seeder untuk data awal:

```bash
php artisan db:seed
```

### 10. Buat Storage Link

```bash
php artisan storage:link
```

Ini akan membuat symbolic link dari `public/storage` ke `storage/app/public`

### 11. Set Permissions (Linux/Mac)

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 12. Compile Assets

Untuk development:
```bash
npm run dev
```

Untuk production:
```bash
npm run build
```

### 13. Jalankan Server Development

```bash
php artisan serve
```

Aplikasi akan berjalan di: **http://localhost:8000**

## Konfigurasi Tambahan

### Upload Folder

Pastikan folder berikut memiliki permission write:
- `storage/`
- `bootstrap/cache/`
- `public/uploads/`

### File Upload Limits

Edit `php.ini` jika perlu menaikkan limit upload:

```ini
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 300
```

## Troubleshooting

### Error: SQLSTATE[HY000] [1045] Access denied

**Solusi:** Periksa username dan password database di file `.env`

### Error: SQLSTATE[HY000] [2002] Connection refused

**Solusi:** 
- Pastikan MySQL service sudah berjalan
- Periksa DB_HOST dan DB_PORT di `.env`

### Error: Specified key was too long

**Solusi:** Sudah diperbaiki di `AppServiceProvider.php`. Pastikan file tersebut berisi:

```php
use Illuminate\Support\Facades\Schema;

public function boot(): void
{
    Schema::defaultStringLength(191);
}
```

### Error: npm ERR! code ELIFECYCLE

**Solusi:**
```bash
rm -rf node_modules package-lock.json
npm cache clean --force
npm install
```

### Error: Class not found

**Solusi:**
```bash
composer dump-autoload
```

### Clear Cache

Jika ada masalah dengan cache:

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

## Production Deployment

### 1. Optimisasi

```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

### 2. Environment

Ubah di `.env`:
```env
APP_ENV=production
APP_DEBUG=false
```

### 3. Security

- Pastikan `.env` tidak di-commit ke repository
- Generate APP_KEY yang kuat
- Gunakan HTTPS di production
- Set permission folder yang benar

## Bantuan

Jika mengalami masalah, periksa:
1. Laravel log di `storage/logs/laravel.log`
2. PHP error log
3. MySQL error log

Atau hubungi developer/maintainer project ini.
