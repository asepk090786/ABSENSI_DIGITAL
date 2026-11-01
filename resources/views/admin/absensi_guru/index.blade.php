@extends('layouts.app')

@section('title', 'Absensi Guru')

@push('css')
<style>
    .admin-absensi-guru .status-badge {
        border: 0;
        border-radius: 999px;
        display: inline-block;
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .01em;
        line-height: 1.2;
        padding: .35rem .6rem;
    }

    .admin-absensi-guru .status-hadir {
        background-color: #18794e;
        color: #fff;
    }

    .admin-absensi-guru .status-tidak-hadir,
    .admin-absensi-guru .status-dinas-luar {
        background-color: #b42318;
        color: #fff;
    }

    .admin-absensi-guru .status-izin {
        background-color: #f5c242;
        color: #3d2b00;
    }

    .admin-absensi-guru .status-sakit {
        background-color: #1769aa;
        color: #fff;
    }

    .admin-absensi-guru .status-belum {
        background-color: #e9ecef;
        color: #343a40;
    }

    .admin-absensi-guru .table td,
    .admin-absensi-guru .table th {
        color: #27313b;
    }

    .admin-absensi-guru .table thead th {
        color: #17212b;
        font-weight: 700;
    }

    .admin-absensi-guru .text-muted {
        color: #5b6670 !important;
    }

    .admin-absensi-guru .jadwal-kelas-list {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem;
    }

    .admin-absensi-guru .jadwal-kelas-block {
        border-radius: 3px;
        display: inline-flex;
        flex-direction: column;
        font-size: .72rem;
        font-weight: 700;
        line-height: 1.25;
        min-width: 108px;
        padding: .35rem .5rem;
    }

    .admin-absensi-guru .jadwal-kelas-block small {
        font-size: .66rem;
        font-weight: 600;
        opacity: .9;
    }

    .admin-absensi-guru .jadwal-hadir {
        background-color: #b7e4c7;
        border: 1px solid #18794e;
        color: #104c31;
    }

    .admin-absensi-guru .jadwal-menunggu {
        background-color: #ffe69c;
        border: 1px solid #b58105;
        color: #4a3500;
    }

    .admin-absensi-guru .jadwal-tidak-hadir {
        background-color: #f5b5b0;
        border: 1px solid #b42318;
        color: #71150e;
    }

    .admin-absensi-guru .keterangan-absensi {
        color: #3f4a54;
        font-size: .8rem;
        margin-top: .45rem;
    }

    .admin-absensi-guru .absensi-audit-log {
        border-left: 2px solid #adb5bd;
        color: #495057;
        font-size: .75rem;
        margin-top: .45rem;
        padding-left: .5rem;
    }

    .admin-absensi-guru .absensi-audit-log + .absensi-audit-log {
        margin-top: .3rem;
    }

    .admin-absensi-guru .bukti-absensi-link {
        display: inline-block;
        margin-top: .35rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid admin-absensi-guru">
    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title"><i class="ti ti-user-check me-2"></i>Absensi Guru</h2>
                <div class="text-muted">Kelola absensi kehadiran guru</div>
            </div>
            <div class="col-auto">
                <a href="{{ route($routePrefix . '.laporan') }}" class="btn btn-outline-info">
                    <i class="ti ti-report me-1"></i>Laporan
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success d-print-none"><i class="ti ti-check me-2"></i>{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger d-print-none"><i class="ti ti-alert-circle me-2"></i>{{ session('error') }}</div>
    @endif
    @if(session('warning'))
    <div class="alert alert-warning d-print-none"><i class="ti ti-alert-triangle me-2"></i>{{ session('warning') }}</div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger d-print-none">
        <strong>Periksa kembali input absensi:</strong>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    @if($piketPastDateBlocked)
    <div class="alert alert-warning d-print-none">
        <i class="ti ti-lock me-2"></i>Pengaturan Admin memblokir input dan perubahan absensi Guru Piket untuk tanggal terlewat ({{ \Carbon\Carbon::parse($tanggal)->format('d F Y') }}).
    </div>
    @endif

    <div class="row mb-3">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filter & Pencarian</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Tanggal</label>
                            <input type="date" class="form-control" name="tanggal" value="{{ $tanggal }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cari Guru</label>
                            <input type="text" class="form-control" name="search_guru" placeholder="Nama atau NIP guru..." value="{{ $searchGuru }}">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-search me-1"></i>Tampilkan
                            </button>
                            <a href="{{ route($routePrefix . '.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-reset me-1"></i>Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Statistik</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-2">
                            <small class="text-muted">Total Guru</small>
                            <div class="h4 mb-0">{{ $totalGuru }}</div>
                        </div>
                        <div class="col-6 mb-2">
                            <small class="text-muted">Sudah Absensi</small>
                            <div class="h4 mb-0 text-success">{{ $sudahAbsensi }}</div>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">Belum Absensi</small>
                            <div class="h4 mb-0 text-warning">{{ $belumAbsensi }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Data Absensi Guru - {{ \Carbon\Carbon::parse($tanggal)->format('d F Y') }}</h3>
            <div class="card-actions">
                @if($canManageLocks)
                <form method="POST" action="{{ route('admin.absensi_guru.lock_all') }}" class="d-inline"
                    onsubmit="return confirm('{{ $allGuruLocked ? 'Buka kunci absensi semua guru aktif pada tanggal ini?' : 'Kunci input absensi semua guru aktif pada tanggal ini untuk Guru Piket?' }}');">
                    @csrf
                    <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                    <input type="hidden" name="locked" value="{{ $allGuruLocked ? 0 : 1 }}">
                    <button type="submit" class="btn {{ $allGuruLocked ? 'btn-warning' : 'btn-outline-danger' }}"
                        @disabled($totalGuruAktif === 0)>
                        <i class="ti {{ $allGuruLocked ? 'ti-lock-open' : 'ti-lock' }} me-1"></i>
                        {{ $allGuruLocked ? 'Buka Semua Kunci' : 'Kunci Semua' }}
                    </button>
                </form>
                @endif
                <form method="POST" action="{{ route($routePrefix . '.hadir_semua') }}"
                    onsubmit="return confirm('Tandai semua guru aktif sebagai hadir pada tanggal ini?');">
                    @csrf
                    <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                    <button type="submit" class="btn btn-success" @disabled($piketPastDateBlocked)>
                        <i class="ti ti-checks me-1"></i>Hadir Semua
                    </button>
                </form>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th>Nama Guru</th>
                        <th>NIP</th>
                        <th style="width: 15%;">Status</th>
                        <th>Keterangan</th>
                        <th style="width: 15%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($guru as $index => $g)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $g->nama }}</strong>
                            @if($g->user)
                            <br><small class="text-muted">{{ $g->user->name }}</small>
                            @endif
                        </td>
                        <td>{{ $g->nip ?? '-' }}</td>
                        <td>
                            @php
                                $absensi = $absensiHariIni->get($g->id);
                                $status = $absensi?->status;
                                $statusBadges = [
                                    'hadir' => 'status-hadir',
                                    'tidak_hadir' => 'status-tidak-hadir',
                                    'dinas_luar' => 'status-dinas-luar',
                                    'izin' => 'status-izin',
                                    'sakit' => 'status-sakit'
                                ];
                                $statusLabels = [
                                    'hadir' => 'Hadir',
                                    'tidak_hadir' => 'Tidak Hadir',
                                    'dinas_luar' => 'Dinas Luar',
                                    'izin' => 'Izin',
                                    'sakit' => 'Sakit',
                                ];
                                $statusLabel = $status ? ($statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status))) : null;
                            @endphp
                            @if($status)
                                <span class="status-badge {{ $statusBadges[$status] ?? 'status-belum' }}">
                                    {{ $statusLabel }}
                                </span>
                            @else
                                <span class="status-badge status-belum">Belum Absensi</span>
                            @endif
                        </td>
                        <td>
                            @php $jadwalItems = $jadwalPerGuru->get($g->id, collect()); @endphp
                            @php $isLocked = $absensiLocks->has($g->id); @endphp
                            @if($isLocked)
                                <span class="badge bg-warning text-dark">Terkunci Admin</span>
                            @endif
                            @if($jadwalItems->isNotEmpty())
                                <div class="jadwal-kelas-list">
                                    @foreach($jadwalItems as $jadwalItem)
                                    <span class="jadwal-kelas-block jadwal-{{ $jadwalItem['status'] }}">
                                        <span>{{ $jadwalItem['kelas'] }}</span>
                                        <small>{{ $jadwalItem['jam'] }} - {{ $jadwalItem['label'] }}</small>
                                    </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted">Tidak ada jadwal KBM</span>
                            @endif
                            @if($absensi && $absensi->keterangan)
                                <div class="keterangan-absensi">{{ $absensi->keterangan }}</div>
                            @endif
                            @if($absensi?->bukti_token)
                                <a class="bukti-absensi-link" href="{{ route('public.absensi-guru.evidence', $absensi->bukti_token) }}" target="_blank" rel="noopener">
                                    <i class="ti ti-paperclip me-1"></i>Lihat bukti
                                </a>
                            @endif
                            @if($showAbsensiAudit && $absensi?->logs?->isNotEmpty())
                                @foreach($absensi->logs as $log)
                                <div class="absensi-audit-log">
                                    <span>{{ $log->created_at->format('d/m/Y H:i') }}</span><br>
                                    @if($log->old_status)
                                        {{ $log->actor_name }} mengubah status:
                                        {{ $statusLabels[$log->old_status] ?? ucfirst(str_replace('_', ' ', $log->old_status)) }}
                                        &rarr; {{ $statusLabels[$log->new_status] ?? ucfirst(str_replace('_', ' ', $log->new_status)) }}
                                    @else
                                        {{ $log->actor_name }} menginput absensi:
                                        {{ $statusLabels[$log->new_status] ?? ucfirst(str_replace('_', ' ', $log->new_status)) }}
                                    @endif
                                </div>
                                @endforeach
                            @elseif($showAbsensiAudit && $absensi?->pencatat)
                                <div class="absensi-audit-log">
                                    {{ $absensi->pencatat->nama }} menginput absensi:
                                    {{ $statusLabels[$absensi->status] ?? ucfirst(str_replace('_', ' ', $absensi->status)) }}
                                </div>
                            @endif
                            @if($jadwalItems->isEmpty() && !$absensi?->keterangan && (!$showAbsensiAudit || (!$absensi?->logs?->isNotEmpty() && !$absensi?->pencatat)))
                                <div class="keterangan-absensi">-</div>
                            @endif
                        </td>
                        <td>
                            @if($canManageLocks)
                            <form method="POST" action="{{ route('admin.absensi_guru.lock') }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                                <input type="hidden" name="guru_id" value="{{ $g->id }}">
                                <input type="hidden" name="locked" value="{{ $isLocked ? 0 : 1 }}">
                                <button type="submit" class="btn btn-sm {{ $isLocked ? 'btn-warning' : 'btn-outline-secondary' }}"
                                    title="{{ $isLocked ? 'Buka kunci input Guru Piket' : 'Kunci input Guru Piket' }}">
                                    <i class="ti {{ $isLocked ? 'ti-lock-open' : 'ti-lock' }}"></i>
                                    {{ $isLocked ? 'Buka' : 'Kunci' }}
                                </button>
                            </form>
                            @endif
                            @if(!$piketPastDateBlocked && (!$isLocked || $showAbsensiAudit))
                                <button type="button" class="btn btn-sm {{ $absensi && $showAbsensiAudit ? 'btn-success' : 'btn-primary' }}"
                                    data-bs-toggle="modal" data-bs-target="#modalAbsensi"
                                    data-guru-id="{{ $g->id }}" data-guru-name="{{ $g->nama }}"
                                    data-status="{{ $status ?? '' }}" data-keterangan="{{ $absensi?->keterangan ?? '' }}"
                                    data-bukti-url="{{ $absensi?->bukti_token ? route('public.absensi-guru.evidence', $absensi->bukti_token) : '' }}">
                                    <i class="ti {{ $absensi && $showAbsensiAudit ? 'ti-edit' : 'ti-pencil' }} me-1"></i>{{ $absensi && $showAbsensiAudit ? 'Edit' : 'Input' }}
                                </button>
                                @if($absensi)
                                <form method="POST" action="{{ route($routePrefix . '.delete') }}" class="d-inline"
                                    onsubmit="return confirm('Hapus absensi ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                                    <input type="hidden" name="guru_id" value="{{ $g->id }}">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="ti ti-trash me-1"></i>Hapus
                                    </button>
                                </form>
                                @endif
                            @else
                                <span class="text-muted">{{ $piketPastDateBlocked ? 'Tanggal terlewat dikunci' : 'Tidak dapat diubah' }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            Tidak ada guru yang cocok dengan kriteria pencarian.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Absensi -->
<div class="modal fade" id="modalAbsensi" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Input Absensi Guru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route($routePrefix . '.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="modalGuruId" name="guru_id">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Guru: <span id="modalGuruName"></span></label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" class="form-control" name="tanggal" value="{{ $tanggal }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="">-- Pilih Status --</option>
                            <option value="hadir">Hadir</option>
                            <option value="tidak_hadir">Tidak Hadir</option>
                            <option value="dinas_luar">Dinas Luar</option>
                            <option value="izin">Izin</option>
                            <option value="sakit">Sakit</option>
                        </select>
                    </div>
                    @if($showAbsensiAudit)
                    <div class="mb-3" id="rentangAbsensiGroup" hidden>
                        <label class="form-label">Rentang tanggal status</label>
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <label class="form-label" for="tanggalMulaiAbsensi">Dari</label>
                                <input type="date" class="form-control" id="tanggalMulaiAbsensi" name="tanggal_mulai">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="tanggalSelesaiAbsensi">Sampai</label>
                                <input type="date" class="form-control" id="tanggalSelesaiAbsensi" name="tanggal_selesai">
                            </div>
                        </div>
                        <div class="form-hint">Opsional. Jika diisi, status akan dicatat dan dikunci untuk setiap tanggal dalam rentang ini. Kosongkan jika hanya untuk tanggal yang dipilih.</div>
                    </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" name="keterangan" rows="3" placeholder="Contoh: Sakit flu, izin acara keluarga, dll"></textarea>
                    </div>
                    <div class="mb-3" id="buktiAbsensiGroup" hidden>
                        <label class="form-label" for="buktiAbsensiInput">Upload bukti dukung (opsional)</label>
                        <input type="file" class="form-control" id="buktiAbsensiInput" name="bukti" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png">
                        <div class="form-hint">Format PDF, JPEG, atau PNG; maksimal 5 MB. Tautan publik akan tercantum di laporan rekap absensi.</div>
                        <a id="buktiAbsensiExisting" class="d-none mt-2" href="#" target="_blank" rel="noopener">Buka bukti yang tersimpan</a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-check me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script>
document.querySelectorAll('[data-bs-target="#modalAbsensi"]').forEach((button) => {
    button.addEventListener('click', () => {
        document.getElementById('modalGuruId').value = button.dataset.guruId;
        document.getElementById('modalGuruName').textContent = button.dataset.guruName;
        document.querySelector('select[name="status"]').value = button.dataset.status;
        document.querySelector('textarea[name="keterangan"]').value = button.dataset.keterangan;
        document.getElementById('buktiAbsensiInput').value = '';
        const tanggalMulaiInput = document.getElementById('tanggalMulaiAbsensi');
        const tanggalSelesaiInput = document.getElementById('tanggalSelesaiAbsensi');
        if (tanggalMulaiInput && tanggalSelesaiInput) {
            tanggalMulaiInput.value = '';
            tanggalSelesaiInput.value = '';
        }
        const buktiUrl = button.dataset.buktiUrl || '';
        const buktiLink = document.getElementById('buktiAbsensiExisting');
        buktiLink.href = buktiUrl || '#';
        buktiLink.classList.toggle('d-none', !buktiUrl);
        updateBuktiVisibility();
    });
});

function updateBuktiVisibility() {
    const status = document.querySelector('select[name="status"]').value;
    const showEvidence = ['izin', 'sakit', 'dinas_luar'].includes(status);
    document.getElementById('buktiAbsensiGroup').hidden = !showEvidence;
    if (!showEvidence) {
        document.getElementById('buktiAbsensiInput').value = '';
    }
    const rentangGroup = document.getElementById('rentangAbsensiGroup');
    const tanggalMulaiInput = document.getElementById('tanggalMulaiAbsensi');
    const tanggalSelesaiInput = document.getElementById('tanggalSelesaiAbsensi');
    if (rentangGroup && tanggalMulaiInput && tanggalSelesaiInput) {
        rentangGroup.hidden = !showEvidence;
        tanggalMulaiInput.disabled = !showEvidence;
        tanggalSelesaiInput.disabled = !showEvidence;
        tanggalMulaiInput.required = showEvidence && Boolean(tanggalSelesaiInput.value);
        tanggalSelesaiInput.required = showEvidence && Boolean(tanggalMulaiInput.value);
    }
}

document.querySelector('select[name="status"]').addEventListener('change', updateBuktiVisibility);
document.getElementById('tanggalMulaiAbsensi')?.addEventListener('change', updateBuktiVisibility);
document.getElementById('tanggalSelesaiAbsensi')?.addEventListener('change', updateBuktiVisibility);
</script>
@endpush
@endsection
