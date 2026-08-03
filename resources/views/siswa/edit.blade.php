@extends('layouts.app', ['pageSlug' => 'siswa'])

@section('title','Edit Siswa')

@php
    $backRoute = $backRoute ?? route('siswa.index');
@endphp

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title fw-semibold m-0">Edit Siswa</h4>
                <a href="{{ $backRoute }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('siswa.update', $siswa->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>Informasi:</strong> Perubahan akan memperbarui data siswa dan akun login terkait. Kosongkan password jika tidak ingin mengubah.
                    </div>

                    <h5 class="mb-2">Data Pribadi</h5>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">NIS <span class="text-danger">*</span></label>
                            <input type="text" name="nis" class="form-control @error('nis') is-invalid @enderror" value="{{ old('nis', $siswa->nis) }}" required>
                            @error('nis')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">NISN <span class="text-danger">*</span></label>
                            <input type="text" name="nisn" class="form-control @error('nisn') is-invalid @enderror" value="{{ old('nisn', $siswa->nisn) }}" required>
                            @error('nisn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $siswa->nama) }}" required>
                        @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror" required>
                                <option value="">Pilih</option>
                                <option value="L" {{ old('jenis_kelamin', $siswa->jenis_kelamin)=='L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('jenis_kelamin', $siswa->jenis_kelamin)=='P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('jenis_kelamin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Kelas <span class="text-danger">*</span></label>
                            <select name="kelas_id" class="form-select @error('kelas_id') is-invalid @enderror" required>
                                <option value="">Pilih Kelas</option>
                                @foreach($kelasList as $kelas)
                                    <option value="{{ $kelas->id }}" {{ old('kelas_id', $siswa->kelas_id)==$kelas->id ? 'selected' : '' }}>{{ $kelas->nama_kelas }}</option>
                                @endforeach
                            </select>
                            @error('kelas_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        @if($canManageClassPositions)
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Jabatan Kelas</label>
                                <select name="jabatan_kelas" class="form-select @error('jabatan_kelas') is-invalid @enderror">
                                    <option value="">Tidak ada</option>
                                    <option value="ketua" {{ old('jabatan_kelas', $siswa->jabatan_kelas) == 'ketua' ? 'selected' : '' }}>Ketua Kelas</option>
                                    <option value="wakil" {{ old('jabatan_kelas', $siswa->jabatan_kelas) == 'wakil' ? 'selected' : '' }}>Wakil Ketua Kelas</option>
                                    <option value="sekretaris" {{ old('jabatan_kelas', $siswa->jabatan_kelas) == 'sekretaris' ? 'selected' : '' }}>Sekretaris Kelas</option>
                                    <option value="bendahara" {{ old('jabatan_kelas', $siswa->jabatan_kelas) == 'bendahara' ? 'selected' : '' }}>Bendahara</option>
                                </select>
                                @error('jabatan_kelas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        @endif
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $siswa->user->email ?? $siswa->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Foto Profile Siswa</label>
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <img src="{{ asset('storage/' . ($siswa->user->foto ?? 'images/default-avatar-male.svg')) }}" alt="Foto Siswa" id="siswaPreviewImage" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover; border: 1px solid #e2e8f0;">
                            <div>
                                <input type="file" id="siswaFotoInput" name="foto" class="form-control @error('foto') is-invalid @enderror" accept="image/*" capture="environment" onchange="previewSiswaImage(event)">
                                <div class="mt-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#siswaCameraModal">
                                        <i class="ti ti-camera me-1"></i> Ambil dari Kamera
                                    </button>
                                </div>
                                <small class="form-hint">Format: JPG, PNG. Maksimal 2MB</small>
                                @error('foto')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                    <hr class="my-4">

                    <h5 class="mb-2">Data Akun Login</h5>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $siswa->user->username ?? '') }}" required>
                            <small class="form-hint">Username untuk login ke sistem</small>
                            @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Password Baru</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                            <small class="form-hint">Isi hanya jika ingin mengganti password (minimal 6 karakter)</small>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-2"></i>Perbarui
                        </button>
                        <a href="{{ $backRoute }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Camera capture modal for siswa photo -->
<div class="modal fade" id="siswaCameraModal" tabindex="-1" aria-labelledby="siswaCameraModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="siswaCameraModalLabel">Ambil Foto Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body text-center">
                <video id="siswaCameraVideo" class="w-100" style="max-height: 360px; background: #000; border-radius: 0.75rem;" autoplay muted playsinline></video>
                <canvas id="siswaCameraCanvas" style="display:none;"></canvas>
                <p class="mt-3 text-muted">Tekan tombol ambil untuk menggunakan foto dari kamera perangkat Anda.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="takeSiswaPhoto()">Ambil Foto</button>
            </div>
        </div>
    </div>
</div>

<script>
    let siswaCameraStream = null;
    let siswaFotoInput = null;
    let siswaCameraModalEl = null;
    let siswaCameraVideo = null;
    let siswaCameraCanvas = null;

    function previewSiswaImage(event) {
        const input = event.target;
        const preview = document.getElementById('siswaPreviewImage');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function startSiswaCamera() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert('Browser Anda tidak mendukung akses kamera.');
            return;
        }

        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false })
            .then(function(stream) {
                siswaCameraStream = stream;
                siswaCameraVideo.srcObject = stream;
                siswaCameraVideo.play();
            })
            .catch(function(error) {
                console.error('Siswa camera access error:', error);
                alert('Tidak dapat mengakses kamera. Pastikan izin kamera diberikan.');
                const modal = bootstrap.Modal.getInstance(siswaCameraModalEl);
                if (modal) modal.hide();
            });
    }

    function stopSiswaCamera() {
        if (siswaCameraStream) {
            siswaCameraStream.getTracks().forEach(function(track) {
                track.stop();
            });
            siswaCameraStream = null;
        }
        if (siswaCameraVideo) {
            siswaCameraVideo.pause();
            siswaCameraVideo.srcObject = null;
        }
    }

    function takeSiswaPhoto() {
        if (!siswaCameraVideo || !siswaCameraCanvas) return;
        const context = siswaCameraCanvas.getContext('2d');
        siswaCameraCanvas.width = siswaCameraVideo.videoWidth;
        siswaCameraCanvas.height = siswaCameraVideo.videoHeight;
        context.drawImage(siswaCameraVideo, 0, 0, siswaCameraCanvas.width, siswaCameraCanvas.height);

        siswaCameraCanvas.toBlob(function(blob) {
            if (!blob) return;
            const file = new File([blob], 'siswa-profile.jpg', { type: blob.type });
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            siswaFotoInput.files = dataTransfer.files;
            previewSiswaImage({ target: siswaFotoInput });
            const modal = bootstrap.Modal.getInstance(siswaCameraModalEl);
            if (modal) modal.hide();
        }, 'image/jpeg', 0.92);
    }

    document.addEventListener('DOMContentLoaded', function() {
        siswaFotoInput = document.getElementById('siswaFotoInput');
        siswaCameraModalEl = document.getElementById('siswaCameraModal');
        siswaCameraVideo = document.getElementById('siswaCameraVideo');
        siswaCameraCanvas = document.getElementById('siswaCameraCanvas');

        if (siswaCameraModalEl) {
            siswaCameraModalEl.addEventListener('shown.bs.modal', function() {
                startSiswaCamera();
            });
            siswaCameraModalEl.addEventListener('hidden.bs.modal', function() {
                stopSiswaCamera();
            });
        }
    });
</script>
@endsection
