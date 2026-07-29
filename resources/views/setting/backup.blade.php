@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3>Backup Database</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="POST" action="{{ route('setting.backup.settings') }}">
                @csrf
                <div class="mb-3 form-check">
                    <input type="hidden" name="enabled" value="0">
                    <input type="checkbox" class="form-check-input" id="backup_enabled" name="enabled" value="1" {{ data_get($backupSettings,'backup.enabled',false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="backup_enabled">Aktifkan backup otomatis</label>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <label>Waktu (HH:MM)</label>
                        <input type="time" name="time" class="form-control" value="{{ data_get($backupSettings,'backup.time','02:00') }}">
                    </div>
                    <div class="col-md-3">
                        <label>Format</label>
                        <select name="format" class="form-control">
                            <option value="zip" {{ data_get($backupSettings,'backup.format','zip') === 'zip' ? 'selected' : '' }}>ZIP (.zip)</option>
                            <option value="sql" {{ data_get($backupSettings,'backup.format','zip') === 'sql' ? 'selected' : '' }}>SQL (.sql)</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <button class="btn btn-primary">Simpan Pengaturan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h5>Manual Backup</h5>
            <form method="POST" action="{{ route('setting.backup.manual') }}" class="d-flex gap-2">
                @csrf
                <input type="hidden" name="download" value="1">
                <select name="format" class="form-control w-auto">
                    <option value="sql">SQL</option>
                    <option value="zip" selected>ZIP</option>
                </select>
                <button class="btn btn-success">Buat & Unduh Backup Sekarang</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5>Daftar Backup</h5>
            <table class="table">
                <thead><tr><th>Nama</th><th>Ukuran</th><th>Terakhir</th><th>Aksi</th></tr></thead>
                <tbody>
                @forelse($backups as $b)
                    <tr>
                        <td>{{ $b['name'] }}</td>
                        <td>{{ number_format($b['size']/1024,2) }} KB</td>
                        <td>{{ date('Y-m-d H:i:s', $b['modified']) }}</td>
                        <td>
                            <a href="{{ route('setting.backup.download', $b['name']) }}" class="btn btn-sm btn-primary">Download</a>
                            <form action="{{ route('setting.backup.delete', $b['name']) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Hapus backup?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">Belum ada backup</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
