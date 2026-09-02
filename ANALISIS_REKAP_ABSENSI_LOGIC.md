# 📋 Analisis Logika Rekap Absensi Bulanan - Role Guru

**File yang Dianalisis:**
- Controller: `app/Http/Controllers/AbsensiController.php` (Lines 237-350)
- View: `resources/views/absensi/rekap_bulanan.blade.php`
- Routes: `routes/web.php` (Lines 231-234)

---

## 🔍 Alur Kerja Rekap Absensi

### 1️⃣ Halaman Utama: `rekapBulanan()` (Line 237-291)

**Endpoint:** `GET /absensi/rekap-bulanan?kelas_id=...`

**Fungsi:**
- Menampilkan daftar rekap absensi PER BULAN untuk semua kelas yang diajar guru

**Akses Control untuk Role Guru:**
```php
when(! $isAdminOrKepala && $user->guru_id, 
  fn ($q) => $q->where('ak.guru_id', $user->guru_id))
```
- ✅ Guru HANYA bisa lihat kelas yang diajar sendiri
- ✅ Admin/Kepala bisa lihat semua kelas

**Query Aggregasi yang Dijalankan:**
```sql
SELECT
  k.id, k.nama_kelas,
  YEAR(ak.tanggal) as tahun,
  MONTH(ak.tanggal) as bulan,
  COUNT(DISTINCT ak.id) as total_pertemuan,
  SUM(CASE WHEN LOWER(ass.status) = 'hadir' THEN 1 ELSE 0 END) as hadir,
  SUM(CASE WHEN LOWER(ass.status) IN ('izin','ijin') THEN 1 ELSE 0 END) as izin,
  SUM(CASE WHEN LOWER(ass.status) = 'sakit' THEN 1 ELSE 0 END) as sakit,
  SUM(CASE WHEN LOWER(ass.status) IN ('terlambat','telat') THEN 1 ELSE 0 END) as terlambat,
  SUM(CASE WHEN LOWER(ass.status) IN ('alpha','alpa','alfa','absen','tidak_hadir') THEN 1 ELSE 0 END) as alpha
FROM absensi_kelas ak
JOIN absensi_siswa ass ON ass.absensi_kelas_id = ak.id
JOIN kelas k ON k.id = ak.kelas_id
WHERE ak.tahun_ajaran_id = ? AND ak.semester_id = ? AND ak.guru_id = ?
GROUP BY YEAR(ak.tanggal), MONTH(ak.tanggal), k.id
ORDER BY YEAR(ak.tanggal) DESC, MONTH(ak.tanggal) DESC, k.nama_kelas ASC
```

**Output di View:**
Tabel dengan kolom:
- Kelas
- Bulan/Tahun
- Total Pertemuan (COUNT DISTINCT absensi_kelas records)
- Hadir, Terlambat, Izin, Sakit, Alpha
- Tombol: Detail, Export (Excel), Print (PDF)

---

### 2️⃣ Halaman Detail: `rekapBulananDetail()` (Line 293-316)

**Endpoint:** `GET /absensi/rekap-bulanan/detail?kelas_id=X&bulan=Y&tahun=Z`

**Fungsi:**
- Menampilkan detail REKAP PER SISWA dalam satu kelas untuk satu bulan

**Parameter Validation:**
```php
$kelasId && $bulan >= 1 && $bulan <= 12 && $tahunKalender >= 2000
// Jika parameter tidak lengkap → error 422
```

**Akses Control:**
```php
ensureMonthlyReportAccess($user, $tahun, $semester, $kelasId)
```
- Validasi: kelas tersebut harus ada record absensi
- Guru: hanya bisa akses kelas yang diajar (`guru_id = ?`)
- Admin/Kepala: bisa akses semua kelas

**Data yang Diambil:**
- Panggil `getRangeStudentReportRows()` dengan range tanggal 1-28/29/30/31 bulan tersebut
- Return data per siswa dengan count: hadir, terlambat, sakit, izin, alpha

---

### 3️⃣ Perhitungan Detail: `getRangeStudentReportRows()` (Line 3741-3880)

