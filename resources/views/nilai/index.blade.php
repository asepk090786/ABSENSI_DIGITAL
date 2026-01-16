@extends('layouts.app')

@section('title', 'Daftar Nilai')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    <i class="ti ti-report-analytics me-2"></i>Daftar Nilai Harian
                </h2>
                <div class="text-muted mt-1">Kelola data nilai harian siswa per mata pelajaran</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="#" class="btn btn-primary d-none d-sm-inline-block" data-bs-toggle="modal" data-bs-target="#modalTambahNilai">
                        <i class="ti ti-plus"></i>
                        Tambah Nilai
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Data Nilai Harian</h3>
                <div class="card-actions">
                    <input type="text" class="form-control form-control-sm" placeholder="Cari..." id="searchInput">
                </div>
            </div>
            <div class="card-body">
                @if($items->count() > 0)
                <div class="table-responsive">
                    <table class="table table-vcenter table-hover" id="tableNilai">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Mata Pelajaran</th>
                                <th>Komponen</th>
                                <th class="text-center">Nilai</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                                <td>{{ $item->nama_siswa }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ $item->nama_kelas ?? '-' }}</span>
                                </td>
                                <td>{{ $item->nama_mapel }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $item->nama_komponen ?? 'Harian' }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $item->nilai >= 75 ? 'success' : ($item->nilai >= 60 ? 'warning' : 'danger') }} fs-3">
                                        {{ $item->nilai ?? '-' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Hapus">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="empty">
                    <div class="empty-img"><img src="{{ asset('tabler/static/illustrations/undraw_printing_invoices_5r4r.svg') }}" height="128" alt="">
                    </div>
                    <p class="empty-title">Belum ada data nilai</p>
                    <p class="empty-subtitle text-muted">
                        Tambahkan nilai siswa dengan mengklik tombol "Tambah Nilai" di atas.
                    </p>
                    <div class="empty-action">
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahNilai">
                            <i class="ti ti-plus"></i>
                            Tambah Nilai Pertama
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Info Card -->
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="ti ti-info-circle text-blue" style="font-size: 32px;"></i>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">Informasi</div>
                                <div class="text-muted">
                                    Sistem nilai menggunakan tabel <strong>nilai_harian</strong> untuk menyimpan nilai siswa per komponen (tugas, kuis, UTS, UAS, dll).
                                    Pastikan data guru, kelas, mata pelajaran, dan komponen nilai sudah tersedia sebelum menginput nilai.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Nilai -->
<div class="modal fade" id="modalTambahNilai" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Nilai Harian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" class="form-control" name="tanggal" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Siswa</label>
                        <select class="form-select" name="siswa_id" required>
                            <option value="">Pilih Siswa...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mata Pelajaran</label>
                        <select class="form-select" name="mapel_id" required>
                            <option value="">Pilih Mata Pelajaran...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Komponen Nilai</label>
                        <select class="form-select" name="komponen_id">
                            <option value="">Pilih Komponen...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nilai (0-100)</label>
                        <input type="number" class="form-control" name="nilai" min="0" max="100" step="0.01" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Search functionality
    document.getElementById('searchInput')?.addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#tableNilai tbody tr');
        
        rows.forEach(row => {
            let text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>
@endpush
