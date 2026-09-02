@extends('layouts.app')

@section('title', 'Absensi Guru - Admin')

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

    .admin-absensi-guru .status-tidak-hadir {
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
                <a href="{{ route('admin.absensi_guru.laporan') }}" class="btn btn-outline-info">
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
                            <a href="{{ route('admin.absensi_guru.index') }}" class="btn btn-outline-secondary">
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
                <form method="POST" action="{{ route('admin.absensi_guru.hadir_semua') }}"
                    onsubmit="return confirm('Tandai semua guru aktif sebagai hadir pada tanggal ini?');">
                    @csrf
                    <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                    <button type="submit" class="btn btn-success">
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
                                    'izin' => 'status-izin',
                                    'sakit' => 'status-sakit'
                                ];
                            @endphp
                            @if($status)
                                <span class="status-badge {{ $statusBadges[$status] ?? 'status-belum' }}">
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </span>
                            @else
                                <span class="status-badge status-belum">Belum Absensi</span>
                            @endif
                        </td>
                        <td>
                            @php $jadwalItems = $jadwalPerGuru->get($g->id, collect()); @endphp
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
                            @else
                                @if($jadwalItems->isEmpty())
                                    <div class="keterangan-absensi">-</div>
                                @endif
                            @endif
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAbsensi" 
                                onclick="setModalData({{ $g->id }}, '{{ $g->nama }}', '{{ $status ?? '' }}', '{{ $absensi?->keterangan ?? '' }}')">
                                <i class="ti ti-pencil me-1"></i>Input
                            </button>
                            @if($absensi)
                            <form method="POST" action="{{ route('admin.absensi_guru.delete') }}" style="display: inline;" 
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
            <form method="POST" action="{{ route('admin.absensi_guru.store') }}">
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
                            <option value="izin">Izin</option>
                            <option value="sakit">Sakit</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" name="keterangan" rows="3" placeholder="Contoh: Sakit flu, izin acara keluarga, dll"></textarea>
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
function setModalData(guruId, guruName, status, keterangan) {
    document.getElementById('modalGuruId').value = guruId;
    document.getElementById('modalGuruName').textContent = guruName;
    document.querySelector('select[name="status"]').value = status;
    document.querySelector('textarea[name="keterangan"]').value = keterangan;
}
</script>
@endpush
@endsection
