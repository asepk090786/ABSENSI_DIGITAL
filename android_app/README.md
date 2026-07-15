# Android App for SIMADIS

Proyek Android ini dibuat sebagai skeleton untuk aplikasi mobile yang mengonsumsi API Laravel SIMADIS.

## Struktur
- app/: modul aplikasi Android
- app/src/main/java/: source code utama
- app/src/main/res/: resource UI
- gradle/: konfigurasi build

## Endpoint yang didukung
- POST /api/auth/login
- POST /api/auth/logout
- GET /api/me
- GET /api/dashboard
- GET /api/profile
- GET /api/attendance/summary
- GET /api/classes
- GET /api/students
- GET /api/teachers
- GET /api/schedule

## Catatan
- Ubah BASE_URL sesuai host backend saat dipakai di device.
- Untuk emulator lokal, gunakan http://10.0.2.2:8000