**Fungsi Core:**
- Aggregasi data absensi per SISWA dalam range tanggal

**Step 1: Hitung Hari Unik (distinctDates)**
```php
$distinctDates = DB::table('absensi_kelas')
    ->whereBetween('tanggal', [$startDate, $endDate])
    ->distinct()->count(DB::raw('DATE(tanggal)'));
```
- **Arti:** Berapa hari ada record `absensi_kelas` dalam bulan untuk kelas tersebut
- **Penggunaan:** Jadi `total_days` untuk setiap siswa (untuk referensi persentase)

**Step 2: Aggregasi Absensi Per Siswa**
```php
SELECT
  abs_s.siswa_id,
  SUM(CASE WHEN LOWER(abs_s.status) = 'hadir' THEN 1 ELSE 0 END) as hadir_count,
  SUM(CASE WHEN LOWER(abs_s.status) IN ('terlambat','telat') THEN 1 ELSE 0 END) as terlambat_count,
  SUM(CASE WHEN LOWER(abs_s.status) = 'sakit' THEN 1 ELSE 0 END) as sakit_count,
  SUM(CASE WHEN LOWER(abs_s.status) IN ('izin','ijin') THEN 1 ELSE 0 END) as izin_count,
  SUM(CASE WHEN LOWER(abs_s.status) IN ('alpha','alpa','alfa','absen','tidak_hadir') THEN 1 ELSE 0 END) as alpa_count
FROM absensi_siswa abs_s
JOIN absensi_kelas abs_k ON abs_s.absensi_kelas_id = abs_k.id
GROUP BY abs_s.siswa_id
```

**Step 3: Dua Skenario Berbeda**

**Skenario A: Jika ada filter `kelas_id`**
```php
// Ambil semua siswa AKTIF dari kelas
$students = DB::table('siswa')
    ->where('kelas_id', $kelasId)
    ->where('status_aktif', 1)
    ->get();

// Loop semua siswa, ambil attendance dari query Step 2
foreach ($students as $s) {
    $att = $attendance->get($s->id);
    // Jika siswa tidak ada di $attendance, count semua = 0
}
```
✅ **Bagus:** Semua siswa aktif ditampilkan, bahkan yang tidak pernah absen

**Skenario B: Jika TIDAK ada filter `kelas_id`**
```php
// Hanya ambil siswa yang ada absensi dalam range
$attendanceAll = DB::table('absensi_siswa')
    ->join('siswa', 's.id', '=', 'abs_s.siswa_id')
    ->whereBetween('abs_k.tanggal', [$startDate, $endDate])
    ->get();
```
⚠️ **Masalah:** Hanya siswa yang punya absensi yang muncul, siswa baru/tidak diabsen tidak muncul

---

## ⚠️ POTENSI MASALAH LOGIKA YANG PERLU DICEK

### Issue #1: Variasi Status Tidak Konsisten
**Status yang diterima:**
- Izin: `izin`, `ijin`
- Terlambat: `terlambat`, `telat`
- Alpha: `alpha`, `alpa`, `alfa`, `absen`, `tidak_hadir` (5 VARIASI!)

**Risiko:** Jika ada typo atau data lama, bisa tidak tercatat dengan benar

### Issue #2: Total Pertemuan di `rekapBulanan()`
**Yang dihitung:**
```sql
COUNT(DISTINCT ak.id) as total_pertemuan
```
- Ini hitung TOTAL attendance records di kelas
- **Bukan** per siswa

**Contoh:**
- Kelas A, Agustus: 20 hari ada absensi
- Siswa 1 hadir 18 hari, alpha 2 hari → total attendance = 20
- Siswa 2 hadir 19 hari, sakit 1 hari → total attendance = 20
- Di summary, "total_pertemuan = 20" (benar)
- Tapi di detail, "total_days = 20" untuk semua siswa (benar juga)

✅ **Logika ini sebenarnya benar**, karena `total_pertemuan` adalah jumlah hari ada absensi kelas

### Issue #3: Siswa Baru Tidak Muncul di Summary
**Di `rekapBulanan()`:**
```sql
JOIN absensi_siswa ass ON ass.absensi_kelas_id = ak.id
```
- INNER JOIN dengan `absensi_siswa`
- Siswa yang tidak ada absensi TIDAK muncul di rekap summary

