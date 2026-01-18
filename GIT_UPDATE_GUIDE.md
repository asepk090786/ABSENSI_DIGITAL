# Panduan Git Update Script

Script ini membantu Anda melakukan update ke GitHub dengan konfirmasi interaktif sebelum setiap aksi penting.

## Fitur

✓ Menampilkan status branch saat ini
✓ Mendeteksi perubahan yang belum di-commit
✓ Menampilkan jumlah commit yang belum di-push
✓ **Memberikan pilihan untuk commit** sebelum push
✓ **Memberikan pilihan untuk push** ke GitHub
✓ Mendeteksi update dari GitHub
✓ **Memberikan pilihan untuk pull** update dari GitHub

## Cara Penggunaan

### Menggunakan Script Interaktif (Recommended)

Jalankan script ini untuk mendapatkan menu interaktif:

```bash
php git-update.php
```

Script akan menanyakan Anda setiap langkah:

1. **"Apakah ingin commit perubahan ini?"**
   - Ketik `y` atau `yes` untuk commit
   - Ketik `n` atau `no` untuk membatalkan

2. **"Apakah ingin push ke GitHub?"**
   - Ketik `y` atau `yes` untuk push
   - Ketik `n` atau `no` untuk membatalkan

3. **"Apakah ingin pull update dari GitHub?"**
   - Ketik `y` atau `yes` untuk pull
   - Ketik `n` atau `no` untuk membatalkan

### Contoh Interaksi

```
═══════════════════════════════════════
  SISTEM UPDATE GIT INTERAKTIF
═══════════════════════════════════════

ℹ Mengecek branch dan status...
✓ Branch saat ini: main

Perubahan yang ditemukan:
  ?? file1.php
  ?? file2.php

Apakah ingin commit perubahan ini? [Y/n]: y

✓ Files di-stage
✓ Commit berhasil!

Apakah ingin push ke GitHub? [Y/n]: y

→ git push origin main
✓ Push berhasil!

ℹ Mengecek update dari GitHub...
✓ Repository sudah up-to-date dengan GitHub

═══════════════════════════════════════
  SELESAI
═══════════════════════════════════════

✓ Proses update selesai!
```

## Default Input

- Jika Anda hanya menekan ENTER tanpa mengetik apapun, script akan menggunakan input default: **Y (Ya)**

## Pesan Commit Otomatis

Ketika Anda memilih untuk commit, pesan commit akan otomatis di-generate:
```
Update dari fix_logo dan git-update script
```

## Deskripsi Warna Output

- 🟢 **Hijau** (`✓`) = Aksi berhasil
- 🔴 **Merah** (`✗`) = Ada error
- 🟡 **Kuning** (`⚠`) = Peringatan/konfirmasi yang dibutuhkan
- 🔵 **Biru** (`ℹ`) = Informasi

## Troubleshooting

### Error: "Gagal mendapatkan informasi branch!"

Pastikan Anda berada di direktori repository Git.

### Error saat push

Pastikan:
- Anda memiliki akses ke repository di GitHub
- Koneksi internet berjalan normal
- Git credentials sudah di-setup

### Error saat pull

Kemungkinan ada konflikt. Anda perlu:
1. Membatalkan pull menggunakan `git merge --abort`
2. Menyelesaikan konflikt secara manual
3. Jalankan script lagi

## Script Otomatis (fix_logo.php)

Ada juga script `fix_logo.php` yang digunakan untuk memperbaiki path logo sekolah di database:

```bash
php fix_logo.php
```

Script ini secara otomatis:
- Menemukan file logo terbaru di storage
- Update database dengan path yang benar
- Menampilkan hasil operasi

## Tips

1. **Selalu review** perubahan sebelum commit
2. **Backup lokal** jika ada data penting sebelum pull
3. **Gunakan pesan commit yang jelas** jika ingin custom
4. **Check GitHub** secara berkala untuk update dari tim lain

---

**Dibuat untuk:** SIMADIS - Sistem Manajemen Absensi Digital
**Tanggal:** Januari 2026
