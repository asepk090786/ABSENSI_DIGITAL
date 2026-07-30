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
