@extends('layouts.app', ['pageSlug' => 'ekskul'])

@section('title', 'Absensi - ' . $ekskul->nama)

@section('content')
<style>
.grid-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 1rem;
}
.grid-card {
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 1rem;
    text-align: center;
    background: #fff;
    transition: box-shadow 0.15s;
}
.grid-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.grid-card .avatar {
    width: 60px; height: 60px;
    border-radius: 50%; object-fit: cover;
    margin: 0 auto 0.5rem;
    background: #f0f0f0;
}
.grid-card .nama { font-weight: 600; font-size: 0.9rem; }
.grid-card .nis { font-size: 0.75rem; color: #6b7280; }
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h4 class="card-title fw-semibold m-0">Absensi: {{ $ekskul->nama }}</h4>
                <div class="d-flex gap-2">
                    <div class="btn-group btn-group-sm">
                        <button type="button" id="viewListBtn" class="btn btn-outline-primary active" title="Tampilan List">
                            <i class="ti ti-list"></i>
                        </button>
                        <button type="button" id="viewGridBtn" class="btn btn-outline-primary" title="Tampilan Grid">
                            <i class="ti ti-layout-grid"></i>
                        </button>
                    </div>
                    <a href="{{ route('ekskul.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="GET" class="row g-2 mb-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Pilih Agenda</label>
                        <select name="agenda" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Tanpa Agenda --</option>
                            @foreach($agendaList as $ag)
                                <option value="{{ $ag->id }}" {{ ($agendaId ?? '') == $ag->id ? 'selected' : '' }}>
                                    {{ $ag->judul }} ({{ \Carbon\Carbon::parse($ag->tanggal)->format('d/m/Y') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Absensi</label>
                        <input type="date" name="tanggal_absensi" id="tanggalAbsensi" class="form-control" value="{{ request('tanggal_absensi', date('Y-m-d')) }}">
                    </div>
                    @if($agendaId)
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="form-text text-muted">Agenda: {{ $agenda->judul ?? '' }} ({{ $agenda->tanggal ?? '' }})</div>
                    </div>
                    @endif
                </form>

                <form method="POST" action="{{ route('ekskul.absensi.store', $ekskul->id) }}">
                    @csrf
                    <input type="hidden" name="tanggal" value="{{ request('tanggal_absensi', date('Y-m-d')) }}">
                    <input type="hidden" name="ekskul_agenda_id" value="{{ $agendaId ?? '' }}">

                    @if($siswa->isNotEmpty())
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <button type="button" id="btnHadirSemua" class="btn btn-success btn-sm">
                            <i class="ti ti-check me-1"></i>Hadir Semua
                        </button>
                        <button type="button" id="btnTidakHadirSemua" class="btn btn-danger btn-sm">
                            <i class="ti ti-x me-1"></i>Tidak Hadir Semua
                        </button>
                    </div>
                    @endif

                    <!-- List View -->
                    <div id="listView" class="table-responsive">
                        <table class="table table-vcenter table-hover">
                            <thead>
                                <tr>
                                    <th width="50">No</th>
                                    <th width="50">Foto</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th width="220">Status</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($siswa as $index => $s)
                                @php
                                    $izinAktif = $activeIzinKegiatan->get($s->siswa_id);
                                    $lockedByIzin = (bool) $izinAktif;
                                    $existingStatus = $lockedByIzin ? 'hadir' : ($existingAbsensi->get($s->siswa_id)->status ?? 'hadir');
                                    $existingKeterangan = $existingAbsensi->get($s->siswa_id)->keterangan ?? '';
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        @php
                                            $foto = $s->siswa->user->foto ?? null;
                                            $fotoUrl = $foto ? (str_contains($foto, '://') ? $foto : asset('storage/' . $foto)) : null;
                                        @endphp
                                        @if($fotoUrl)
                                        <img src="{{ $fotoUrl }}" alt="" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                                        @else
                                        <span class="avatar avatar-xs rounded-circle" style="width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;background:#e9ecef;font-weight:600;font-size:0.8rem;color:#6c757d;">
                                            {{ substr($s->siswa->nama ?? '?', 0, 1) }
                                        </span>
                                        @endif
                                    </td>
                                    <td>{{ $s->siswa->nis ?? '-' }}</td>
                                    <td>{{ $s->siswa->nama ?? '-' }}</td>
                                    <td>{{ $s->siswa->kelas->nama_kelas ?? '-' }}</td>
                                    <td>
                                        <select name="absensi[{{ $index }}][siswa_id]" hidden>
                                            <option value="{{ $s->siswa_id }}" selected></option>
                                        </select>
                                        <select name="absensi[{{ $index }}][status]" class="form-select form-select-sm status-select" {{ $lockedByIzin ? 'disabled' : '' }}>
                                            <option value="hadir" {{ $existingStatus === 'hadir' ? 'selected' : '' }}>Hadir</option>
                                            <option value="izin" {{ $existingStatus === 'izin' ? 'selected' : '' }}>Izin</option>
                                            <option value="sakit" {{ $existingStatus === 'sakit' ? 'selected' : '' }}>Sakit</option>
                                            <option value="alpa" {{ $existingStatus === 'alpa' ? 'selected' : '' }}>Alpa</option>
                                            <option value="tanpa_keterangan" {{ $existingStatus === 'tanpa_keterangan' ? 'selected' : '' }}>Tanpa Keterangan</option>
                                        </select>
                                        @if($lockedByIzin)
                                            <input type="hidden" name="absensi[{{ $index }}][status]" value="hadir">
                                            <div class="form-text mt-1"><span class="badge bg-info">Izin Kegiatan</span></div>
                                        @endif
                                    </td>
                                    <td>
                                        <input type="text" name="absensi[{{ $index }}][keterangan]" class="form-control form-control-sm"
                                               value="{{ $lockedByIzin ? ($izinAktif->keterangan_kegiatan ?? '') : $existingKeterangan }}" maxlength="200" {{ $lockedByIzin ? 'readonly' : '' }}>
                                        @if($lockedByIzin)
                                            <input type="hidden" name="absensi[{{ $index }}][keterangan]" value="{{ $izinAktif->keterangan_kegiatan ?? '' }}">
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada anggota yang diterima.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Grid View -->
                    <div id="gridView" class="grid-container" style="display:none;">
                        @forelse($siswa as $index => $s)
                            @php
                                $izinAktif = $activeIzinKegiatan->get($s->siswa_id);
                                $lockedByIzin = (bool) $izinAktif;
                                $existingStatus = $lockedByIzin ? 'hadir' : ($existingAbsensi->get($s->siswa_id)->status ?? 'hadir');
                                $existingKeterangan = $existingAbsensi->get($s->siswa_id)->keterangan ?? '';
                            @endphp
                            <div class="grid-card">
                                @if($fotoUrl)
                                <img src="{{ $fotoUrl }}" alt="" class="avatar">
                                @else
                                <span class="avatar" style="width:60px;height:60px;display:inline-flex;align-items:center;justify-content:center;background:#e9ecef;font-weight:700;font-size:1.3rem;color:#6c757d;border-radius:50%;margin:0 auto 0.5rem;">
                                    {{ substr($s->siswa->nama ?? '?', 0, 1) }}
                                </span>
                                @endif
                                <div class="nama">{{ $s->siswa->nama ?? '-' }}</div>
                                <div class="nis">{{ $s->siswa->nis ?? '-' }}</div>
                                <div class="mb-1"><small class="text-muted">{{ $s->siswa->kelas->nama_kelas ?? '-' }}</small></div>
                                <select name="absensi[{{ $index }}][siswa_id]" hidden>
                                    <option value="{{ $s->siswa_id }}" selected></option>
                                </select>
                                <select name="absensi[{{ $index }}][status]" class="form-select form-select-sm status-select mb-1" {{ $lockedByIzin ? 'disabled' : '' }}>
                                    <option value="hadir" {{ $existingStatus === 'hadir' ? 'selected' : '' }}>Hadir</option>
                                    <option value="izin" {{ $existingStatus === 'izin' ? 'selected' : '' }}>Izin</option>
                                    <option value="sakit" {{ $existingStatus === 'sakit' ? 'selected' : '' }}>Sakit</option>
                                    <option value="alpa" {{ $existingStatus === 'alpa' ? 'selected' : '' }}>Alpa</option>
                                    <option value="tanpa_keterangan" {{ $existingStatus === 'tanpa_keterangan' ? 'selected' : '' }}>Tanpa Keterangan</option>
                                </select>
                                @if($lockedByIzin)
                                    <input type="hidden" name="absensi[{{ $index }}][status]" value="hadir">
                                @endif
                                <input type="text" name="absensi[{{ $index }}][keterangan]" class="form-control form-control-sm"
                                       value="{{ $lockedByIzin ? ($izinAktif->keterangan_kegiatan ?? '') : $existingKeterangan }}" maxlength="200" placeholder="Catatan" {{ $lockedByIzin ? 'readonly' : '' }}>
                                @if($lockedByIzin)
                                    <input type="hidden" name="absensi[{{ $index }}][keterangan]" value="{{ $izinAktif->keterangan_kegiatan ?? '' }}">
                                @endif
                            </div>
                        @empty
                            <div class="col-12 text-center text-muted py-4">Belum ada anggota yang diterima.</div>
                        @endforelse
                    </div>

                    @if($siswa->isNotEmpty())
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>Simpan Absensi
                        </button>
                    </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var viewListBtn = document.getElementById('viewListBtn');
    var viewGridBtn = document.getElementById('viewGridBtn');
    var listView = document.getElementById('listView');
    var gridView = document.getElementById('gridView');
    var btnHadirSemua = document.getElementById('btnHadirSemua');
    var btnTidakHadirSemua = document.getElementById('btnTidakHadirSemua');

    // View toggle
    if (viewListBtn && viewGridBtn) {
        viewListBtn.addEventListener('click', function() {
            viewListBtn.classList.add('active');
            viewGridBtn.classList.remove('active');
            listView.style.display = '';
            gridView.style.display = 'none';
        });
        viewGridBtn.addEventListener('click', function() {
            viewGridBtn.classList.add('active');
            viewListBtn.classList.remove('active');
            listView.style.display = 'none';
            gridView.style.display = '';
        });
    }

    // Hadir Semua
    if (btnHadirSemua) {
        btnHadirSemua.addEventListener('click', function() {
            document.querySelectorAll('.status-select').forEach(function(sel) {
                sel.value = 'hadir';
            });
        });
    }

    // Tidak Hadir Semua
    if (btnTidakHadirSemua) {
        btnTidakHadirSemua.addEventListener('click', function() {
            document.querySelectorAll('.status-select').forEach(function(sel) {
                sel.value = 'alpa';
            });
        });
    }
});
</script>
@endpush