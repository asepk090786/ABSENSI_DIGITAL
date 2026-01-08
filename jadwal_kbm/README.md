# Jadwal KBM

Proyek ini adalah kerangka awal untuk aplikasi web berbasis Laravel bernama "jadwal_kbm".

Tujuan:
- Menyiapkan project Laravel dasar dan langkah-langkah instalasi.

Langkah instalasi (PowerShell di Windows):

```powershell
cd "C:\Users\sman1\OneDrive\Desktop\Project_absensi"
composer create-project --prefer-dist laravel/laravel jadwal_kbm
cd jadwal_kbm
copy .env.example .env
composer install
php artisan key:generate
php artisan migrate
npm install
npm run dev
php artisan serve --host=127.0.0.1 --port=8000
```

Langkah instalasi (bash/macOS/Linux):

```bash
cd "$HOME/Desktop/Project_absensi"
composer create-project --prefer-dist laravel/laravel jadwal_kbm
cd jadwal_kbm
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
npm install
npm run dev
php artisan serve
```

Anda juga dapat menjalankan skrip `setup.ps1` (Windows) atau `setup.sh` (Linux/macOS) dari folder parent untuk otomatisasi.

Jika Anda ingin, saya bisa menjalankan perintah `composer create-project` sekarang (butuh Composer terinstal).