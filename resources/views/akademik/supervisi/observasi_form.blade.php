@extends('layouts.app', ['pageSlug' => 'supervisi'])

@section('title', 'Formulir Observasi')

@section('content')
<div class="row">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0">Formulir Observasi Pembelajaran</h4>
                        <small class="text-muted d-block mt-1">Guru: {{ $supervisi->guru->user->name ?? $supervisi->guru->nama }} | Kelas: {{ $supervisi->kelas->nama_kelas }} | {{ $supervisi->tanggal->format('d-m-Y') }}</small>
                    </div>
                    <span class="badge bg-warning">Berlangsung</span>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('supervisi.observasi.store', $supervisi) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <h5 class="mb-3">📝 Catatan Objektif Pengamatan</h5>
                    <div class="mb-4">
                        <textarea name="catatan_objektif" class="form-control @error('catatan_objektif') is-invalid @enderror" rows="5" 
                                  placeholder="Tuliskan data/fakta observasi yang Anda amati secara objektif (apa yang Anda lihat, dengar, dan amati).&#10;Hindari penilaian atau interpretasi - fokus pada deskripsi perilaku konkret.">{{ old('catatan_objektif', $supervisi->catatan_objektif) }}</textarea>
                        @error('catatan_objektif') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <hr>

                    <h5 class="mb-3">📋 Penilaian Indikator</h5>
                    <p class="text-muted small">Berikan skor untuk setiap indikator yang relevan. Skala: 1=Belum, 2=Kurang, 3=Cukup, 4=Baik, 5=Sangat Baik</p>

                    @forelse($instruments as $instrument)
                        <div class="card mb-3 border">
                            <div class="card-header bg-light">
                                <strong>{{ $instrument->nama }}</strong>
                                <span class="badge bg-secondary float-end">{{ ucfirst($instrument->tipe) }}</span>
                            </div>
                            <div class="card-body">
                                @forelse($instrument->indicators as $indicator)
                                    <div class="mb-4 pb-3 border-bottom">
                                        <label class="form-label"><strong>{{ $indicator->indikator }}</strong></label>
                                        <p class="text-muted small">{{ $indicator->deskripsi }}</p>

                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <select name="observation_items[{{ $indicator->id }}][skor]" class="form-select form-select-sm">
                                                    <option value="">Pilih Skor</option>
                                                    <option value="1">1 - Belum</option>
                                                    <option value="2">2 - Kurang</option>
                                                    <option value="3">3 - Cukup</option>
                                                    <option value="4">4 - Baik</option>
                                                    <option value="5">5 - Sangat Baik</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mt-2">
                                            <textarea name="observation_items[{{ $indicator->id }}][catatan]" class="form-control form-control-sm" rows="2" 
                                                      placeholder="Catatan khusus untuk indikator ini (opsional)"></textarea>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted">Belum ada indikator</p>
                                @endforelse
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-warning">
                            Belum ada instrumen. <a href="{{ route('supervisi.instrumen.create') }}">Buat instrumen terlebih dahulu</a>
                        </div>
                    @endforelse

                    <hr class="my-4">

                    <h5 class="mb-3">📸 Bukti Pendukung</h5>
                    <p class="text-muted small">Upload foto, video, atau dokumen pendukung observasi (maksimal 10MB per file)</p>

                    <div id="evidenceContainer" class="mb-3">
                        <div class="evidence-row mb-3 p-3 border rounded">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Jenis</label>
                                    <select name="evidences[0][jenis]" class="form-select form-select-sm">
                                        <option value="dokumen">Dokumen</option>
                                        <option value="foto">Foto</option>
                                        <option value="video">Video</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">File</label>
                                    <input type="file" name="evidences[0][file]" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Keterangan</label>
                                    <input type="text" name="evidences[0][keterangan]" class="form-control form-control-sm" placeholder="Deskripsi bukti">
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-secondary mb-4" onclick="addEvidence()">
                        + Tambah Bukti
                    </button>

                    <div class="alert alert-info mt-4">
                        <strong>💡 Petunjuk Pengisian:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Catatan objektif harus berbasis fakta, bukan penilaian subjektif</li>
                            <li>Skor hanya untuk indikator yang benar-benar teramati</li>
                            <li>Bukti pendukung sangat membantu dalam post-conference</li>
                            <li>Pastikan semua data tersimpan sebelum lanjut ke post-conference</li>
                        </ul>
                    </div>

                    <div class="btn-group mt-4" role="group">
                        <button type="submit" class="btn btn-success">✓ Simpan Observasi</button>
                        <a href="{{ route('supervisi.pelaksanaan') }}" class="btn btn-secondary">Kembali</a>
                        <button type="button" class="btn btn-primary" onclick="setTimeout(() => location.href='{{ route('supervisi.post-conference.show', $supervisi) }}', 500)">
                            Lanjut ke Post-Conference →
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let evidenceCount = 1;
function addEvidence() {
    const container = document.getElementById('evidenceContainer');
    const newRow = document.createElement('div');
    newRow.className = 'evidence-row mb-3 p-3 border rounded';
    newRow.innerHTML = `
        <div class="row">
            <div class="col-md-3">
                <label class="form-label">Jenis</label>
                <select name="evidences[${evidenceCount}][jenis]" class="form-select form-select-sm">
                    <option value="dokumen">Dokumen</option>
                    <option value="foto">Foto</option>
                    <option value="video">Video</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">File</label>
                <input type="file" name="evidences[${evidenceCount}][file]" class="form-control form-control-sm">
            </div>
            <div class="col-md-4">
                <label class="form-label">Keterangan</label>
                <input type="text" name="evidences[${evidenceCount}][keterangan]" class="form-control form-control-sm" placeholder="Deskripsi bukti">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-sm btn-danger w-100" onclick="this.parentElement.parentElement.parentElement.remove()">
                    ✕
                </button>
            </div>
        </div>
    `;
    container.appendChild(newRow);
    evidenceCount++;
}
</script>
@endsection
