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
                        <div class="col-md-12 mb-2">
                            <label class="form-label">Mata Pelajaran</label>
                            <input type="text" class="form-control" value="{{ $item->mataPelajaran->nama_mapel }}" disabled>
                        </div>

                        <div class="col-md-6 mb-2">
                            <label class="form-label">Kelas <span class="text-danger">*</span></label>
                            <div class="@error('kelas_id') is-invalid @enderror">
                                @forelse($kelas as $k)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="kelas_id" value="{{ $k->id }}" id="kelas_{{ $k->id }}" {{ $item->kelas_id == $k->id ? 'checked' : '' }}>
                                        <label class="form-check-label" for="kelas_{{ $k->id }}">
                                            {{ $k->nama_kelas }}
                                        </label>
                                    </div>
                                @empty
                                    <div class="alert alert-info alert-sm mb-0">
                                        <i class="ti ti-info-circle me-2"></i>Belum ada kelas untuk mata pelajaran ini
                                    </div>
                                @endforelse
                            </div>
                            @error('kelas_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-2">
                            <label class="form-label">Judul <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror" value="{{ old('judul', $item->judul) }}" required>
                            @error('judul')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="form-label">Deskripsi / Capaian Pembelajaran</label>
                            <textarea name="capaian_pembelajaran" class="form-control @error('capaian_pembelajaran') is-invalid @enderror" rows="3">{{ old('capaian_pembelajaran', $item->capaian_pembelajaran) }}</textarea>
                            @error('capaian_pembelajaran')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="form-label">Capaian Pembelajaran</label>
                            <select name="capaian_pembelajaran_id" id="capaianPembelajaranSelect" class="form-select @error('capaian_pembelajaran_id') is-invalid @enderror">
                                <option value="">-- Pilih Capaian Pembelajaran --</option>
                                @foreach($capaianPembelajaran as $cp)
                                <option value="{{ $cp->id }}" data-tujuan="{{ $cp->tujuan_pembelajaran }}" {{ $item->capaian_pembelajaran_id == $cp->id ? 'selected' : '' }}>
                                    [{{ $cp->fase ?? '-' }}] {{ $cp->nama_capaian_pembelajaran }}
                                </option>
                                @endforeach
                            </select>
                            @error('capaian_pembelajaran_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="form-label">Tujuan Pembelajaran</label>
                            <textarea name="tujuan" id="tujuanPembelajaranTextarea" class="form-control @error('tujuan') is-invalid @enderror" rows="3">{{ old('tujuan', $item->tujuan) }}</textarea>
                            @error('tujuan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="form-label">Metode Pembelajaran</label>
                            <textarea name="metode" class="form-control @error('metode') is-invalid @enderror" rows="2">{{ old('metode', $item->metode) }}</textarea>
                            @error('metode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="form-label">Media Pembelajaran</label>
                            <textarea name="media" class="form-control @error('media') is-invalid @enderror" rows="2">{{ old('media', $item->media) }}</textarea>
                            @error('media')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="form-label">Sumber Belajar</label>
                            <textarea name="sumber" class="form-control @error('sumber') is-invalid @enderror" rows="2">{{ old('sumber', $item->sumber) }}</textarea>
                            @error('sumber')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="form-label">Penilaian</label>
                            <textarea name="penilaian" class="form-control @error('penilaian') is-invalid @enderror" rows="2">{{ old('penilaian', $item->penilaian) }}</textarea>
                            @error('penilaian')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-2">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const capaianSelect = document.getElementById('capaianPembelajaranSelect');
    const tujuanTextarea = document.getElementById('tujuanPembelajaranTextarea');

    if (capaianSelect && tujuanTextarea) {
        capaianSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const tujuanValue = selectedOption.getAttribute('data-tujuan') || '';
            tujuanTextarea.value = tujuanValue;
        });

        // Auto-populate on page load if a Capaian Pembelajaran is already selected
        if (capaianSelect.value) {
            const selectedOption = capaianSelect.options[capaianSelect.selectedIndex];
            const tujuanValue = selectedOption.getAttribute('data-tujuan') || '';
            if (tujuanValue && !tujuanTextarea.value) {
                tujuanTextarea.value = tujuanValue;
            }
        }
    }
});
</script>
@endsection
