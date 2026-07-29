@extends('layouts.app', ['pageSlug' => 'ekskul'])

@section('title', 'Data Ekstrakurikuler')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title fw-semibold m-0">Data Ekstrakurikuler</h4>
                    </div>
                    <div class="col-auto">
                        @if(auth()->user()->hasRole('Admin'))
                        <a href="{{ route('ekskul.create') }}" class="btn btn-primary btn-sm">
                            <i class="ti ti-plus me-1"></i>Tambah Baru
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-vcenter table-hover">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Nama</th>
                                <th>Pembina</th>
                                <th class="text-center">Anggota</th>
                                <th class="text-center">Jadwal</th>
                                <th>Status</th>
                                <th width="320">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-semibold">{{ $item->nama }}</td>
                                <td>{{ $item->guru->nama ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-primary-light text-primary">
                                        {{ $item->anggota_diterima_count ?? 0 }} Anggota
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-accent-light text-accent">
                                        {{ $item->jadwal_count ?? 0 }} Jadwal
                                    </span>
                                </td>
                                <td>
                                    @if($item->status === 'aktif')
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        @if(auth()->user()->hasRole('Admin'))
                                        <a href="{{ route('ekskul.edit', $item->id) }}" class="btn btn-outline-primary" title="Edit">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        @endif
                                        <a href="{{ route('ekskul.anggota', $item->id) }}" class="btn btn-outline-info" title="Kelola Anggota">
                                            <i class="ti ti-users"></i>
                                        </a>
                                        <a href="{{ route('ekskul.jadwal', $item->id) }}" class="btn btn-outline-warning" title="Jadwal">
                                            <i class="ti ti-calendar"></i>
                                        </a>
                                        <a href="{{ route('ekskul.agenda', $item->id) }}" class="btn btn-outline-secondary" title="Agenda">
                                            <i class="ti ti-list-check"></i>
                                        </a>
                                        <a href="{{ route('ekskul.absensi', $item->id) }}" class="btn btn-outline-success" title="Absensi">
                                            <i class="ti ti-clipboard-check"></i>
                                        </a>
                                        <a href="{{ route('ekskul.bukti', $item->id) }}" class="btn btn-outline-dark" title="Bukti Kegiatan">
                                            <i class="ti ti-photo"></i>
                                        </a>
                                        <a href="{{ route('ekskul.rekap', $item->id) }}" class="btn btn-outline-purple" title="Rekap">
                                            <i class="ti ti-report-analytics"></i>
                                        </a>
                                        @if(auth()->user()->hasRole('Admin'))
                                        <form method="POST" action="{{ route('ekskul.destroy', $item->id) }}" class="d-inline" onsubmit="return confirm('Yakin hapus ekstrakurikuler ini?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-outline-danger" title="Hapus">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="ti ti-info-circle me-2"></i>Belum ada data ekstrakurikuler.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection