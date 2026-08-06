@extends('layouts.app', ['pageSlug' => 'setting-about'])

@section('title','About')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">About</h3>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-3 text-center mb-3 mb-md-0">
                    <img src="{{ $appInfo['logo'] }}" alt="Logo SIMADIS" class="img-fluid" style="max-height: 120px;">
                </div>
                <div class="col-md-9">
                    <h4 class="mb-2">{{ $appInfo['name'] }}</h4>
                    <p class="mb-3">{{ $appInfo['description'] }}</p>
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr><th style="width:35%">Nama Sistem</th><td>{{ $appInfo['name'] }}</td></tr>
                            <tr><th>Versi SIMADIS</th><td>{{ $appInfo['version'] }}</td></tr>
                    <tr><th>Release Tahun</th><td>{{ date('Y') }}</td></tr>
                            <tr><th>Repository</th><td><a href="{{ $appInfo['repository'] }}" target="_blank" rel="noopener">{{ $appInfo['repository'] }}</a></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <strong>Informasi Web SIMADIS</strong>
        </div>
        <div class="card-body">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th style="width:35%">Item</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><th>Fungsi Utama</th><td>Absensi digital, jadwal belajar, data siswa/guru, dan pelaporan sekolah.</td></tr>
                    <tr><th>Platform</th><td>Laravel + Bootstrap + database MySQL/MariaDB.</td></tr>
                    <tr><th>Tujuan</th><td>Membantu sekolah mengelola proses pembelajaran dan administrasi secara cepat dan terintegrasi.</td></tr>
                    <tr><th>Hak Akses</th><td>Admin, Kepala Sekolah, Guru, Wali Kelas, dan pengguna terkait sesuai peran.</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <strong>Cek Update dari GitHub</strong>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <form action="{{ route('setting.about.check_update') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-refresh"></i> Cek Update
                    </button>
                </form>
                <form action="{{ route('setting.about.update') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success" {{ $appInfo['update_available'] ? '' : 'disabled' }}>
                        <i class="ti ti-download"></i> Update dari GitHub
                    </button>
                </form>
                <form action="{{ route('setting.about.bump_from_git') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary">
                        <i class="ti ti-git-branch"></i> Sinkronkan Versi dari Git
                    </button>
                </form>
            </div>
            <table class="table table-sm mb-0">
                <tbody>
                    <tr><th style="width:35%">Versi Terpasang</th><td>{{ $appInfo['version'] }}</td></tr>
                    <tr><th>Format Versi</th><td>Ver.Major.Minor.Patch, misalnya Ver.1.0.26 untuk tahun 2026 dan patch ke-26. Jika ada release baru di tahun berikutnya, format akan menjadi Ver.2.0.1.</td></tr>
                    <tr><th>Commit Lokal</th><td>{{ $appInfo['current_commit'] }}</td></tr>
                    <tr><th>Commit Remote</th><td>{{ $appInfo['remote_commit'] }}</td></tr>
                    <tr><th>Status</th><td>@if($appInfo['update_available'])<span class="badge bg-warning text-white">Update tersedia</span>@else<span class="badge bg-success text-white">Terupdate</span>@endif</td></tr>
                    <tr><th>Pesan</th><td>{{ $appInfo['update_message'] }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <strong>What’s New</strong>
        </div>
        <div class="card-body">
            @php($latest = $appInfo['whats_new'] ?? [])
            @if(!empty($latest))
                <div class="alert alert-info mb-3">
                    <div class="fw-bold">{{ $latest['version'] ?? '-' }}</div>
                    <div class="small text-muted">{{ $latest['date'] ?? '-' }} • {{ $latest['source'] ?? '-' }}</div>
                    <div class="mt-2">{{ $latest['notes'] ?? '-' }}</div>
                </div>
            @else
                <div class="text-muted">Belum ada informasi perubahan terbaru.</div>
            @endif
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <strong>Riwayat Versi</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Versi</th>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appInfo['history'] as $entry)
                            <tr>
                                <td>{{ $entry['version'] ?? '-' }}</td>
                                <td>{{ $entry['date'] ?? '-' }}</td>
                                <td>{{ $entry['notes'] ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted">Belum ada riwayat versi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <strong>Spesifikasi Server</strong>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr><th style="width:45%">Sistem Operasi</th><td>{{ $server['os'] }}</td></tr>
                            <tr><th>Versi PHP</th><td>{{ $server['php_version'] }}</td></tr>
                            <tr><th>Web Server</th><td>{{ $server['server_software'] }}</td></tr>
                            <tr><th>Versi Laravel</th><td>{{ $server['laravel_version'] }}</td></tr>
                            <tr><th>Database</th><td>{{ strtoupper($server['database_driver']) }} @if($server['database_version']) - {{ $server['database_version'] }} @endif</td></tr>
                            <tr><th>Zona Waktu</th><td>{{ $server['timezone'] }}</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr><th style="width:45%">Memory Limit</th><td>{{ $server['memory_limit'] }}</td></tr>
                            <tr><th>Max Execution Time</th><td>{{ $server['max_execution_time'] }}</td></tr>
                            <tr><th>Upload Max Filesize</th><td>{{ $server['upload_max_filesize'] }}</td></tr>
                            <tr><th>Post Max Size</th><td>{{ $server['post_max_size'] }}</td></tr>
                            <tr><th>Disk Free / Total</th><td>{{ $server['disk_free'] }} / {{ $server['disk_total'] }}</td></tr>
                            <tr><th>Waktu Server</th><td>{{ $server['server_time'] }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <strong>Library / Plugin PHP (Composer)</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Library</th>
                            <th>Versi Constraint</th>
                            <th>Versi Terinstall</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($phpLibraries as $i => $lib)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $lib['name'] }}</td>
                                <td>{{ $lib['constraint'] }}</td>
                                <td>{{ $lib['installed_version'] }}</td>
                                <td>
                                    @if($lib['installed'])
                                        <span class="badge bg-success">Terinstall</span>
                                    @else
                                        <span class="badge bg-danger">Belum Terinstall</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!$lib['installed'])
                                        <form action="{{ route('setting.about.install', 'composer') }}" method="POST" class="d-inline" onsubmit="return confirm('Jalankan composer install untuk melengkapi dependency PHP yang belum terinstall?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="ti ti-download"></i> Install
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">Tidak ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <strong>Library / Plugin JavaScript (NPM)</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Library</th>
                            <th>Versi Constraint</th>
                            <th>Versi Terinstall</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jsLibraries as $i => $lib)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $lib['name'] }}</td>
                                <td>{{ $lib['constraint'] }}</td>
                                <td>{{ $lib['installed_version'] }}</td>
                                <td>
                                    @if($lib['installed'])
                                        <span class="badge bg-success">Terinstall</span>
                                    @else
                                        <span class="badge bg-danger">Belum Terinstall</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!$lib['installed'])
                                        <form action="{{ route('setting.about.install', 'npm') }}" method="POST" class="d-inline" onsubmit="return confirm('Jalankan npm install untuk melengkapi dependency JS yang belum terinstall?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="ti ti-download"></i> Install
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">Tidak ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
