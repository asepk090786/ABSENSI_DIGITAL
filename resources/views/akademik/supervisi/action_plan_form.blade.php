@extends('layouts.app', ['pageSlug' => 'supervisi'])

@section('title', 'Rencana Tindak Lanjut')

@section('content')
<div class="row">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0">Rencana Tindak Lanjut Supervisi</h4>
                        <small class="text-muted d-block mt-1">Guru: {{ $supervisi->guru->user->name ?? $supervisi->guru->nama }} | {{ $supervisi->tanggal->format('d-m-Y') }}</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if($actionPlans->count() > 0)
                    <div class="alert alert-info">
                        <strong>ℹ️ Rencana Tindak Lanjut yang Sudah Dibuat:</strong> {{ $actionPlans->count() }} item
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Tujuan</th>
                                    <th>Aktivitas</th>
                                    <th>PJ</th>
                                    <th>Target</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($actionPlans as $plan)
                                    <tr>
                                        <td><small>{{ Str::limit($plan->tujuan, 30) }}</small></td>
                                        <td><small>{{ Str::limit($plan->aktivitas, 25) }}</small></td>
                                        <td><small>{{ $plan->penanggungJawab->user->name ?? $plan->penanggungJawab->nama ?? '-' }}</small></td>
                                        <td><small>{{ $plan->target_selesai->format('d-m-Y') }}</small></td>
                                        <td>
                                            <span class="badge bg-{{ $plan->status == 'Selesai' ? 'success' : ($plan->status == 'Berjalan' ? 'warning' : 'secondary') }}">
                                                {{ $plan->status }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('supervisi.action-plan.edit', $plan) }}" class="btn btn-xs btn-info" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">+ Tambah Rencana Tindak Lanjut Baru</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('supervisi.action-plan.store', $supervisi) }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label"><strong>Tujuan Tindak Lanjut *</strong></label>
                                <input type="text" name="tujuan" class="form-control @error('tujuan') is-invalid @enderror" 
                                       placeholder="Contoh: Meningkatkan penggunaan media pembelajaran interaktif"
                                       value="{{ old('tujuan') }}" required>
                                @error('tujuan') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><strong>Aktivitas/Kegiatan yang Akan Dilakukan *</strong></label>
                                <textarea name="aktivitas" class="form-control @error('aktivitas') is-invalid @enderror" rows="3" 
                                          placeholder="Jelaskan secara detail aktivitas apa yang akan dilakukan untuk mencapai tujuan&#10;Contoh: 1) Workshop pembuatan media digital, 2) Praktik desain pembelajaran interaktif, 3) Kolaborasi dengan guru lain, dll"
                                          required>{{ old('aktivitas') }}</textarea>
                                @error('aktivitas') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><strong>Rekomendasi Supervisor</strong></label>
                                <textarea name="rekomendasi" class="form-control @error('rekomendasi') is-invalid @enderror" rows="2" 
                                          placeholder="Saran atau rekomendasi khusus dari supervisor untuk membantu pelaksanaan tindak lanjut">{{ old('rekomendasi') }}</textarea>
                                @error('rekomendasi') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Penanggung Jawab *</strong></label>
                                    <select name="penanggung_jawab_id" class="form-select @error('penanggung_jawab_id') is-invalid @enderror" required>
                                        <option value="">Pilih Guru</option>
                                        @foreach($guruList as $guru)
                                            <option value="{{ $guru->id }}" {{ old('penanggung_jawab_id') == $guru->id ? 'selected' : '' }}>
                                                {{ $guru->user->name ?? $guru->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('penanggung_jawab_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Target Selesai *</strong></label>
                                    <input type="date" name="target_selesai" class="form-control @error('target_selesai') is-invalid @enderror" 
                                           value="{{ old('target_selesai') }}" required>
                                    @error('target_selesai') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><strong>Status Awal</strong></label>
                                <select name="status" class="form-select">
                                    <option value="Belum Mulai" selected>Belum Mulai</option>
                                    <option value="Berjalan">Berjalan</option>
                                    <option value="Selesai">Selesai</option>
                                </select>
                            </div>

                            <div class="alert alert-info mt-3">
                                <strong>💡 Petunjuk Pembuatan Rencana Tindak Lanjut:</strong>
                                <ul class="mb-0 mt-2">
                                    <li><strong>Spesifik dan Terukur:</strong> Tujuan harus jelas dan dapat diukur keberhasilannya</li>
                                    <li><strong>Realistis:</strong> Aktivitas dapat dilakukan dengan sumber daya yang tersedia</li>
                                    <li><strong>Waktu Jelas:</strong> Target selesai harus realistis dan jelas</li>
                                    <li><strong>Akuntabel:</strong> Ada penanggung jawab yang jelas untuk setiap rencana</li>
                                    <li><strong>Terhubung dengan Feedback:</strong> Rencana harus mengacu pada area pengembangan dari post-conference</li>
                                </ul>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                + Tambah Rencana Tindak Lanjut
                            </button>
                        </form>
                    </div>
                </div>

                <div class="btn-group mt-4" role="group">
                    <a href="{{ route('supervisi.tindak-lanjut') }}" class="btn btn-secondary">← Kembali ke Daftar</a>
                    <a href="{{ route('supervisi.monitoring') }}" class="btn btn-primary">Lanjut ke Monitoring →</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card bg-light">
            <div class="card-header">
                <h5 class="card-title mb-0">📋 Fase Supervisi</h5>
            </div>
            <div class="card-body small">
                <div class="phase-item mb-3 pb-3 border-bottom">
                    <span class="badge bg-secondary">1</span>
                    <strong>Prasupervisi</strong><br>
                    <small class="text-muted">Perencanaan & kesepakatan</small>
                </div>
                <div class="phase-item mb-3 pb-3 border-bottom">
                    <span class="badge bg-secondary">2</span>
                    <strong>Observasi</strong><br>
                    <small class="text-muted">Pengamatan di kelas</small>
                </div>
                <div class="phase-item mb-3 pb-3 border-bottom">
                    <span class="badge bg-secondary">3</span>
                    <strong>Post-Conference</strong><br>
                    <small class="text-muted">Refleksi & feedback</small>
                </div>
                <div class="phase-item pb-3">
                    <span class="badge bg-primary">4</span>
                    <strong>Tindak Lanjut</strong><br>
                    <small class="text-muted">Perencanaan perbaikan ← Sekarang</small>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">📊 Ringkasan Supervisi</h5>
            </div>
            <div class="card-body small">
                <dl class="row">
                    <dt class="col-6">Guru:</dt>
                    <dd class="col-6">{{ $supervisi->guru->user->name ?? $supervisi->guru->nama }}</dd>

                    <dt class="col-6">Mapel:</dt>
                    <dd class="col-6">{{ $supervisi->mataPelajaran->nama_mapel ?? '-' }}</dd>

                    <dt class="col-6">Kelas:</dt>
                    <dd class="col-6">{{ $supervisi->kelas->nama_kelas ?? '-' }}</dd>

                    <dt class="col-6">Fokus:</dt>
                    <dd class="col-6">{{ Str::limit($supervisi->fokus ?? '-', 20) }}</dd>

                    <dt class="col-6">Status:</dt>
                    <dd class="col-6"><span class="badge bg-success">Selesai</span></dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
