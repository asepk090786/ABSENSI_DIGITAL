@extends('layouts.app', ['pageSlug' => 'rencana_pembelajaran'])

@section('title', 'Edit Rencana Pembelajaran')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header border-0 pt-3 pb-2">
                <h4 class="card-title">Edit Rencana Pembelajaran</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('rencana_pembelajaran.update', $item->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <h5 class="mb-2">1. Informasi Umum</h5>
                            <div class="mb-2">
                                <label class="form-label">Mata Pelajaran</label>
                                <input type="text" class="form-control" value="{{ $item->mataPelajaran->nama_mapel }}" disabled>
                            </div>
                            <div class="mb-2 @error('kelas_id') is-invalid @enderror">
                                <label class="form-label">Kelas <span class="text-danger">*</span></label>
                                @forelse($kelas as $k)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="kelas_id" value="{{ $k->id }}" id="kelas_{{ $k->id }}" {{ old('kelas_id', $item->kelas_id) == $k->id ? 'checked' : '' }}>
                                        <label class="form-check-label" for="kelas_{{ $k->id }}">{{ $k->nama_kelas }}</label>
                                    </div>
                                @empty
                                    <div class="alert alert-info alert-sm mb-0">
                                        <i class="ti ti-info-circle me-2"></i>Belum ada kelas untuk mata pelajaran ini
                                    </div>
                                @endforelse
                                @error('kelas_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Judul <span class="text-danger">*</span></label>
                                <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror" value="{{ old('judul', $item->judul) }}" required>
                                @error('judul')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="card card-body p-3 border-0 shadow-sm">
                                <h5 class="mb-2">2. Capaian Pembelajaran</h5>
                                <label class="form-label">Capaian Pembelajaran</label>
                                <textarea name="capaian_pembelajaran" class="form-control tiny-editor @error('capaian_pembelajaran') is-invalid @enderror" rows="4">{{ old('capaian_pembelajaran', $item->capaian_pembelajaran) }}</textarea>
                                <div class="form-text text-muted">
                                    Tuliskan Capaian pembelajaran untuk masing-masing mapel berdasarkan Kep BSKAP 046/2025 (bagi mapel umum) dan Kep BKPDM 020/2026 (bagi mapel PAI dan Budi Pekerti).
                                </div>
                                @error('capaian_pembelajaran')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="card card-body p-3 border-0 shadow-sm">
                                <h5 class="mb-2">3. Tujuan Pembelajaran</h5>
                                <label class="form-label">Tujuan Pembelajaran</label>
                                <textarea name="tujuan" class="form-control tiny-editor @error('tujuan') is-invalid @enderror" rows="4">{{ old('tujuan', $item->tujuan) }}</textarea>
                                <div class="form-text text-muted">
                                    Sebutkan Tujuan pembelajaran yang mengacu pada capaian pembelajaran.
                                </div>
                                @error('tujuan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="card card-body p-3 border-0 shadow-sm">
                                <h5 class="mb-2">4. Praktik Pedagogis</h5>
                                <label class="form-label">Praktik Pedagogis</label>
                                <textarea name="praktik_pedagogis" class="form-control tiny-editor @error('praktik_pedagogis') is-invalid @enderror" rows="4">{{ old('praktik_pedagogis', $item->praktik_pedagogis) }}</textarea>
                                <div class="form-text text-muted">
                                    Jelaskan metode dan model pembelajaran yang akan digunakan.
                                </div>
                                @error('praktik_pedagogis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="card card-body p-3 border-0 shadow-sm">
                                <h5 class="mb-2">5. Lingkungan Pembelajaran</h5>
                                <label class="form-label">Lingkungan Pembelajaran</label>
                                <textarea name="lingkungan_pembelajaran" class="form-control tiny-editor @error('lingkungan_pembelajaran') is-invalid @enderror" rows="4">{{ old('lingkungan_pembelajaran', $item->lingkungan_pembelajaran) }}</textarea>
                                <div class="form-text text-muted">
                                    Jelaskan ruang fisik, ruang virtual, dan budaya belajar.
                                </div>
                                @error('lingkungan_pembelajaran')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="card card-body p-3 border-0 shadow-sm">
                                <h5 class="mb-2">6. Pemanfaatan Digital</h5>
                                <label class="form-label">Pemanfaatan Digital</label>
                                <textarea name="pemanfaatan_digital" class="form-control tiny-editor @error('pemanfaatan_digital') is-invalid @enderror" rows="4">{{ old('pemanfaatan_digital', $item->pemanfaatan_digital) }}</textarea>
                                <div class="form-text text-muted">
                                    Sebutkan referensi buku, link, atau sumber lain yang digunakan.
                                </div>
                                @error('pemanfaatan_digital')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="card card-body p-3 border-0 shadow-sm">
                                <h5 class="mb-2">7. Pengalaman Pembelajaran</h5>
                                <label class="form-label">Pengalaman Pembelajaran</label>
                                <textarea name="pengalaman_pembelajaran" class="form-control tiny-editor @error('pengalaman_pembelajaran') is-invalid @enderror" rows="4">{{ old('pengalaman_pembelajaran', $item->pengalaman_pembelajaran) }}</textarea>
                                <div class="form-text text-muted">
                                    Sebutkan gambaran singkat kegiatan pendahuluan, inti, dan penutup.
                                </div>
                                @error('pengalaman_pembelajaran')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="card card-body p-3 border-0 shadow-sm">
                                <h5 class="mb-2">8. Refleksi Pembelajaran</h5>
                                <label class="form-label">Refleksi Pembelajaran</label>
                                <textarea name="refleksi_pembelajaran" class="form-control tiny-editor @error('refleksi_pembelajaran') is-invalid @enderror" rows="4">{{ old('refleksi_pembelajaran', $item->refleksi_pembelajaran) }}</textarea>
                                <div class="form-text text-muted">
                                    Sebutkan refleksi pembelajaran (opsional) jika dilakukan refleksi.
                                </div>
                                @error('refleksi_pembelajaran')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="card card-body p-3 border-0 shadow-sm">
                                <h5 class="mb-2">9. Asesmen</h5>
                                <label class="form-label">Deskripsi Asesmen</label>
                                <textarea name="penilaian" class="form-control tiny-editor @error('penilaian') is-invalid @enderror" rows="4">{{ old('penilaian', $item->penilaian) }}</textarea>
                                <div class="form-text text-muted">
                                    Sebutkan bentuk instrumen (Lembar Kerja Murid) dan kriteria asesmen kognitif / psikomotorik / afektif.
                                </div>
                                @error('penilaian')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Komponen Penilaian</label>
                            <div class="@error('komponen_nilai_ids') is-invalid @enderror">
                                @forelse($komponenNilai as $komponen)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="komponen_nilai_ids[]" value="{{ $komponen->id }}" id="komponen_{{ $komponen->id }}" {{ in_array($komponen->id, old('komponen_nilai_ids', $selectedKomponenIds ?? [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="komponen_{{ $komponen->id }}">
                                            {{ $komponen->nama_komponen }}
                                            @if($komponen->bobot)
                                                <span class="text-muted">({{ $komponen->bobot }}%)</span>
                                            @endif
                                        </label>
                                    </div>
                                @empty
                                    <div class="alert alert-info alert-sm mb-0">
                                        <i class="ti ti-info-circle me-2"></i>Belum ada komponen penilaian
                                    </div>
                                @endforelse
                            </div>
                            @error('komponen_nilai_ids')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-2">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="draft" {{ old('status', $item->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status', $item->status) === 'published' ? 'selected' : '' }}>Published</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-footer">
                        <a href="{{ route('rencana_pembelajaran.index', ['mata_pelajaran_id' => $item->mata_pelajaran_id, 'tingkat' => $item->kelas->tingkat_kelas]) }}" class="btn btn-link">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>Perbarui
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.7.0/tinymce.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: 'textarea.tiny-editor',
                plugins: 'lists link image table code help wordcount',
                toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image table | code help',
                height: 320,
                menubar: false,
                statusbar: true,
                license_key: 'gpl',
                content_style: 'body { color: #212529; font-family: inherit; }'
            });
        }
    });
</script>
@endpush
@endsection

