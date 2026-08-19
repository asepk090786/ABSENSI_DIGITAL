@extends('layouts.app', ['pageSlug' => 'supervisi'])
@section('title', 'Pascasupervisi')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">Pascasupervisi - Post-Conference & Feedback</h4>
        <a href="{{ route('supervisi.dashboard') }}" class="btn btn-secondary btn-sm">Dashboard</a>
    </div>
    <div class="card-body">
        <p class="text-muted mb-4">Bagian ini menampung refleksi guru, refleksi supervisor, dan umpan balik yang bersifat konstruktif dan berbasis data.</p>

        @if($items->isEmpty())
            <div class="alert alert-light border">Belum ada supervisi yang siap untuk post-conference.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Guru</th>
                            <th>Mata Pelajaran</th>
                            <th>Kelas</th>
                            <th>Tanggal</th>
                            <th>Fokus</th>
                            <th>Status</th>
                            <th>Post-Conf</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td><strong>{{ $item->guru->user->name ?? $item->guru->nama ?? '-' }}</strong></td>
                                <td><small>{{ $item->mataPelajaran->nama_mapel ?? '-' }}</small></td>
                                <td><small>{{ $item->kelas->nama_kelas ?? '-' }}</small></td>
                                <td><small>{{ $item->tanggal?->format('d-m-Y') ?? '-' }}</small></td>
                                <td><small>{{ Str::limit($item->fokus, 20) }}</small></td>
                                <td><span class="badge bg-success">{{ $item->status ?? 'Selesai' }}</span></td>
                                <td>
                                    @if($item->postConference)
                                        <span class="badge bg-info">✓ Ada</span>
                                    @else
                                        <span class="badge bg-light text-dark">Belum</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('supervisi.post-conference.show', $item) }}" class="btn btn-sm btn-info d-inline-flex align-items-center gap-1" title="Post-Conference & Feedback">
                                        <i class="fas fa-comments"></i>
                                        <span>Feedback</span>
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
