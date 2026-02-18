# Dokumentasi Integrasi Agenda Guru, Agenda Kelas, dan Absensi

## 📋 Overview

Sistem ini mengintegrasikan tiga komponen utama:
1. **Agenda Guru** - Jurnal harian mengajar guru
2. **Agenda Kelas** - Detail kegiatan per kelas
3. **Absensi Siswa** - Catatan kehadiran siswa

Semuanya terhubung satu sama lain untuk memberikan gambaran lengkap aktivitas guru setiap hari.

---

## 🔄 Alur Integrasi

### 1. Guru Input Agenda Kelas
```
"Guru membuat agenda kelas"
    ↓
[AgendaKelasController::store()]
    ├── Create agenda_kelas record
    ├── Call: syncAgendaGuru($agendaKelas)
    └── Auto-create/update agenda_guru entry
```

**Apa yang terjadi:**
- Guru input: Tanggal, Jam, Kelas, Kegiatan (materi ajar, tujuan, strategi, dll)
- Sistem otomatis membuat entry di `agenda_guru` dengan format: `Nama Kelas - Kegiatan`
- Jika sudah ada agenda guru untuk jam/tanggal yg sama, kegiatan di-append

### 2. Guru Input Absensi Siswa
```
"Guru membuat absensi untuk kelas"
    ↓
[AbsensiController::store()]
    ├── Create absensi_kelas record
    ├── Create absensi_siswa records (per siswa)
    ├── Call: syncAbsensiToAgendaGuru($absensi)
    └── Call: updateAgendaGuruAttendanceNote($absensi)
```

**Apa yang terjadi:**
- Guru input: Kelas, Tanggal, Jam, Status per siswa (Hadir/Absen/Izin/Sakit)
- Sistem otomatis membuat entry di `agenda_guru` untuk tracking
- Catatan absensi ditambahkan ke agenda guru dalam format: `(Hadir: X, Absen: Y, Izin: Z, Sakit: W)`

### 3. Lihat Agenda Guru dengan Status Kehadiran
```
"Guru akses Agenda Guru > Jurnal Harian"
    ↓
[AgendaGuruController::index()]
    ├── Query agenda_guru records
    └── For each agenda: call $agendaGuru->getAbsensiSummary()
        └── Returns: total, hadir, absen, izin, sakit
```

**Yang ditampilkan:**
- List agenda guru dengan tanggal, jam, kegiatan
- Badge ringkas kehadiran: `Hadir: 30/35` (jika ada absensi)

---

## 📊 Data Structure

### Agenda Guru Table
```
agenda_guru
├── id
├── guru_id
├── jam_belajar_id
├── tanggal
├── kegiatan (text - bisa berisi ringkasan dari multiple kelas + absensi)
├── tahun_ajaran_id
├── semester_id
├── created_at
└── updated_at
```

### Agenda Kelas Table
```
agenda_kelas
├── id
├── kelas_id
├── guru_id
├── jam_belajar_id
├── tanggal
├── kegiatan (text)
├── tujuan_pembelajaran
├── strategi_pembelajaran
├── media_pembelajaran
├── sumber_belajar
├── penilaian
├── catatan_tambahan
├── tahun_ajaran_id
├── semester_id
├── created_at
└── updated_at
```

### Absensi Kelas Table
```
absensi_kelas
├── id
├── kelas_id
├── guru_id
├── jam_belajar_id
├── tanggal
├── status_kelas
├── tahun_ajaran_id
├── semester_id
├── created_at
└── updated_at
```

### Absensi Siswa Table
```
absensi_siswa
├── id
├── absensi_kelas_id
├── siswa_id
├── status (Hadir/Absen/Izin/Sakit)
├── keterangan
├── created_at
└── updated_at
```

---

## 🔧 Functions & Methods

### AgendaGuru Model

#### `getAbsensiSummary()`
Menghitung ringkasan kehadiran untuk agenda guru tertentu.

