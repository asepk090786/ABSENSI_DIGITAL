# USE Case Diagram - Aplikasi Absensi Digital SMAN 1 Pontang

## Actors

| Actor | Description |
|-------|-------------|
| **Admin** | Administrator dengan akses penuh |
| **Guru** | Guru - input absensi, nilai, kelola agenda |
| **Siswa** | Siswa - lihat nilai dan jadwal |
| **Wali Kelas** | Guru wali kelas - kelola kelas, lihat laporan |
| **Kepala Sekolah** | Kepala sekolah - laporan dan pengaturan |
| **Wakil Kepala Sekolah** | Wakil kepala - laporan |
| **Guru BK** | Bimbingan konseling - layanan siswa |

## Use Cases

```
                    +------------------+
                    |     Login        |
                    +------------------+
                           |
                           v
                    +------------------+
                    |   Dashboard      |
                    +------------------+
                           |
         +-----------------+-----------------+-------+
         |        |           |        |            |
         v        v           v        v            v
    +-------+  +------+  +-------+  +------+  +----------+
    |Profile|  |Absensi|  |Nilai  |  |Jadwal|  |Settings  |
    +-------+  +------+  +-------+  +------+  +----------+
```

## Actor - Use Case Relationships

### Admin
```
Admin --> [Login]
Admin --> [Dashboard]
Admin --> [Profile]
Admin --> [Manage Absensi]
Admin --> [Manage Nilai]
Admin --> [Manage Jadwal KBM]
Admin --> [Manage Guru]
Admin --> [Manage Siswa]
Admin --> [Manage Kelas]
Admin --> [Manage Mata Pelajaran]
Admin --> [Manage Ekstrakurikuler]
Admin --> [Manage Tahun Ajaran]
Admin --> [Manage Semester]
Admin --> [Manage Users]
Admin --> [Manage Settings]
```

### Guru
```
Guru --> [Login]
Guru --> [Dashboard]
Guru --> [Profile]
Guru --> [Input Absensi]
Guru --> [View Absensi]
Guru --> [Generate Absensi]
Guru --> [Report Absensi Siswa]
Guru --> [Report Absensi Guru]
Guru --> [Input Nilai]
Guru --> [View Nilai]
Guru --> [Import Nilai]
Guru --> [Export Nilai]
Guru --> [View Jadwal KBM]
Guru --> [Manage Agenda Guru]
Guru --> [Manage Rencana Pembelajaran]
```

### Siswa
```
Siswa --> [Login]
Siswa --> [Dashboard]
Siswa --> [Profile]
Siswa --> [View Nilai]
Siswa --> [View Jadwal KBM]
```

### Wali Kelas
```
WaliKelas --> [Login]
WaliKelas --> [Dashboard]
WaliKelas --> [Profile]
WaliKelas --> [View Absensi]
WaliKelas --> [View Nilai]
WaliKelas --> [Report Nilai]
WaliKelas --> [Report Absensi Guru]
WaliKelas --> [Manage Agenda Kelas]
```

### Kepala Sekolah
```
KepalaSekolah --> [Login]
KepalaSekolah --> [Dashboard]
KepalaSekolah --> [Profile]
KepalaSekolah --> [View Jadwal KBM]
KepalaSekolah --> [Report Absensi Guru]
KepalaSekolah --> [Manage Settings]
```

### Wakil Kepala Sekolah
```
WakilKepalaSekolah --> [Login]
WakilKepalaSekolah --> [Dashboard]
WakilKepalaSekolah --> [Profile]
WakilKepalaSekolah --> [View Jadwal KBM]
WakilKepalaSekolah --> [Report Absensi Guru]
```

### Guru BK
```
GuruBK --> [Login]
GuruBK --> [Dashboard]
GuruBK --> [Profile]
GuruBK --> [Manage Layanan BK]
GuruBK --> [Manage Pembinaan BK]
GuruBK --> [Cetak Kartu Kendali]
GuruBK --> [Manage Tindak Lanjut]
```

## PlantUML

Source file: `docs/usecase-diagram.puml`

Untuk generate diagram:
1. Instal PlantUML: `npm install -g @plantumltools/cli`
2. Jalankan: `plantuml docs/usecase-diagram.puml`

## Mermaid Diagram

```mermaid
graph TD
    subgraph "Authentication"
        A[Login] --> B[Dashboard]
        B --> C[Profile]
    end

    subgraph "Admin"
        A1[Manage Users]
        A2[Manage Data Master]
        A3[Manage Settings]
        Admin[A] --> A1 & A2 & A3
    end

    subgraph "Guru"
        G1[Input Absensi]
        G2[View/Generate Absensi]
        G3[Input Nilai]
        G4[Manage Agenda]
        Guru[G] --> G1 & G2 & G3 & G4
    end

    subgraph "Siswa"
        S1[View Nilai]
        S2[View Jadwal]
        Siswa[S] --> S1 & S2
    end

    subgraph "Wali Kelas"
        WK1[View Absensi]
        WK2[Report Nilai]
        WaliK[WaliK] --> WK1 & WK2
    end

    subgraph "Kepala Sekolah"
        KS1[Report Absensi]
        Kepala[K] --> KS1
    end

    subgraph "Guru BK"
        BK1[Manage Layanan BK]
        BK2[Kartu Kendali]
        GuruBK[BK] --> BK1 & BK2
    end
```