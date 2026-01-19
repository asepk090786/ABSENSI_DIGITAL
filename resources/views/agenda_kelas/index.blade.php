@extends('layouts.app', ['pageSlug' => 'agenda'])

@section('title','Agenda Kelas')

@section('content')
<style>
    .hover-shadow {
        transition: all 0.3s ease;
    }
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
</style>

<div class="container-fluid">
    @if($kelasQuickAccess->isNotEmpty())
    <!-- Menu Akses Cepat -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary-subtle">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-clock-play me-2"></i>Menu Akses Cepat - Isi Agenda Kelas Anda
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($kelasQuickAccess as $kelas)
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <div class="card border border-primary h-100 hover-shadow">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="ti ti-book-2" style="font-size: 48px; color: var(--bs-primary);"></i>
                                    </div>
                                    <h5 class="card-title mb-2">{{ $kelas->nama_kelas }}</h5>
                                    @if($kelas->wali_nama)
                                    <p class="text-muted small mb-3">
                                        <i class="ti ti-user me-1"></i>{{ $kelas->wali_nama }}
                                    </p>
                                    @endif
                                    <a href="{{ route('agenda_kelas.create', ['kelas_id' => $kelas->id]) }}" 
                                       class="btn btn-primary btn-sm w-100">
                                        <i class="ti ti-edit me-1"></i>Isi Agenda Kelas Ini
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Data Agenda Kelas</h4>
                    <a href="{{ route('agenda_kelas.create') }}" class="btn btn-primary btn-sm">
                        <i class="ti ti-plus"></i> Tambah Agenda
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

                    @if($items->isEmpty())
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle"></i> Belum ada data agenda kelas.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Kelas</th>
                                        <th>Guru</th>
                                        <th>Jam KBM</th>
                                        <th>Kegiatan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($items as $index => $it)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($it->tanggal)->format('d/m/Y') }}</td>
                                        <td>{{ $it->kelas->nama_kelas ?? '-' }}</td>
                                        <td>{{ $it->guru->nama ?? '-' }}</td>
                                        <td>{{ $it->jamBelajar->jam_mulai ?? '-' }} - {{ $it->jamBelajar->jam_selesai ?? '-' }}</td>
                                        <td>{{ Str::limit($it->kegiatan, 50) }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('agenda_kelas.show', $it->id) }}" class="btn btn-sm btn-info" title="Lihat">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                                <a href="{{ route('agenda_kelas.edit', $it->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="ti ti-edit"></i>
                                                </a>
                                                <form action="{{ route('agenda_kelas.destroy', $it->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus agenda ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