```php
$agendaGuru = AgendaGuru::find(1);
$summary = $agendaGuru->getAbsensiSummary();

// Returns:
// [
//     'total' => 35,
//     'hadir' => 30,
//     'absen' => 3,
//     'izin' => 2,
//     'sakit' => 0
// ]
```

#### `absensiKelas()`
Query builder untuk semua absensi kelas yang terkait dengan agenda guru.

```php
$absensiRecords = $agendaGuru->absensiKelas()->get();
```

### AgendaKelasController

#### `syncAgendaGuru(AgendaKelas $agendaKelas)`
Private function untuk sync agenda kelas ke agenda guru. Dipanggil otomatis saat:
- Create agenda kelas
- Update agenda kelas

```php
// Membuat atau update agenda_guru dengan:
// - guru_id, jam_belajar_id, tanggal, tahun_ajaran_id, semester_id (sama)
// - kegiatan: "Nama Kelas - Kegiatan"
```

#### `cleanupAgendaGuru(AgendaKelas $deletedAgenda)`
Private function untuk remove agenda guru jika semua agenda kelas untuk jam itu sudah dihapus.

### AbsensiController

#### `syncAbsensiToAgendaGuru(AbsensiKelas $absensi)`
Membuat agenda guru entry untuk absensi yang baru dibuat (jika belum ada).

#### `updateAgendaGuruAttendanceNote(AbsensiKelas $absensi)`
Update catatan kehadiran di agenda guru dengan summary absensi siswa.

---

## 📝 Contoh Skenario

### Skenario 1: Input Agenda + Absensi Kelas X

**Step 1: Guru input Agenda Kelas**
- Tanggal: 17 Feb 2026
- Jam: 10.00-11.00 (Jam ke-3)
- Kelas: X-A
- Kegiatan: "Pelajaran Sistem Persamaan Linear"

**Hasil:** 
- agenda_kelas: 1 record tercipta
- agenda_guru: auto-create dengan kegiatan = "X-A - Pelajaran Sistem Persamaan Linear"

**Step 2: Guru input Absensi untuk jam yang sama**
- Tanggal: 17 Feb 2026
- Jam: 10.00-11.00
- Kelas: X-A
- Absensi: 30 hadir, 3 absen, 2 izin, 0 sakit

**Hasil:**
- absensi_kelas: 1 record dengan status kehadiran
- absensi_siswa: 35 records (1 per siswa)
- agenda_guru: update kegiatan = "X-A - Pelajaran Sistem Persamaan Linear (Hadir: 30, Absen: 3, Izin: 2, Sakit: 0)"

**Step 3: Guru lihat Agenda Guru > Jurnal Harian**
- Buka menu Pembelajaran → Agenda Guru
- Pilih bulan Februari 2026
- Lihat entry untuk 17 Feb jam 10.00-11.00
- Badge menunjukkan: "Hadir: 30/35"

---

### Skenario 2: Multiple Kelas Satu Jam

**Step 1: Guru input Agenda Kelas untuk 2 kelas**
- Kelas X-A jam 10.00-11.00: "Pelajaran SPLDV"
- Kelas X-B jam 10.00-11.00: "Pelajaran Determinan Matriks"

**Hasil:**
- agenda_kelas: 2 records
- agenda_guru: 1 combined entry dengan kegiatan:
  ```
  X-A - Pelajaran SPLDV
  X-B - Pelajaran Determinan Matriks
  ```

**Step 2: Input Absensi untuk kedua kelas**
- Absensi X-A: 30/35
- Absensi X-B: 32/32

**Hasilnya:**
- agenda_guru kegiatan diupdate menjadi:
  ```
  X-A - Pelajaran SPLDV (Hadir: 30, Absen: 3, Izin: 2)
  X-B - Pelajaran Determinan Matriks (Hadir: 32, Absen: 0, Izin: 0)
  ```

---

## 🎯 User Flow

### Guru Workflow