**Contoh:**
- Siswa baru masuk bulan Agustus tapi belum diabsensi
- Di rekap, siswa tersebut tidak akan tampil dalam perhitungan

⚠️ **Risiko:** Data summary bisa tidak akurat karena siswa yang belum diabsen tidak tercatat

### Issue #4: Guru Filter Tidak Include Wali Kelas
**Saat ini:**
```php
when(! $isAdminOrKepala && $user->guru_id, 
  fn ($q) => $q->where('ak.guru_id', $user->guru_id))
```
- Guru hanya bisa lihat kelas yang diajar langsung
- Tidak ada filter untuk "guru adalah wali kelas"
- Guru BK tidak bisa lihat rekap kelasnya (jika ada kolom `guru_bk_id`)

⚠️ **Risiko:** Wali kelas/Guru BK tidak bisa akses rekap kelasnya

---

## 📊 Data Flow Chart

```
┌─────────────────────────────────────────────────────────────┐
│ GET /absensi/rekap-bulanan?kelas_id=X&bulan=Y              │
└─────────────────────────────────────────────────────────────┘
                          ↓
          ┌───────────────────────────────┐
          │ Check Role & Tahun Ajaran     │
          │ (Active Tahun & Semester)     │
          └───────────────────────────────┘
                          ↓
       ┌────────────────────────────────────────┐
       │ Query: absensi_kelas + absensi_siswa   │
       │ GROUP BY: YEAR, MONTH, kelas_id        │
       │ FILTER: guru_id (jika guru biasa)      │
       └────────────────────────────────────────┘
                          ↓
           ┌──────────────────────────────┐
           │ Render Table Rekap Summary   │
           │ (Per Kelas, Per Bulan)       │
           └──────────────────────────────┘
                          ↓
              User click "Tampilkan Rekap"
                          ↓
        ┌──────────────────────────────────────┐
        │ GET /absensi/rekap-bulanan/detail    │
        │ ?kelas_id=X&bulan=Y&tahun=Z          │
        └──────────────────────────────────────┘
                          ↓
             getRangeStudentReportRows()
                          ↓
        ┌────────────────────────────────────┐
        │ 1. Hitung distinctDates            │
        │ 2. Aggregasi per siswa             │
        │ 3. Join dengan daftar siswa aktif  │
        │ 4. Return collection               │
        └────────────────────────────────────┘
                          ↓
            Render Modal/Detail PDF
```

---

## ✅ CHECKLIST CEK LOGIKA

Silakan cek poin-poin ini di database:

```sql
-- 1. Cek ada berapa siswa aktif di kelas X
SELECT COUNT(*) FROM siswa WHERE kelas_id = X AND status_aktif = 1;

-- 2. Cek ada berapa absensi_kelas di kelas X bulan Y
SELECT COUNT(DISTINCT ak.id) FROM absensi_kelas ak 
WHERE ak.kelas_id = X AND MONTH(ak.tanggal) = Y;

-- 3. Cek ada berapa absensi_siswa di kelas X bulan Y
SELECT COUNT(*) FROM absensi_siswa ass
JOIN absensi_kelas ak ON ass.absensi_kelas_id = ak.id
WHERE ak.kelas_id = X AND MONTH(ak.tanggal) = Y;

-- 4. Cek status yang ada (ada typo?)
SELECT DISTINCT LOWER(status) FROM absensi_siswa;

-- 5. Cek siswa yang tidak ada absensi di kelas X bulan Y
SELECT s.id, s.nama FROM siswa s
LEFT JOIN absensi_siswa ass ON ass.siswa_id = s.id
LEFT JOIN absensi_kelas ak ON ass.absensi_kelas_id = ak.id 
  AND ak.kelas_id = X AND MONTH(ak.tanggal) = Y
WHERE s.kelas_id = X AND s.status_aktif = 1 AND ass.id IS NULL;
```

---

**Siap untuk debugging lebih lanjut atau modifikasi logika?**
