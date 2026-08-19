@extends('layouts.app', ['pageSlug' => 'supervisi'])
@section('title', 'Prasupervisi')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">Prasupervisi</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('supervisi.dashboard') }}" class="btn btn-secondary btn-sm">Dashboard</a>
            <a href="{{ route('akademik.supervisi.create') }}" class="btn btn-primary btn-sm">Tambah Jadwal</a>
        </div>
    </div>
    <div class="card-body">
        <p class="text-muted">Tahap ini berfokus pada kesepakatan awal, tujuan supervisi, fokus observasi, dan penentuan jadwal bersama guru.</p>

        @if($items->isEmpty())
            <div class="alert alert-light border">Belum ada jadwal yang siap dipersiapkan.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Guru</th>
                            <th>Mata Pelajaran</th>
                            <th>Kelas</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td>{{ $item->guru->user->name ?? $item->guru->nama ?? '-' }}</td>
                                <td>{{ $item->mataPelajaran->nama_mapel ?? '-' }}</td>
                                <td>{{ $item->kelas->nama_kelas ?? '-' }}</td>
                                <td>{{ $item->tanggal?->format('d-m-Y') ?? '-' }}</td>
                                <td><span class="badge bg-primary">{{ $item->status ?? 'Terjadwal' }}</span></td>
                                <td>
                                    <a href="{{ route('supervisi.prasupervisi.edit', $item) }}" class="btn btn-sm btn-info d-inline-flex align-items-center gap-1" title="Edit Prasupervisi">
                                        <i class="fas fa-edit"></i>
                                        <span>Edit</span>
                                    </a>
                                    <a href="{{ route('akademik.supervisi.show', $item) }}" class="btn btn-sm btn-secondary d-inline-flex align-items-center gap-1" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                        <span>Detail</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
