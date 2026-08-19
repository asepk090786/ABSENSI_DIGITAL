@extends('layouts.app', ['pageSlug' => 'supervisi'])
@section('title', 'Pelaksanaan Supervisi')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">Pelaksanaan Supervisi</h4>
        <a href="{{ route('supervisi.dashboard') }}" class="btn btn-secondary btn-sm">Dashboard</a>
    </div>
    <div class="card-body">
        <p class="text-muted">Tahap observasi dilakukan secara objektif, berbasis fakta, dan tidak menilai semata-mata untuk menemukan kekurangan.</p>

        @if($items->isEmpty())
            <div class="alert alert-light border">Belum ada supervisi yang sedang dilaksanakan.</div>
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
                                <td><span class="badge bg-secondary">{{ $item->status ?? 'Terjadwal' }}</span></td>
                                <td>
                                    <a href="{{ route('supervisi.observasi.show', $item) }}" class="btn btn-sm btn-warning d-inline-flex align-items-center gap-1" title="Mulai Observasi">
                                        <i class="fas fa-pencil-alt"></i>
                                        <span>Observasi</span>
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
