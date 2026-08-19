@extends('layouts.app', ['pageSlug' => 'supervisi'])
@section('title', 'Tindak Lanjut')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">Tindak Lanjut - Rencana Perbaikan</h4>
        <a href="{{ route('supervisi.dashboard') }}" class="btn btn-secondary btn-sm">Dashboard</a>
    </div>
    <div class="card-body">
        <p class="text-muted mb-4">Rencana tindak lanjut dibuat bersama guru dan dipantau sampai target selesai tercapai. Setiap rencana memiliki tujuan spesifik, aktivitas konkret, dan target waktu yang jelas.</p>

        @if($items->isEmpty())
            <div class="alert alert-light border">Belum ada supervisi yang siap untuk rencana tindak lanjut.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Guru</th>
                            <th>Mata Pelajaran</th>
                            <th>Kelas</th>
                            <th>Tanggal</th>
                            <th>Tujuan</th>
                            <th>Status</th>
                            <th>Action Plans</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            @php
                                $actionPlansCount = $item->postConference?->actionPlans->count() ?? 0;
                            @endphp
                            <tr>
                                <td><strong>{{ $item->guru->user->name ?? $item->guru->nama ?? '-' }}</strong></td>
                                <td><small>{{ $item->mataPelajaran->nama_mapel ?? '-' }}</small></td>
                                <td><small>{{ $item->kelas->nama_kelas ?? '-' }}</small></td>
                                <td><small>{{ $item->tanggal?->format('d-m-Y') ?? '-' }}</small></td>
                                <td><small>{{ Str::limit($item->tujuan, 25) }}</small></td>
                                <td><span class="badge bg-success">{{ $item->status ?? 'Selesai' }}</span></td>
                                <td>
                                    @if($actionPlansCount > 0)
                                        <span class="badge bg-primary">{{ $actionPlansCount }} plan</span>
                                    @else
                                        <span class="badge bg-light text-dark">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('supervisi.action-plan.show', $item) }}" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" title="Rencana Tindak Lanjut">
                                        <i class="fas fa-tasks"></i>
                                        <span>Plan</span>
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
