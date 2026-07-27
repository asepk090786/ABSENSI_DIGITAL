@extends('layouts.app', ['pageSlug' => 'siswa_pembelajaran'])

@section('title', 'Materi Pembelajaran')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header border-0 pt-3 pb-2">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title fw-semibold m-0">Materi Pembelajaran</h4>
                        <p class="text-muted mt-1">Kelas: {{ $siswa->kelas->nama_kelas ?? '-' }}</p>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if($items->isEmpty())
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>Belum ada materi pembelajaran untuk kelas Anda saat ini.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-vcenter table-hover table-tabler">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Nama Kegiatan</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($items as $index => $item)
                                <tr>
                                    <td>{{ ($items->currentPage() - 1) * $items->perPage() + $index + 1 }}</td>
                                    <td>{{ $item->rencanaPembelajaran->mataPelajaran->nama_mapel ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('siswa.pembelajaran.show', $item->id) }}" class="text-decoration-none">
                                            {{ $item->nama_kegiatan }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-success text-white">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $item->created_at->format('d/m/Y H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        <i class="ti ti-info-circle me-2"></i>Belum ada materi pembelajaran.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($items->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $items->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
