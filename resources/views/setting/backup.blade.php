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

    <div id="backupProgressPanel" class="alert alert-info d-none" role="status" aria-live="polite">
        <div class="d-flex align-items-center gap-2">
            <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>
            <div>
                <strong>Memproses backup...</strong>
                <div id="backupProgressText">Harap tunggu sebentar.</div>
            </div>
        </div>
        <div class="progress mt-2" style="height: 8px;">
            <div id="backupProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 20%" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100"></div>
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

    <div class="card mb-3">
        <div class="card-body">
            <h5>Daftar Backup Database</h5>
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

    <div class="card mb-3">
        <div class="card-body">
            <h5>Backup Foto Profil</h5>
            <form method="POST" action="{{ route('setting.backup.profile.export') }}" class="mb-3 backup-submit-form" data-progress-label="Sedang mengekspor foto profil..." data-ajax="true" onsubmit="return submitBackupForm(this)">
                @csrf
                <button type="submit" class="btn btn-outline-success">Export Foto Profil ke ZIP</button>
            </form>

            <form method="POST" action="{{ route('setting.backup.profile.import') }}" enctype="multipart/form-data" class="mb-3 backup-submit-form" data-progress-label="Sedang mengunggah dan mengimpor foto profil..." data-ajax="true" onsubmit="return submitBackupForm(this)">
                @csrf
                <div class="mb-2">
                    <label class="form-label">Import Foto Profil dari ZIP</label>
                    <input type="file" name="profile_photo_backup" class="form-control" accept=".zip" required>
                </div>
                <button type="submit" class="btn btn-outline-primary">Import Foto Profil</button>
            </form>

            <table class="table">
                <thead><tr><th>Nama</th><th>Ukuran</th><th>Terakhir</th><th>Aksi</th></tr></thead>
                <tbody>
                @forelse($photoBackups as $b)
                    <tr>
                        <td>{{ $b['name'] }}</td>
                        <td>{{ number_format($b['size']/1024,2) }} KB</td>
                        <td>{{ date('Y-m-d H:i:s', $b['modified']) }}</td>
                        <td>
                            <a href="{{ route('setting.backup.profile.download', $b['name']) }}" class="btn btn-sm btn-primary">Download</a>
                            <form action="{{ route('setting.backup.profile.delete', $b['name']) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Hapus backup foto profil?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">Belum ada backup foto profil</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('js')
<script>
    function submitBackupForm(form) {
        if (form.dataset.ajax !== 'true') {
            return true;
        }

        const panel = document.getElementById('backupProgressPanel');
        const bar = document.getElementById('backupProgressBar');
        const text = document.getElementById('backupProgressText');
        const label = form.dataset.progressLabel || 'Memproses data...';
        const button = form.querySelector('button[type="submit"]');

        panel.classList.remove('d-none');
        panel.classList.remove('alert-success', 'alert-danger');
        panel.classList.add('alert-info');
        text.textContent = label;
        bar.style.width = '10%';
        bar.setAttribute('aria-valuenow', '10');

        if (button) {
            button.disabled = true;
            button.dataset.originalText = button.textContent;
            button.textContent = 'Memproses...';
        }

        const xhr = new XMLHttpRequest();
        xhr.open('POST', form.action, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('X-CSRF-TOKEN', form.querySelector('input[name="_token"]')?.value || '');

        xhr.upload.addEventListener('progress', function (e) {
            if (!e.lengthComputable) {
                text.textContent = label + ' (mengunggah...)';
                return;
            }
            const percent = Math.round((e.loaded / e.total) * 100);
            bar.style.width = percent + '%';
            bar.setAttribute('aria-valuenow', percent);
            text.textContent = label + ' (' + percent + '%)';
        });

        xhr.addEventListener('load', function () {
            if (xhr.status >= 200 && xhr.status < 300) {
                panel.classList.remove('alert-info');
                panel.classList.add('alert-success');
                text.textContent = 'Selesai. Memuat ulang halaman...';
                bar.style.width = '100%';
                bar.setAttribute('aria-valuenow', '100');
                setTimeout(function () {
                    window.location.reload();
                }, 800);
            } else {
                panel.classList.remove('alert-info');
                panel.classList.add('alert-danger');
                text.textContent = 'Proses gagal. Silakan coba lagi.';
                if (button) {
                    button.disabled = false;
                    button.textContent = button.dataset.originalText || button.textContent;
                }
            }
        });

        xhr.addEventListener('error', function () {
            panel.classList.remove('alert-info');
            panel.classList.add('alert-danger');
            text.textContent = 'Gagal menghubungi server. Silakan coba lagi.';
            if (button) {
                button.disabled = false;
                button.textContent = button.dataset.originalText || button.textContent;
            }
        });

        xhr.send(new FormData(form));
        return false;
    }
</script>
@endpush
