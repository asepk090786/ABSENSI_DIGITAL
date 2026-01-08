Param(
    [string]$TargetDir = "."
)

if (Test-Path "$TargetDir\artisan") {
    Write-Host "Laravel sudah terlihat terinstal di $TargetDir" -ForegroundColor Yellow
    exit 0
}

Write-Host "Menjalankan composer create-project di $TargetDir..." -ForegroundColor Cyan
composer create-project --prefer-dist laravel/laravel $TargetDir

if ($LASTEXITCODE -ne 0) {
    Write-Host "composer gagal. Pastikan Composer terinstal dan tersedia di PATH." -ForegroundColor Red
    exit $LASTEXITCODE
}

Set-Location $TargetDir

if (Test-Path ".env.example") {
    Copy-Item .env.example .env -Force
}

composer install
php artisan key:generate
php artisan migrate
npm install
npm run dev

Write-Host "Selesai. Jalankan: php artisan serve" -ForegroundColor Green
