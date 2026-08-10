@extends('layouts.app', ['pageSlug' => 'supervisi'])

@section('title', 'Supervisi')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Pengaturan Jadwal Supervisi</h4>
                <a href="{{ route('akademik.supervisi.create') }}" class="btn btn-primary btn-sm">
                    <i class="ti ti-plus me-1"></i> Tambah Supervisi
                </a>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ti ti-check me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Guru</th>
                                <th>Mata Pelajaran</th>
                                <th>Tanggal Supervisi</th>
                                <th>Jam KBM ke</th>
                                <th>Kelas</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->guru->user->name ?? $item->guru->nama }}</td>
                                    <td>{{ $item->mataPelajaran->nama_mapel ?? '-' }}</td>
                                    <td>{{ $item->tanggal?->format('d-m-Y') ?? '-' }}</td>
                                    <td>{{ $item->jam_ke }}</td>
                                    <td>{{ $item->kelas->nama_kelas ?? '-' }}</td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('akademik.supervisi.show', $item) }}" class="btn btn-sm btn-info">Lihat</a>
                                        <a href="{{ route('akademik.supervisi.edit', $item) }}" class="btn btn-sm btn-warning">Edit</a>
                                        <form action="{{ route('akademik.supervisi.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus jadwal supervisi ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Belum ada jadwal supervisi.</td>
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
