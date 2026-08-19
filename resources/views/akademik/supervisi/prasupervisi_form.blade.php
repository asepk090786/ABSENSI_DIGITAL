@extends('layouts.app', ['pageSlug' => 'supervisi'])

@section('title', 'Edit Prasupervisi')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title mb-0">Prasupervisi</h4>
                    <small class="text-muted d-block mt-1">Guru: {{ $supervisi->guru->user->name ?? $supervisi->guru->nama }}</small>
                </div>
                <span class="badge bg-info">{{ $supervisi->status ?? 'Draft' }}</span>
            </div>
            <div class="card-body">
                <form action="{{ route('supervisi.prasupervisi.update', $supervisi) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Mata Pelajaran</label>
                            <div class="form-control-plaintext">
                                <strong>{{ $supervisi->mataPelajaran->nama_mapel ?? '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kelas</label>
                            <div class="form-control-plaintext">
                                <strong>{{ $supervisi->kelas->nama_kelas ?? '-' }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Supervisi</label>
                            <div class="form-control-plaintext">
                                <strong>{{ $supervisi->tanggal->format('d-m-Y') ?? '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jam Ke</label>
                            <div class="form-control-plaintext">
                                <strong>{{ $supervisi->jam_ke ?? '-' }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Supervisor</label>
                        <select name="supervisor_id" class="form-select @error('supervisor_id') is-invalid @enderror" required>
                            <option value="">Pilih Supervisor</option>
                            @foreach($supervisi->guru()->with('user')->get() as $guru)
                                <option value="{{ $guru->id }}" {{ $supervisi->supervisor_id == $guru->id ? 'selected' : '' }}>
                                    {{ $guru->user->name ?? $guru->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('supervisor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tujuan Supervisi</label>
                        <textarea name="tujuan" class="form-control @error('tujuan') is-invalid @enderror" rows="3" required>{{ old('tujuan', $supervisi->tujuan) }}</textarea>
                        <small class="text-muted">Jelaskan tujuan umum supervisi ini (misal: meningkatkan keterampilan mengajar, membimbing kurikulum baru, dll)</small>
                        @error('tujuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fokus Supervisi</label>
                        <select name="fokus" class="form-select @error('fokus') is-invalid @enderror" required>
                            <option value="">Pilih Fokus</option>
                            @foreach($fokusOptions as $fokus)
                                <option value="{{ $fokus }}" {{ $supervisi->fokus == $fokus ? 'selected' : '' }}>
                                    {{ $fokus }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Pilih aspek yang akan menjadi fokus observasi utama</small>
                        @error('fokus') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="Draft" {{ $supervisi->status == 'Draft' ? 'selected' : '' }}>Draft</option>
                            <option value="Terjadwal" {{ $supervisi->status == 'Terjadwal' ? 'selected' : '' }}>Terjadwal</option>
                            <option value="Berlangsung" {{ $supervisi->status == 'Berlangsung' ? 'selected' : '' }}>Berlangsung</option>
                            <option value="Selesai" {{ $supervisi->status == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="Dibatalkan" {{ $supervisi->status == 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="alert alert-info">
                        <strong>💡 Tip Prasupervisi:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Jelaskan tujuan supervisi dengan spesifik dan SMART (Specific, Measurable, Achievable, Relevant, Time-bound)</li>
                            <li>Fokus harus selaras dengan kebutuhan pengembangan guru</li>
                            <li>Status "Terjadwal" berarti sudah disepakati, siap untuk observasi</li>
                        </ul>
                    </div>

                    <div class="btn-group mt-4" role="group">
                        <button type="submit" class="btn btn-primary">💾 Simpan Prasupervisi</button>
                        <a href="{{ route('supervisi.prasupervisi') }}" class="btn btn-secondary">Batal</a>
                        @if($supervisi->status == 'Terjadwal')
                            <a href="{{ route('supervisi.observasi.show', $supervisi) }}" class="btn btn-success">Mulai Observasi →</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card bg-light">
            <div class="card-header">
                <h5 class="card-title mb-0">Informasi Supervisi</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-6">Guru:</dt>
                    <dd class="col-sm-6">{{ $supervisi->guru->user->name ?? $supervisi->guru->nama }}</dd>

                    <dt class="col-sm-6">Mapel:</dt>
                    <dd class="col-sm-6">{{ $supervisi->mataPelajaran->nama_mapel ?? '-' }}</dd>

                    <dt class="col-sm-6">Kelas:</dt>
                    <dd class="col-sm-6">{{ $supervisi->kelas->nama_kelas ?? '-' }}</dd>

                    <dt class="col-sm-6">Tanggal:</dt>
                    <dd class="col-sm-6">{{ $supervisi->tanggal->format('d M Y') }}</dd>

                    <dt class="col-sm-6">Status:</dt>
                    <dd class="col-sm-6"><span class="badge bg-primary">{{ $supervisi->status ?? 'Draft' }}</span></dd>
                </dl>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">📋 Alur Supervisi</h5>
            </div>
            <div class="card-body small">
                <div class="progress-step mb-3">
                    <div class="progress-item active">
                        <div class="progress-marker">✓</div>
                        <div class="progress-content">
                            <strong>Prasupervisi</strong><br>
                            <small class="text-muted">Perencanaan & kesepakatan</small>
                        </div>
                    </div>
                    <div class="progress-item">
                        <div class="progress-marker">2</div>
                        <div class="progress-content">
                            <strong>Observasi</strong><br>
                            <small class="text-muted">Pengamatan di kelas</small>
                        </div>
                    </div>
                    <div class="progress-item">
                        <div class="progress-marker">3</div>
                        <div class="progress-content">
                            <strong>Post-Conference</strong><br>
                            <small class="text-muted">Refleksi & feedback</small>
                        </div>
                    </div>
                    <div class="progress-item">
                        <div class="progress-marker">4</div>
                        <div class="progress-content">
                            <strong>Tindak Lanjut</strong><br>
                            <small class="text-muted">Perencanaan perbaikan</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.progress-step { list-style: none; padding: 0; }
.progress-item { display: flex; gap: 12px; margin-bottom: 16px; }
.progress-marker { 
    width: 32px; height: 32px; border-radius: 50%;
    background: #e9ecef; border: 2px solid #dee2e6;
    display: flex; align-items: center; justify-content: center;
    font-weight: bold; font-size: 12px;
    flex-shrink: 0;
}
.progress-item.active .progress-marker { background: #0d6efd; color: white; border-color: #0d6efd; }
</style>
@endsection
