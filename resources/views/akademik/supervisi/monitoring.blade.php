@extends('layouts.app', ['pageSlug' => 'supervisi'])

@section('title', 'Monitoring Tindak Lanjut')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">📊 Monitoring Tindak Lanjut Supervisi</h4>
                <a href="{{ route('supervisi.dashboard') }}" class="btn btn-secondary btn-sm">← Dashboard</a>
            </div>
            <div class="card-body">
                <p class="text-muted">Pantau progress pelaksanaan rencana tindak lanjut dari setiap supervisi. Lihat deadline, status, dan bukti perkembangan.</p>
            </div>
        </div>

        {{-- Filter & Search --}}
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('supervisi.monitoring') }}" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="guru" class="form-control form-control-sm" placeholder="Cari Guru..." value="{{ request('guru') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua Status</option>
                            <option value="Belum Mulai" {{ request('status') == 'Belum Mulai' ? 'selected' : '' }}>Belum Mulai</option>
                            <option value="Berjalan" {{ request('status') == 'Berjalan' ? 'selected' : '' }}>Berjalan</option>
                            <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="Ditunda" {{ request('status') == 'Ditunda' ? 'selected' : '' }}>Ditunda</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="priority" class="form-select form-select-sm">
                            <option value="">Semua Prioritas</option>
                            <option value="overdue" {{ request('priority') == 'overdue' ? 'selected' : '' }}>Lewat Deadline</option>
                            <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Segera</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-sm w-100">🔍 Filter</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Action Plans List --}}
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Daftar Rencana Tindak Lanjut</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Guru</th>
                            <th>Mapel</th>
                            <th>Tujuan Tindak Lanjut</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Deadline</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(\App\Models\ActionPlan::with('postConference.supervisi.guru.user', 'postConference.supervisi.mataPelajaran', 'monitorings')->orderBy('target_selesai')->get() as $plan)
                            @php
                                $supervisi = $plan->postConference->supervisi;
                                $daysLeft = now()->diffInDays(\Carbon\Carbon::parse($plan->target_selesai), false);
                                $lastMonitoring = $plan->monitorings->sortByDesc('created_at')->first();
                                $progress = $lastMonitoring?->progress_persen ?? 0;
                                $isOverdue = now()->greaterThan($plan->target_selesai) && $plan->status != 'Selesai';
                            @endphp
                            <tr class="{{ $isOverdue ? 'table-danger' : '' }}">
                                <td>
                                    <strong>{{ $supervisi->guru->user->name ?? $supervisi->guru->nama }}</strong>
                                </td>
                                <td><small>{{ $supervisi->mataPelajaran->nama_mapel ?? '-' }}</small></td>
                                <td>
                                    <div>{{ Str::limit($plan->tujuan, 35) }}</div>
                                    <small class="text-muted">{{ Str::limit($plan->aktivitas, 40) }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $plan->status == 'Selesai' ? 'success' : ($plan->status == 'Berjalan' ? 'warning' : 'secondary') }}">
                                        {{ $plan->status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar {{ $progress >= 75 ? 'bg-success' : ($progress >= 50 ? 'bg-info' : 'bg-warning') }}" 
                                             role="progressbar" style="width: {{ $progress }}%"
                                             aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                                            <small>{{ $progress }}%</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ \Carbon\Carbon::parse($plan->target_selesai)->format('d-m-Y') }}</strong>
                                    </div>
                                    @if($isOverdue)
                                        <span class="badge bg-danger">{{ abs($daysLeft) }} hari lewat</span>
                                    @elseif($daysLeft <= 7 && $daysLeft > 0)
                                        <span class="badge bg-warning">{{ $daysLeft }} hari lagi</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('supervisi.action-plan.edit', $plan) }}" class="btn btn-sm btn-info d-inline-flex align-items-center gap-1" title="Edit & Monitor">
                                        <i class="fas fa-edit"></i>
                                        <span>Monitor</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <p class="text-muted mb-2">Belum ada rencana tindak lanjut</p>
                                    <a href="{{ route('supervisi.prasupervisi') }}" class="btn btn-sm btn-primary">Buat Supervisi Baru</a>
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
                $allPlans = \App\Models\ActionPlan::all();
                $selesai = $allPlans->where('status', 'Selesai')->count();
                $berjalan = $allPlans->where('status', 'Berjalan')->count();
                $belumMulai = $allPlans->where('status', 'Belum Mulai')->count();
                $overdue = $allPlans->filter(fn($p) => now()->greaterThan($p->target_selesai) && $p->status != 'Selesai')->count();
            @endphp
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">✓ Selesai</h5>
                        <h2 class="text-success">{{ $selesai }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">▶ Berjalan</h5>
                        <h2 class="text-warning">{{ $berjalan }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">⏸ Belum Mulai</h5>
                        <h2 class="text-secondary">{{ $belumMulai }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">⚠ Lewat Deadline</h5>
                        <h2 class="text-danger">{{ $overdue }}</h2>
                    </div>
                </div>
            </div>
        </div>

        {{-- Monitoring Tips --}}
        <div class="card mt-4 bg-light">
            <div class="card-header">
                <h5 class="card-title mb-0">💡 Tips Monitoring Efektif</h5>
            </div>
            <div class="card-body small">
                <ul class="mb-0">
                    <li><strong>Jadwal Regular:</strong> Lakukan monitoring secara periodik (mingguan/dua mingguan) sesuai durasi action plan</li>
                    <li><strong>Komunikasi:</strong> Hubungi penanggung jawab untuk update progress dan hambatan</li>
                    <li><strong>Dokumentasi:</strong> Catat semua perkembangan dan bukti nyata pelaksanaan</li>
                    <li><strong>Support:</strong> Berikan dukungan dan solusi jika ada hambatan</li>
                    <li><strong>Fleksibilitas:</strong> Sesuaikan timeline jika ada kendala, tapi tetap fokus pada hasil akhir</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
