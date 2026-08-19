@extends('layouts.app', ['pageSlug' => 'supervisi'])

@section('title', 'Post-Conference & Feedback')

@section('content')
<div class="row">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0">Post-Conference & Feedback Supervisi</h4>
                        <small class="text-muted d-block mt-1">Guru: {{ $supervisi->guru->user->name ?? $supervisi->guru->nama }} | {{ $supervisi->tanggal->format('d-m-Y') }}</small>
                    </div>
                    <span class="badge bg-success">Selesai</span>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('supervisi.post-conference.store', $supervisi) }}" method="POST">
                    @csrf

                    <div class="alert alert-primary">
                        <strong>ℹ️ Post-Conference</strong> adalah pertemuan reflektif setelah observasi untuk membahas temuan dan memberikan umpan balik konstruktif.
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label"><strong>Tanggal Pelaksanaan Post-Conference</strong></label>
                            <input type="datetime-local" name="tanggal_pelaksanaan" class="form-control" 
                                   value="{{ old('tanggal_pelaksanaan', $postConference->tanggal_pelaksanaan?->format('Y-m-d H:i')) ?? now()->format('Y-m-d H:i') }}">
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">🤝 Refleksi Bersama</h5>

                    <div class="mb-4">
                        <label class="form-label"><strong>Refleksi Guru</strong></label>
                        <p class="text-muted small">Guru merefleksikan pembelajaran apa yang sudah dilakukan dan apa yang bisa ditingkatkan</p>
                        <textarea name="refleksi_guru" class="form-control @error('refleksi_guru') is-invalid @enderror" rows="4" 
                                  placeholder="Apa yang sudah Anda lakukan dengan baik? Apa yang masih perlu ditingkatkan? Bagaimana respon murid?">{{ old('refleksi_guru', $postConference->refleksi_guru) }}</textarea>
                        @error('refleksi_guru') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><strong>Refleksi Supervisor</strong></label>
                        <p class="text-muted small">Supervisor berbagi observasi dan analisis tentang pembelajaran yang disaksikan</p>
                        <textarea name="refleksi_supervisor" class="form-control @error('refleksi_supervisor') is-invalid @enderror" rows="4" 
                                  placeholder="Apa yang Anda amati selama observasi? Temuan penting apa yang perlu dibahas lebih lanjut?">{{ old('refleksi_supervisor', $postConference->refleksi_supervisor) }}</textarea>
                        @error('refleksi_supervisor') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <hr>

                    <h5 class="mb-3">💬 Umpan Balik (Feedback)</h5>

                    <div class="mb-4">
                        <label class="form-label"><strong>Kekuatan Guru</strong></label>
                        <p class="text-muted small">Aspek-aspek positif dan kekuatan yang perlu dipertahankan atau dikembangkan lebih lanjut</p>
                        <textarea name="kekuatan" class="form-control @error('kekuatan') is-invalid @enderror" rows="3" 
                                  placeholder="Contoh: Penguasaan konten yang kuat, Manajemen waktu efektif, Komunikasi yang jelas, dll">{{ old('kekuatan', $postConference->feedback?->kekuatan) }}</textarea>
                        @error('kekuatan') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><strong>Area Pengembangan</strong></label>
                        <p class="text-muted small">Aspek-aspek yang perlu ditingkatkan atau dikembangkan lebih lanjut</p>
                        <textarea name="area_pengembangan" class="form-control @error('area_pengembangan') is-invalid @enderror" rows="3" 
                                  placeholder="Contoh: Penggunaan media pembelajaran, Teknik bertanya yang lebih variatif, Penguatan motivasi siswa, dll">{{ old('area_pengembangan', $postConference->feedback?->area_pengembangan) }}</textarea>
                        @error('area_pengembangan') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><strong>Umpan Balik Keseluruhan</strong></label>
                        <p class="text-muted small">Catatan kesimpulan dan rekomendasi umum dari supervisor</p>
                        <textarea name="umpan_balik" class="form-control @error('umpan_balik') is-invalid @enderror" rows="3" 
                                  placeholder="Kesimpulan akhir dan saran spesifik untuk pengembangan guru ke depan">{{ old('umpan_balik', $supervisi->umpan_balik) }}</textarea>
                        @error('umpan_balik') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="alert alert-info mt-4">
                        <strong>💡 Petunjuk Feedback Konstruktif:</strong>
                        <ul class="mb-0 mt-2">
                            <li><strong>Spesifik:</strong> Referensikan pada observasi konkret, bukan penilaian umum</li>
                            <li><strong>Seimbang:</strong> Bahas kekuatan dan area pengembangan secara berimbang</li>
                            <li><strong>Actionable:</strong> Berikan rekomendasi konkret dan tindakan nyata</li>
                            <li><strong>Supportif:</strong> Posisikan sebagai mitra dalam peningkatan profesional</li>
                            <li><strong>Growth-oriented:</strong> Fokus pada pengembangan dan pembelajaran berkelanjutan</li>
                        </ul>
                    </div>

                    <div class="btn-group mt-4" role="group">
                        <button type="submit" class="btn btn-success">✓ Simpan Post-Conference</button>
                        <a href="{{ route('supervisi.pascasupervisi') }}" class="btn btn-secondary">Kembali</a>
                        <button type="button" class="btn btn-primary" onclick="setTimeout(() => location.href='{{ route('supervisi.action-plan.show', $supervisi) }}', 500)">
                            Lanjut ke Rencana Tindak Lanjut →
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
