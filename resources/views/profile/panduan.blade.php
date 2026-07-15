@extends('layouts.app')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Panduan Penggunaan</h2>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Panduan Penggunaan</li>
                </ol>
            </div>
            @if($user->foto && \Storage::disk('public')->exists($user->foto))
                <div class="col-auto">
                    <div class="avatar avatar-xl" style="background-image: url({{ asset('storage/'.$user->foto) }}); background-size: cover; background-position: center; border-radius: 50%; width: 3rem; height: 3rem;"></div>
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 mb-3">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Isi</h3>
                </div>
                <div class="list-group list-group-flush list-group-transparent">
                    @if(in_array('Admin', $roles) || in_array('Super Admin', $roles))
                    <a href="#role-admin" class="list-group-item list-group-item-action d-flex align-items-center active">
                        <span class="avatar avatar-xs me-3" style="background:#dc2626;color:#fff;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:.7rem;">A</span>
                        Administrator
                    </a>
                    @endif
                    @if(in_array('Kepala Sekolah', $roles) || in_array('Wakil Kepala Sekolah', $roles))
                    <a href="#role-kepsek" class="list-group-item list-group-item-action d-flex align-items-center">
                        <span class="avatar avatar-xs me-3" style="background:#2563eb;color:#fff;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:.7rem;">K</span>
                        Kepala / Wakil Kepala Sekolah
                    </a>
                    @endif
                    @if(in_array('Guru', $roles) || in_array('Guru Mapel', $roles) || in_array('Guru Kelas', $roles) || in_array('Wali Kelas', $roles))
                    <a href="#role-guru" class="list-group-item list-group-item-action d-flex align-items-center">
                        <span class="avatar avatar-xs me-3" style="background:#16a34a;color:#fff;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:.7rem;">G</span>
                        Guru / Wali Kelas
                    </a>
                    @endif
                    @if(in_array('Siswa', $roles))
                    <a href="#role-siswa" class="list-group-item list-group-item-action d-flex align-items-center">
                        <span class="avatar avatar-xs me-3" style="background:#9333ea;color:#fff;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:.7rem;">S</span>
                        Siswa
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            @if(in_array('Admin', $roles) || in_array('Super Admin', $roles))
            <div class="card mb-3" id="role-admin">
                <div class="card-header">
                    <h3 class="card-title">Administrator</h3>
                </div>
                <div class="card-body">
                    <div class="timeline timeline-activity timeline-sm">
                        <div class="timeline-item">
                            <div class="timeline-badge bg-red-lt"></div>
                            <div class="timeline-time">Login</div>
                            <div class="timeline-title">Masuk ke sistem</div>
                            <div class="timeline-text">Gunakan akun yang diberikan. Pastikan identitas sesuai.</div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-badge bg-blue-lt"></div>
                            <div class="timeline-time">Dashboard</div>
                            <div class="timeline-title">Lihat ringkasan</div>
                            <div class="timeline-text">Monitor statistik kehadiran siswa dan guru secara real-time.</div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-badge bg-azure-lt"></div>
                            <div class="timeline-time">Data Master</div>
                            <div class="timeline-title">Kelola data inti</div>
                            <div class="timeline-text">Tambah, edit, dan hapus data Siswa, Guru, Kelas, dan Mata Pelajaran. Import/Export data melalui template.</div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-badge bg-green-lt"></div>
                            <div class="timeline-time">Laporan</div>
                            <div class="timeline-title">Cetak & ekspor</div>
                            <div class="timeline-text">Generate laporan kehadiran, nilai, dan pelanggaran dalam format PDF atau Excel.</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if(in_array('Kepala Sekolah', $roles) || in_array('Wakil Kepala Sekolah', $roles))
            <div class="card mb-3" id="role-kepsek">
                <div class="card-header">
                    <h3 class="card-title">Kepala / Wakil Kepala Sekolah</h3>
                </div>
                <div class="card-body">
                    <div class="timeline timeline-activity timeline-sm">
                        <div class="timeline-item">
                            <div class="timeline-badge bg-red-lt"></div>
                            <div class="timeline-time">Login</div>
                            <div class="timeline-title">Masuk ke sistem</div>
                            <div class="timeline-text">Gunakan akun resmi sekolah.</div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-badge bg-blue-lt"></div>
                            <div class="timeline-time">Dashboard</div>
                            <div class="timeline-title">Lihat ringkasan</div>
                            <div class="timeline-text">Pantau statistik kehadiran dan performa sekolah.</div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-badge bg-azure-lt"></div>
                            <div class="timeline-time">Laporan Akademik</div>
                            <div class="timeline-title">Review rekap</div>
                            <div class="timeline-text">Lihat rekap nilai, kehadiran, dan pelanggaran. Export PDF untuk归档.</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if(in_array('Guru', $roles) || in_array('Guru Mapel', $roles) || in_array('Guru Kelas', $roles) || in_array('Wali Kelas', $roles))
            <div class="card mb-3" id="role-guru">
                <div class="card-header">
                    <h3 class="card-title">Guru / Wali Kelas</h3>
                </div>
                <div class="card-body">
                    <div class="timeline timeline-activity timeline-sm">
                        <div class="timeline-item">
                            <div class="timeline-badge bg-red-lt"></div>
                            <div class="timeline-time">Login</div>
                            <div class="timeline-title">Masuk ke sistem</div>
                            <div class="timeline-text">Gunakan akun yang diberikan. Pastikan identitas sesuai.</div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-badge bg-blue-lt"></div>
                            <div class="timeline-time">Jadwal & Absensi</div>
                            <div class="timeline-title">Input kehadiran</div>
                            <div class="timeline-text">Lihat jadwal KBM, input absensi siswa (Hadir, Sakit, Izin, Alpa) dan cetak laporan harian.</div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-badge bg-azure-lt"></div>
                            <div class="timeline-time">Nilai</div>
                            <div class="timeline-title">Input & rekap nilai</div>
                            <div class="timeline-text">Input nilai siswa per submit, cetak rekap nilai kelas, dan lihat per siswa.</div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-badge bg-green-lt"></div>
                            <div class="timeline-time">Laporan</div>
                            <div class="timeline-title">Cetak dokumen</div>
                            <div class="timeline-text">Generate dan cetak laporan kehadiran, nilai, dan rekap siswa.</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if(in_array('Siswa', $roles))
            <div class="card mb-3" id="role-siswa">
                <div class="card-header">
                    <h3 class="card-title">Siswa</h3>
                </div>
                <div class="card-body">
                    <div class="timeline timeline-activity timeline-sm">
                        <div class="timeline-item">
                            <div class="timeline-badge bg-red-lt"></div>
                            <div class="timeline-time">Login</div>
                            <div class="timeline-title">Masuk ke sistem</div>
                            <div class="timeline-text">Gunakan akun NIS dan password yang diberikan.</div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-badge bg-blue-lt"></div>
                            <div class="timeline-time">Dashboard</div>
                            <div class="timeline-title">Lihat ringkasan</div>
                            <div class="timeline-text">Cek persentase kehadiran, jadwal pelajaran, dan info penting lainnya.</div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-badge bg-azure-lt"></div>
                            <div class="timeline-time">Data Akademik</div>
                            <div class="timeline-title">Lihat nilai</div>
                            <div class="timeline-text">Lihat rekap nilai per mapel dan nilai setiap submit.</div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-badge bg-green-lt"></div>
                            <div class="timeline-time">Profil</div>
                            <div class="timeline-title">Kelola akun</div>
                            <div class="timeline-text">Ubah data diri, foto profil, dan password.</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Keterangan Badge Status</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success">Hadir</span>
                                <span class="text-muted small">Siswa hadir ke sekolah</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-warning">Sakit</span>
                                <span class="text-muted small">Siswa sedang sakit</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-info">Izin</span>
                                <span class="text-muted small">Siswa izin dengan surat</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-danger">Alpa</span>
                                <span class="text-muted small">Siswa tidak hadir tanpa keterangan</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
