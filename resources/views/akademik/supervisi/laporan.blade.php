@extends('layouts.app', ['pageSlug' => 'supervisi'])

@section('title', 'Laporan Supervisi')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">📋 Laporan Supervisi</h4>
                <a href="{{ route('supervisi.dashboard') }}" class="btn btn-secondary btn-sm">← Dashboard</a>
            </div>
            <div class="card-body">
                <p class="text-muted">Laporan supervisi menampilkan ringkasan guru, fokus, jadwal, hasil observasi, refleksi, dan tindak lanjut yang telah disepakati.</p>
            </div>
        </div>

        {{-- Filter --}}
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('supervisi.laporan') }}" class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label small">Dari Tanggal</label>
                        <input type="date" name="dari_tanggal" class="form-control form-control-sm" value="{{ request('dari_tanggal', now()->startOfYear()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Sampai Tanggal</label>
                        <input type="date" name="sampai_tanggal" class="form-control form-control-sm" value="{{ request('sampai_tanggal', now()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Guru</label>
                        <input type="text" name="guru" class="form-control form-control-sm" placeholder="Nama guru..." value="{{ request('guru') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua Status</option>
                            <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="Berlangsung" {{ request('status') == 'Berlangsung' ? 'selected' : '' }}>Berlangsung</option>
                            <option value="Terjadwal" {{ request('status') == 'Terjadwal' ? 'selected' : '' }}>Terjadwal</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-sm w-100">🔍 Filter</button>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">&nbsp;</label>
                        <div class="btn-group w-100" role="group">
                            <a href="{{ route('supervisi.laporan.export-excel', ['dari_tanggal' => request('dari_tanggal'), 'sampai_tanggal' => request('sampai_tanggal'), 'guru' => request('guru'), 'status' => request('status')]) }}" 
                               class="btn btn-success btn-sm" title="Export Excel">
                                <i class="fas fa-file-excel"></i> Excel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Supervisions List --}}
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Daftar Supervisi</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No.</th>
                            <th>Guru</th>
                            <th>Mapel</th>
                            <th>Kelas</th>
                            <th>Tanggal</th>
                            <th>Fokus</th>
                            <th>Status</th>
                            <th>Post-Conference</th>
                            <th>Tindak Lanjut</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(\App\Models\Supervisi::with('guru.user', 'mataPelajaran', 'kelas', 'postConference.actionPlans')
                            ->whereDate('tanggal', '>=', request('dari_tanggal', now()->startOfYear()))
                            ->whereDate('tanggal', '<=', request('sampai_tanggal', now()))
                            ->when(request('guru'), fn($q) => $q->whereHas('guru.user', fn($sq) => $sq->where('name', 'like', '%'.request('guru').'%')))
                            ->when(request('status'), fn($q) => $q->where('status', request('status')))
                            ->orderBy('tanggal', 'desc')->get() as $key => $supervisi)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td><strong>{{ $supervisi->guru->user->name ?? $supervisi->guru->nama }}</strong></td>
                                <td><small>{{ $supervisi->mataPelajaran->nama_mapel ?? '-' }}</small></td>
                                <td><small>{{ $supervisi->kelas->nama_kelas ?? '-' }}</small></td>
                                <td><small>{{ $supervisi->tanggal->format('d-m-Y') }}</small></td>
                                <td><small>{{ Str::limit($supervisi->fokus, 25) }}</small></td>
                                <td>
                                    <span class="badge bg-{{ $supervisi->status == 'Selesai' ? 'success' : ($supervisi->status == 'Berlangsung' ? 'warning' : 'secondary') }}">
                                        {{ $supervisi->status }}
                                    </span>
                                </td>
                                <td>
                                    @if($supervisi->postConference)
                                        <span class="badge bg-info">✓ Ada</span>
                                    @else
                                        <span class="badge bg-light text-dark">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $actionPlansCount = $supervisi->postConference?->actionPlans->count() ?? 0;
                                    @endphp
                                    @if($actionPlansCount > 0)
                                        <span class="badge bg-primary">{{ $actionPlansCount }}</span>
                                    @else
                                        <span class="badge bg-light text-dark">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('supervisi.laporan.export-pdf', $supervisi) }}" class="btn btn-xs btn-danger d-inline-flex align-items-center gap-1" title="Export PDF">
                                        <i class="fas fa-file-pdf"></i>
                                        <span>PDF</span>
                                    </a>
                                    <a href="{{ route('akademik.supervisi.show', $supervisi) }}" class="btn btn-xs btn-info d-inline-flex align-items-center gap-1" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                        <span>Detail</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <p class="text-muted mb-0">Tidak ada data supervisi</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Summary Stats --}}
        <div class="row mt-4">
            @php
                $allSupervisions = \App\Models\Supervisi::whereDate('tanggal', '>=', request('dari_tanggal', now()->startOfYear()))
                    ->whereDate('tanggal', '<=', request('sampai_tanggal', now()))
                    ->when(request('guru'), fn($q) => $q->whereHas('guru.user', fn($sq) => $sq->where('name', 'like', '%'.request('guru').'%')))
                    ->when(request('status'), fn($q) => $q->where('status', request('status')))
                    ->get();
                $selesai = $allSupervisions->where('status', 'Selesai')->count();
                $berlangsung = $allSupervisions->where('status', 'Berlangsung')->count();
                $terjadwal = $allSupervisions->where('status', 'Terjadwal')->count();
                $draft = $allSupervisions->where('status', 'Draft')->count();
                $withPostConf = $allSupervisions->filter(fn($s) => $s->postConference)->count();
                $withActionPlan = $allSupervisions->filter(fn($s) => $s->postConference?->actionPlans->count() > 0)->count();
            @endphp
            <div class="col-md-2">
                <div class="card text-center border-primary">
                    <div class="card-body">
                        <h5 class="card-title small">Total</h5>
                        <h2 class="text-primary">{{ $allSupervisions->count() }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center border-success">
                    <div class="card-body">
                        <h5 class="card-title small">✓ Selesai</h5>
                        <h2 class="text-success">{{ $selesai }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center border-warning">
                    <div class="card-body">
                        <h5 class="card-title small">▶ Berlangsung</h5>
                        <h2 class="text-warning">{{ $berlangsung }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center border-info">
                    <div class="card-body">
                        <h5 class="card-title small">📅 Terjadwal</h5>
                        <h2 class="text-info">{{ $terjadwal }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center border-success">
                    <div class="card-body">
                        <h5 class="card-title small">💬 Post-Conf</h5>
                        <h2 class="text-success">{{ $withPostConf }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center border-primary">
                    <div class="card-body">
                        <h5 class="card-title small">📋 Tindak Lanjut</h5>
                        <h2 class="text-primary">{{ $withActionPlan }}</h2>
                    </div>
                </div>
            </div>
        </div>

        {{-- Notes --}}
        <div class="alert alert-info mt-4">
            <strong>💡 Tips Membaca Laporan:</strong>
            <ul class="mb-0 mt-2">
                <li><strong>Status Selesai:</strong> Supervisi sudah lengkap semua tahapan dari prasupervisi hingga post-conference</li>
                <li><strong>Post-Conference:</strong> Menunjukkan apakah refleksi dan feedback sudah dilakukan</li>
                <li><strong>Tindak Lanjut:</strong> Jumlah rencana perbaikan yang sudah disepakati untuk guru</li>
                <li><strong>Export:</strong> Gunakan tombol Excel/PDF untuk membuat laporan formal yang bisa dibagikan</li>
            </ul>
        </div>
    </div>
</div>
@endsection
