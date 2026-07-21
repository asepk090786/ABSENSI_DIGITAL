@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0 pt-3 pb-2">
                    <h3 class="card-title">Tambah Data Guru BK</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('guru_bk.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="form-label">Pilih Guru <span class="text-danger">*</span></label>
                                    <select name="guru_id" class="form-select @error('guru_id') is-invalid @enderror" required>
                                        <option value="">-- Pilih Guru --</option>
                                        @forelse($guru as $g)
                                            <option value="{{ $g->id }}" {{ old('guru_id') == $g->id ? 'selected' : '' }}>
                                                {{ $g->nama }} @if($g->nip)({{ $g->nip }})@endif{{ $g->user ? '' : ' - akun belum terhubung' }}
                                            </option>
                                        @empty
                                            <option value="" disabled>Tidak ada guru yang tersedia</option>
                                        @endforelse
                                    </select>
                                    @error('guru_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted d-block mt-1">Jika memilih guru, data nama, NIP, dan email akan diambil dari data guru tersebut</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="form-label">Nama <span class="text-danger">*</span></label>
                                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama') }}" required>
                                    @error('nama')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="form-label">NIP</label>
                                    <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror" value="{{ old('nip') }}">
                                    @error('nip')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="">Pilih Status</option>
                                        <option value="Aktif" {{ old('status') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="Tidak Aktif" {{ old('status') == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="form-label">Pilihan Kelas Binaan</label>
                                    <div class="border rounded p-3" style="max-height: 280px; overflow-y: auto;">
                                        @foreach($kelasList as $kelas)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="kelas_binaan[]" value="{{ $kelas->id }}" id="kelas_binaan_{{ $kelas->id }}" {{ in_array($kelas->id, old('kelas_binaan', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="kelas_binaan_{{ $kelas->id }}">
                                                    {{ $kelas->nama_kelas }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('kelas_binaan')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    @error('kelas_binaan.*')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted d-block mt-1">Centang semua kelas binaan yang ingin ditetapkan untuk Guru BK ini.</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="form-label">Foto</label>
                                    <input type="file" name="foto" class="form-control @error('foto') is-invalid @enderror" accept="image/*">
                                    @error('foto')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-2">
                                    <label class="form-label">Alamat</label>
                                    <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3">{{ old('alamat') }}</textarea>
                                    @error('alamat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="form-label">Telepon</label>
                                    <input type="text" name="telepon" class="form-control @error('telepon') is-invalid @enderror" value="{{ old('telepon') }}">
                                    @error('telepon')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy"></i> Simpan
                            </button>
                            <a href="{{ route('guru_bk.index') }}" class="btn btn-secondary">
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
        return [$item->id => ['nama' => $item->nama, 'nip' => $item->nip, 'email' => $item->email]];
    })->all()) !!};

    if (guruSelect) {
        guruSelect.addEventListener('change', function() {
            const guruId = this.value;
            if (guruId && guruData[guruId]) {
                const data = guruData[guruId];
                document.querySelector('input[name="nama"]').value = data.nama;
                document.querySelector('input[name="nip"]').value = data.nip || '';
                document.querySelector('input[name="email"]').value = data.email || '';
            }
        });
    }
});
</script>
@endsection
