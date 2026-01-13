@extends('layouts.app')

@section('title','Jam Belajar')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-0">Pengaturan Jam KBM</h2>
                <small class="text-muted">Kelola jadwal pembelajaran setiap harinya</small>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('jam_belajar.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-2"></i>Tambah Jam KBM
                </a>
                <a href="{{ route('jam_belajar.export') }}" class="btn btn-success">
                    <i class="ti ti-download me-2"></i>Export Excel
                </a>
                <a href="{{ route('jam_belajar.template') }}" class="btn btn-info">
                    <i class="ti ti-file-spreadsheet me-2"></i>Download Template
                </a>
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="ti ti-upload me-2"></i>Import Excel
                </button>
                <form action="{{ route('jam_belajar.destroy_all') }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus SEMUA pengaturan jam KBM? Tindakan ini tidak dapat dibatalkan.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="ti ti-trash me-2"></i>Hapus Semua
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="ti ti-check me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('warning') && session('import_errors'))
    <div class="alert alert-warning alert-dismissible fade show">
        <i class="ti ti-alert-triangle me-2"></i>
        @if(session('successCount'))
            <strong>{{ session('successCount') }} data berhasil diimport, namun ada kesalahan:</strong><br>
        @endif
        <ul class="mb-0">
            @foreach(session('import_errors') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="ti ti-alert-circle me-2"></i>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Daily Schedule Grid -->
<div class="row g-3">
    @foreach($days as $day)
        @php $daySchedules = $groupedByDay->get($day, collect()); @endphp
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-calendar me-2"></i>{{ $day }}
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($daySchedules->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 10%;">Jam Ke</th>
                                        <th style="width: 20%;">Mulai</th>
                                        <th style="width: 20%;">Selesai</th>
                                        <th style="width: 20%;">Jenis</th>
                                        <th style="width: 30%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($daySchedules->sortBy('urutan') as $schedule)
                                        <tr>
                                            <td class="fw-bold">{{ $schedule->urutan }}</td>
                                            <td>{{ $schedule->jam_mulai }}</td>
                                            <td>{{ $schedule->jam_selesai }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ $schedule->jenis }}</span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('jam_belajar.edit', $schedule->id) }}" class="btn btn-warning">
                                                        <i class="ti ti-edit"></i> Edit
                                                    </a>
                                                    <form action="{{ route('jam_belajar.destroy', $schedule->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus jadwal ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">
                                                            <i class="ti ti-trash"></i> Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="ti ti-calendar-off" style="font-size: 2rem; opacity: 0.5;"></i>
                            <p class="mt-2">Belum ada jadwal untuk hari ini</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Summary -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="ti ti-info-circle me-2"></i>Informasi Jadwal</h6>
                <div class="row">
                    <div class="col-md-4">
                        <small class="text-muted d-block">Total Jam KBM</small>
                        <h4 class="fw-bold">{{ $groupedByDay->flatten()->count() }} Sesi</h4>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Hari Aktif</small>
                        <h4 class="fw-bold">{{ $groupedByDay->count() }} Hari</h4>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Rata-rata Sesi/Hari</small>
                        <h4 class="fw-bold">{{ $groupedByDay->count() > 0 ? round($groupedByDay->flatten()->count() / $groupedByDay->count(), 1) : 0 }} Sesi</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti ti-file-upload me-2"></i>Import Jadwal Jam KBM
                </h5>
            </div>
            <form id="importForm" action="{{ route('jam_belajar.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">File Excel <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="file" accept=".xlsx,.xls,.csv" required>
                        <small class="text-muted d-block mt-2">
                            <i class="ti ti-info-circle"></i> Format yang didukung: Excel (.xlsx, .xls) atau CSV
                        </small>
                        <small class="text-muted d-block">
                            <i class="ti ti-alert-triangle"></i> Pastikan struktur file sesuai dengan template yang disediakan
                        </small>
                    </div>
                    <div class="alert alert-info mb-0">
                        <small>
                            <strong>Cara menggunakan:</strong>
                            <ol class="mb-0">
                                <li>Unduh template terlebih dahulu menggunakan tombol "Download Template"</li>
                                <li>Isi data sesuai format yang ditentukan</li>
                                <li>Pilih file yang sudah diisi</li>
                                <li>Klik tombol "Import" untuk mengunggah</li>
                            </ol>
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-upload me-1"></i>Import Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
