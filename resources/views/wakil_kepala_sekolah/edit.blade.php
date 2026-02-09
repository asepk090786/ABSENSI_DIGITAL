@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Data Wakil Kepala Sekolah</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('wakil_kepala_sekolah.update', $wakil->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Pilih Guru (Opsional)</label>
                                    <select name="guru_id" class="form-select @error('guru_id') is-invalid @enderror">
                                        <option value="">-- Pilih Guru atau Isi Manual --</option>
                                        @forelse($guru as $g)
                                            <option value="{{ $g->id }}" {{ old('guru_id', $wakil->guru_id) == $g->id ? 'selected' : '' }}>
                                                {{ $g->nama }} @if($g->nip)({{ $g->nip }})@endif
                                            </option>
                                        @empty
                                            <option value="" disabled>Semua guru sudah menjadi Wakil Kepala Sekolah</option>
                                        @endforelse
                                    </select>
                                    @error('guru_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted d-block mt-1">Jika memilih guru, data nama, NIP akan diambil dari data guru tersebut</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama <span class="text-danger">*</span></label>
                                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $wakil->nama) }}" required>
                                    @error('nama')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">NIP</label>
                                    <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror" value="{{ old('nip', $wakil->nip) }}">
                                    @error('nip')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Jenis Tugas <span class="text-danger">*</span></label>
                                    <select name="jenis_tugas_wakil" class="form-select @error('jenis_tugas_wakil') is-invalid @enderror" required>
                                        <option value="">Pilih Jenis Tugas</option>
                                        <option value="Bidang Kurikulum" {{ old('jenis_tugas_wakil', $wakil->jenis_tugas_wakil) == 'Bidang Kurikulum' ? 'selected' : '' }}>Bidang Kurikulum</option>
                                        <option value="Bidang Sarana dan Prasarana" {{ old('jenis_tugas_wakil', $wakil->jenis_tugas_wakil) == 'Bidang Sarana dan Prasarana' ? 'selected' : '' }}>Bidang Sarana dan Prasarana</option>
                                        <option value="Bidang Humas" {{ old('jenis_tugas_wakil', $wakil->jenis_tugas_wakil) == 'Bidang Humas' ? 'selected' : '' }}>Bidang Humas</option>
                                        <option value="Bidang Kesiswaan" {{ old('jenis_tugas_wakil', $wakil->jenis_tugas_wakil) == 'Bidang Kesiswaan' ? 'selected' : '' }}>Bidang Kesiswaan</option>
                                    </select>
                                    @error('jenis_tugas_wakil')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="">Pilih Status</option>
                                        <option value="Aktif" {{ old('status', $wakil->status) == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="Tidak Aktif" {{ old('status', $wakil->status) == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Foto</label>
                                    <input type="file" name="foto" class="form-control @error('foto') is-invalid @enderror" accept="image/*">
                                    @error('foto')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if($wakil->foto)
                                        <img src="{{ asset('storage/' . $wakil->foto) }}" alt="Foto" class="mt-2 rounded" style="max-height: 100px;">
                                    @endif
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Alamat</label>
                                    <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3">{{ old('alamat', $wakil->alamat) }}</textarea>
                                    @error('alamat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Telepon</label>
                                    <input type="text" name="telepon" class="form-control @error('telepon') is-invalid @enderror" value="{{ old('telepon', $wakil->telepon) }}">
                                    @error('telepon')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $wakil->email) }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy"></i> Simpan
                            </button>
                            <a href="{{ route('wakil_kepala_sekolah.index') }}" class="btn btn-secondary">
                                <i class="ti ti-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const guruSelect = document.querySelector('select[name="guru_id"]');
    const guruData = {!! json_encode($guru->mapWithKeys(function($item) {
        return [$item->id => ['nama' => $item->nama, 'nip' => $item->nip]];
    })->all()) !!};

    if (guruSelect) {
        guruSelect.addEventListener('change', function() {
            const guruId = this.value;
            if (guruId && guruData[guruId]) {
                const data = guruData[guruId];
                document.querySelector('input[name="nama"]').value = data.nama;
                document.querySelector('input[name="nip"]').value = data.nip || '';
            }
        });
    }
});
</script>
@endsection
