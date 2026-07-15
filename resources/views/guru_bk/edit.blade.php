@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0 pt-3 pb-2">
                    <h3 class="card-title">Edit Data Guru BK</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('guru_bk.update', $gurubk->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-2">
                                    <label class="form-label">Pilih Guru (Opsional)</label>
                                    <select name="guru_id" class="form-control @error('guru_id') is-invalid @enderror">
                                        <option value="">-- Pilih Guru atau Isi Manual --</option>
                                        @forelse($guru as $g)
                                            <option value="{{ $g->id }}" {{ old('guru_id', $gurubk->guru_id) == $g->id ? 'selected' : '' }}>
                                                {{ $g->nama }} @if($g->nip)({{ $g->nip }})@endif{{ $g->user ? '' : ' - akun belum terhubung' }}
                                            </option>
                                        @empty
                                            <option value="" disabled>Semua guru sudah menjadi Guru BK</option>
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
                                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $gurubk->nama) }}" required>
                                    @error('nama')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="form-label">NIP</label>
                                    <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror" value="{{ old('nip', $gurubk->nip) }}">
                                    @error('nip')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                                        <option value="">Pilih Status</option>
                                        <option value="Aktif" {{ old('status', $gurubk->is_active ? 'Aktif' : 'Tidak Aktif') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="Tidak Aktif" {{ old('status', $gurubk->is_active ? 'Aktif' : 'Tidak Aktif') == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="form-label">Pilihan Kelas Binaan</label>
                                    @php
                                        $selectedKelasBinaan = old('kelas_binaan', $kelasBinaanIds ?? []);
                                    @endphp
                                    <select name="kelas_binaan[]" class="form-control @error('kelas_binaan') is-invalid @enderror @error('kelas_binaan.*') is-invalid @enderror" multiple size="5">
                                        @foreach($kelasList as $kelas)
                                            <option value="{{ $kelas->id }}" {{ in_array($kelas->id, $selectedKelasBinaan) ? 'selected' : '' }}>
                                                {{ $kelas->nama_kelas }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('kelas_binaan')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    @error('kelas_binaan.*')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted d-block mt-1">Tekan Ctrl (Windows/Linux) untuk memilih lebih dari satu kelas.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-2">
                                    <label class="form-label">Foto</label>
                                    <input type="file" name="foto" class="form-control @error('foto') is-invalid @enderror" accept="image/*">
                                    @error('foto')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if($gurubk->foto)
                                        <img src="{{ asset('storage/' . $gurubk->foto) }}" alt="Foto" class="mt-2 rounded" style="max-height: 120px;">
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-2">
                                    <label class="form-label">Alamat</label>
                                    <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3">{{ old('alamat', $gurubk->alamat) }}</textarea>
                                    @error('alamat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="form-label">Telepon</label>
                                    <input type="text" name="telepon" class="form-control @error('telepon') is-invalid @enderror" value="{{ old('telepon', $gurubk->telepon) }}">
                                    @error('telepon')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $gurubk->email) }}">
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
