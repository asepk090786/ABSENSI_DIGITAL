@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ isset($sekolah) ? 'Edit' : 'Tambah' }} Data Sekolah</h3>
                </div>
                <div class="card-body">
                    <form action="{{ isset($sekolah) ? route('sekolah.update', $sekolah->id) : route('sekolah.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @if(isset($sekolah))
                            @method('PUT')
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Sekolah <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_sekolah" class="form-control @error('nama_sekolah') is-invalid @enderror" value="{{ old('nama_sekolah', $sekolah->nama_sekolah ?? '') }}" required>
                                    @error('nama_sekolah')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">NPSN</label>
                                    <input type="text" name="npsn" class="form-control @error('npsn') is-invalid @enderror" value="{{ old('npsn', $sekolah->npsn ?? '') }}">
                                    @error('npsn')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Kepala Sekolah</label>
                                    <input type="text" name="nama_kepala_sekolah" class="form-control @error('nama_kepala_sekolah') is-invalid @enderror" value="{{ old('nama_kepala_sekolah', $sekolah->nama_kepala_sekolah ?? '') }}">
                                    @error('nama_kepala_sekolah')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Jenjang <span class="text-danger">*</span></label>
                                    <select name="jenjang" class="form-select @error('jenjang') is-invalid @enderror" required>
                                        <option value="">Pilih Jenjang</option>
                                        <option value="SD" {{ old('jenjang', $sekolah->jenjang ?? '') == 'SD' ? 'selected' : '' }}>SD</option>
                                        <option value="SMP" {{ old('jenjang', $sekolah->jenjang ?? '') == 'SMP' ? 'selected' : '' }}>SMP</option>
                                        <option value="SMA" {{ old('jenjang', $sekolah->jenjang ?? '') == 'SMA' ? 'selected' : '' }}>SMA</option>
                                        <option value="SMK" {{ old('jenjang', $sekolah->jenjang ?? '') == 'SMK' ? 'selected' : '' }}>SMK</option>
                                    </select>
                                    @error('jenjang')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="">Pilih Status</option>
                                        <option value="Negeri" {{ old('status', $sekolah->status ?? '') == 'Negeri' ? 'selected' : '' }}>Negeri</option>
                                        <option value="Swasta" {{ old('status', $sekolah->status ?? '') == 'Swasta' ? 'selected' : '' }}>Swasta</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Akreditasi</label>
                                    <input type="text" name="akreditasi" class="form-control @error('akreditasi') is-invalid @enderror" value="{{ old('akreditasi', $sekolah->akreditasi ?? '') }}" placeholder="A, B, C">
                                    @error('akreditasi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Logo Sekolah</label>
                                    <input type="file" name="logo" id="logoInput" class="form-control @error('logo') is-invalid @enderror" accept="image/*" onchange="previewLogo(event)">
                                    @error('logo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div id="logoPreviewContainer">
                                        @if(isset($sekolah) && $sekolah->logo)
                                            <img id="logoPreview" src="{{ asset('storage/' . $sekolah->logo) }}" alt="Logo" class="mt-2" style="max-height: 100px;">
                                        @else
                                            <img id="logoPreview" src="#" alt="Logo" class="mt-2 d-none" style="max-height: 100px;">
                                        @endif
                                    </div>
                                @push('scripts')
                                <script>
                                function previewLogo(event) {
                                    const input = event.target;
                                    const preview = document.getElementById('logoPreview');
                                    if (input.files && input.files[0]) {
                                        const reader = new FileReader();
                                        reader.onload = function(e) {
                                            preview.src = e.target.result;
                                            preview.classList.remove('d-none');
                                        }
                                        reader.readAsDataURL(input.files[0]);
                                    } else {
                                        preview.src = '#';
                                        preview.classList.add('d-none');
                                    }
                                }
                                </script>
                                @endpush
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Alamat <span class="text-danger">*</span></label>
                                    <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3" required>{{ old('alamat', $sekolah->alamat ?? '') }}</textarea>
                                    @error('alamat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Kelurahan</label>
                                    <input type="text" name="kelurahan" class="form-control @error('kelurahan') is-invalid @enderror" value="{{ old('kelurahan', $sekolah->kelurahan ?? '') }}">
                                    @error('kelurahan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Kecamatan</label>
                                    <input type="text" name="kecamatan" class="form-control @error('kecamatan') is-invalid @enderror" value="{{ old('kecamatan', $sekolah->kecamatan ?? '') }}">
                                    @error('kecamatan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Kode Pos</label>
                                    <input type="text" name="kode_pos" class="form-control @error('kode_pos') is-invalid @enderror" value="{{ old('kode_pos', $sekolah->kode_pos ?? '') }}">
                                    @error('kode_pos')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Kota <span class="text-danger">*</span></label>
                                    <input type="text" name="kota" class="form-control @error('kota') is-invalid @enderror" value="{{ old('kota', $sekolah->kota ?? '') }}" required>
                                    @error('kota')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Provinsi <span class="text-danger">*</span></label>
                                    <input type="text" name="provinsi" class="form-control @error('provinsi') is-invalid @enderror" value="{{ old('provinsi', $sekolah->provinsi ?? '') }}" required>
                                    @error('provinsi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Telepon</label>
                                    <input type="text" name="telepon" class="form-control @error('telepon') is-invalid @enderror" value="{{ old('telepon', $sekolah->telepon ?? '') }}">
                                    @error('telepon')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $sekolah->email ?? '') }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Website</label>
                                    <input type="url" name="website" class="form-control @error('website') is-invalid @enderror" value="{{ old('website', $sekolah->website ?? '') }}" placeholder="https://">
                                    @error('website')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy"></i> Simpan
                            </button>
                            <a href="{{ route('sekolah.index') }}" class="btn btn-secondary">
                                <i class="ti ti-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
