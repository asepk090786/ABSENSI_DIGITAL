@extends('layouts.app', ['pageSlug' => 'supervisi'])

@section('title', 'Edit Rencana Tindak Lanjut')

@section('content')
<div class="row">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Edit Rencana Tindak Lanjut</h4>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('supervisi.action-plan.update', $actionPlan) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="alert alert-info">
                        <strong>Guru:</strong> {{ $supervisi->guru->user->name ?? $supervisi->guru->nama }} | 
                        <strong>Mapel:</strong> {{ $supervisi->mataPelajaran->nama_mapel }} | 
                        <strong>Tanggal Supervisi:</strong> {{ $supervisi->tanggal->format('d-m-Y') }}
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Tujuan Tindak Lanjut *</strong></label>
                        <input type="text" name="tujuan" class="form-control @error('tujuan') is-invalid @enderror" 
                               value="{{ old('tujuan', $actionPlan->tujuan) }}" required>
                        @error('tujuan') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Aktivitas/Kegiatan yang Akan Dilakukan *</strong></label>
                        <textarea name="aktivitas" class="form-control @error('aktivitas') is-invalid @enderror" rows="3" required>{{ old('aktivitas', $actionPlan->aktivitas) }}</textarea>
                        @error('aktivitas') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Rekomendasi Supervisor</strong></label>
                        <textarea name="rekomendasi" class="form-control @error('rekomendasi') is-invalid @enderror" rows="2">{{ old('rekomendasi', $actionPlan->rekomendasi) }}</textarea>
                        @error('rekomendasi') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label"><strong>Penanggung Jawab *</strong></label>
                            <select name="penanggung_jawab_id" class="form-select @error('penanggung_jawab_id') is-invalid @enderror" required>
                                <option value="">Pilih Guru</option>
                                @foreach($guruList as $guru)
                                    <option value="{{ $guru->id }}" {{ $actionPlan->penanggung_jawab_id == $guru->id ? 'selected' : '' }}>
                                        {{ $guru->user->name ?? $guru->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('penanggung_jawab_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><strong>Target Selesai *</strong></label>
                            <input type="date" name="target_selesai" class="form-control @error('target_selesai') is-invalid @enderror" 
                                   value="{{ old('target_selesai', $actionPlan->target_selesai->format('Y-m-d')) }}" required>
                            @error('target_selesai') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Status</strong></label>
                        <select name="status" class="form-select">
                            <option value="Belum Mulai" {{ $actionPlan->status == 'Belum Mulai' ? 'selected' : '' }}>Belum Mulai</option>
                            <option value="Berjalan" {{ $actionPlan->status == 'Berjalan' ? 'selected' : '' }}>Berjalan</option>
                            <option value="Selesai" {{ $actionPlan->status == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="Ditunda" {{ $actionPlan->status == 'Ditunda' ? 'selected' : '' }}>Ditunda</option>
                            <option value="Dibatalkan" {{ $actionPlan->status == 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </div>

                    <hr>

                    <h5 class="mb-3">📊 Monitoring Progress</h5>
                    @if($actionPlan->monitorings->count() > 0)
                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Progress</th>
                                        <th>Catatan</th>
                                        <th>Bukti</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($actionPlan->monitorings as $monitoring)
                                        <tr>
                                            <td><small>{{ $monitoring->tanggal_monitoring->format('d-m-Y') }}</small></td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar" role="progressbar" 
                                                         style="width: {{ $monitoring->progress_persen }}%"
                                                         aria-valuenow="{{ $monitoring->progress_persen }}" 
                                                         aria-valuemin="0" aria-valuemax="100">
                                                        {{ $monitoring->progress_persen }}%
                                                    </div>
                                                </div>
                                            </td>
                                            <td><small>{{ Str::limit($monitoring->catatan, 30) }}</small></td>
                                            <td>
                                                @if($monitoring->bukti)
                                                    <a href="{{ asset('storage/' . $monitoring->bukti) }}" target="_blank" class="btn btn-xs btn-info">
                                                        <i class="fas fa-file"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <div class="card border-light mb-3">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">+ Tambah Monitoring Baru</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('supervisi.monitoring.store', $actionPlan) }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Tanggal Monitoring</label>
                                        <input type="date" name="tanggal_monitoring" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Progress (%)</label>
                                        <input type="number" name="progress_persen" class="form-control" min="0" max="100" value="0" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Upload Bukti</label>
                                        <input type="file" name="bukti" class="form-control">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Catatan Monitoring</label>
                                    <textarea name="catatan" class="form-control" rows="2" placeholder="Perkembangan, hambatan, atau catatan penting lainnya" required></textarea>
                                </div>

                                <button type="submit" class="btn btn-sm btn-success">
                                    ✓ Simpan Monitoring
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="btn-group mt-4" role="group">
                        <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
                        <a href="{{ route('supervisi.action-plan.show', $supervisi) }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