```
1. Login as Guru
   ↓
2. Pilih "Pembelajaran" → "Agenda Kelas"
   └─ Input agenda untuk kelas yang diajar
   └─ System auto-sync ke Agenda Guru
   ↓
3. Pilih "Pembelajaran" → "Absensi"
   └─ Input kehadiran siswa
   └─ System auto-create agenda guru entry + update catatan
   ↓
4. Pilih "Pembelajaran" → "Agenda Guru"
   └─ Lihat jurnal harian dengan ringkasan kehadiran
   └─ Export PDF untuk arsip
```

### Admin/Kepala Sekolah Workflow

```
1. Login as Admin or Kepala Sekolah
   ↓
2. Pilih "Pembelajaran" → "Agenda Guru"
   └─ Filter by guru, bulan, tahun
   └─ Lihat aktivitas mengajar semua guru
   ↓
3. Lihat ringkasan kehadiran per jam mengajar
```

---

## 🔐 Authorization

- **Guru**: Dapat input/edit/delete agenda kelas dan absensi untuk jadwal mereka sendiri. Agenda guru otomatis tersync.
- **Wali Kelas**: Dapat edit data siswa kelas mereka (sudah diimplementasikan sebelumnya)
- **Admin/Kepala Sekolah**: Dapat akses semua agenda guru untuk monitoring

---

## 📊 Query Examples

### Get agenda guru dengan attendance summary
```php
$agendaGuru = AgendaGuru::where('guru_id', 1)
    ->where('tanggal', '2026-02-17')
    ->get();

foreach ($agendaGuru as $agenda) {
    $summary = $agenda->getAbsensiSummary();
    dd($summary);
}
```

### Get all attendance records linked to agenda guru
```php
$agendaGuru = AgendaGuru::find(1);
$absensiRecords = $agendaGuru->absensiKelas()->get();
```

### Find agenda guru by multiple criteria
```php
$agenda = AgendaGuru::where('guru_id', 5)
    ->where('jam_belajar_id', 3)
    ->where('tanggal', '2026-02-17')
    ->where('tahun_ajaran_id', 1)
    ->where('semester_id', 2)
    ->first();
```

---

## ✅ Fitur yang Sudah Diimplementasikan

- ✅ Agenda Guru (jurnal harian)
- ✅ Auto-sync agenda kelas ke agenda guru
- ✅ Auto-sync absensi siswa ke agenda guru
- ✅ Summary kehadiran di agenda guru UI
- ✅ Export PDF agenda guru dengan format jurnal
- ✅ Filter agenda guru by bulan/tahun
- ✅ Authorization checks

---

## 🚀 Cara Testing

1. Login as Guru
2. Buka "Pembelajaran" → "Agenda Kelas"
3. Klik "+ Tambah Agenda" dan input data
4. Buka "Pembelajaran" → "Absensi" dan input kehadiran
5. Buka "Pembelajaran" → "Agenda Guru"
6. Pastikan agenda guru muncul dengan ringkasan kehadiran

---

## 📞 Troubleshooting

**Problem**: Agenda guru tidak muncul setelah input agenda kelas
- **Solusi**: Pastikan tahun ajaran dan semester sudah di-set aktif

**Problem**: Kehadiran tidak terupdate di agenda guru
- **Solusi**: Pastikan absensi input untuk kelas dan jam yang sama dengan agenda kelas

**Problem**: Summary kehadiran menunjukkan angka 0
- **Solusi**: Cek bahwa absensi sudah disimpan (bukan hanya draft)

---

## 📌 Notes

- Agenda guru dirancang sebagai "single source of truth" untuk aktivitas guru setiap hari
- Semua perubahan di agenda kelas dan absensi otomatis terupdate di agenda guru
- Data absensi di-track melalui relasi dengan agenda guru tanpa menambah kolom baru (efisien)
- Export PDF agenda guru menggunakan format jurnal resmi sesuai dengan gambar yang Anda berikan

---

**Last Updated**: 17 Feb 2026
