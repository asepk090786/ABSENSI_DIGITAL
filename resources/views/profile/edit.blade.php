@extends('layouts.app', ['page' => __('User Profile'), 'pageSlug' => 'profile'])

@section('content')
    <div class="row">
        <div class="col-md-4">
            
            <div class="card">
                <div class="card-header border-0 pt-3 pb-2">
                    <h5 class="title">Foto Profile</h5>
                </div>
                <div class="card-body text-center">
                    @php
                        $defaultAvatar = auth()->user()->jenis_kelamin === 'P' ? asset('images/default-avatar-female.svg') : asset('images/default-avatar-male.svg');
                    @endphp
                    <div class="mb-2">
                        @if(auth()->user()->foto)
                            <img src="{{ asset('storage/' . auth()->user()->foto) }}" 
                                 alt="Profile Photo" 
                                 class="rounded-circle"
                                 style="width: 150px; height: 150px; object-fit: cover;"
                                 id="preview-image">
                        @else
                            <img src="{{ $defaultAvatar }}" 
                                 alt="Default Profile Photo" 
                                 class="rounded-circle"
                                 style="width: 150px; height: 150px; object-fit: cover;"
                                 id="preview-image">
                        @endif
                    </div>
                    <h5 class="title mb-1">{{ auth()->user()->name }}</h5>
                    <p class="description mb-0">{{ auth()->user()->role->role_name ?? 'User' }}</p>
                    <p class="description text-muted">{{ auth()->user()->username }}</p>
                </div>
            </div>

            
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2"><i class="ti ti-info-circle me-2"></i>Informasi Akun</h6>
                    <div class="mb-2">
                        <small class="text-muted">NIP:</small>
                        <div>{{ auth()->user()->nip ?? '-' }}</div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Email:</small>
                        <div>{{ auth()->user()->email ?? '-' }}</div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Jenis Kelamin:</small>
                        <div>{{ auth()->user()->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Role:</small>
                        <div>{{ auth()->user()->role->role_name ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            
            <div class="card">
                <div class="card-header border-0 pt-3 pb-2">
                    <h5 class="title">{{ __('Edit Profile') }}</h5>
                </div>
                <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" autocomplete="off">
                    <div class="card-body">
                        @csrf
                        @method('put')

                        @include('alerts.success')

                        <div class="form-group{{ $errors->has('foto') ? ' has-danger' : '' }}">
                            <label>Foto Profile</label>
                            <input type="file" id="fotoInput" name="foto" class="form-control{{ $errors->has('foto') ? ' is-invalid' : '' }}" accept="image/*" onchange="previewImage(event)">
                            <input type="hidden" id="fotoDataInput" name="foto_data" value="">
                            <div class="mt-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="cameraCaptureBtn" data-bs-toggle="modal" data-bs-target="#cameraModal">
                                    <i class="ti ti-camera me-1"></i> Ambil dari Kamera
                                </button>
                            </div>
                            <small class="form-text text-muted">Format: JPG, PNG. Maksimal 2MB. Setelah mengambil foto, tekan tombol Save untuk menyimpan ke server. Refresh tanpa menyimpan akan mengembalikan foto lama.</small>
                            <div id="fotoStatus" class="form-text text-success" style="display:none;">Foto siap disimpan. Tekan tombol Save untuk menyimpan.</div>
                            @include('alerts.feedback', ['field' => 'foto'])
                        </div>

                        <div class="form-group{{ $errors->has('name') ? ' has-danger' : '' }}">
                            <label>Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" placeholder="Nama Lengkap" value="{{ old('name', auth()->user()->name) }}" required>
                            @include('alerts.feedback', ['field' => 'name'])
                        </div>

                        <div class="form-group{{ $errors->has('nip') ? ' has-danger' : '' }}">
                            <label>NIP</label>
                            <input type="text" name="nip" class="form-control{{ $errors->has('nip') ? ' is-invalid' : '' }}" placeholder="NIP" value="{{ old('nip', auth()->user()->nip) }}">
                            @include('alerts.feedback', ['field' => 'nip'])
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group{{ $errors->has('email') ? ' has-danger' : '' }}">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" placeholder="Email" value="{{ old('email', auth()->user()->email) }}">
                                    @include('alerts.feedback', ['field' => 'email'])
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group{{ $errors->has('jenis_kelamin') ? ' has-danger' : '' }}">
                                    <label>Jenis Kelamin <span class="text-danger">*</span></label>
                                    <select name="jenis_kelamin" class="form-control{{ $errors->has('jenis_kelamin') ? ' is-invalid' : '' }}" required>
                                        <option value="">Pilih</option>
                                        <option value="L" {{ old('jenis_kelamin', auth()->user()->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="P" {{ old('jenis_kelamin', auth()->user()->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                    @include('alerts.feedback', ['field' => 'jenis_kelamin'])
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" class="form-control" value="{{ auth()->user()->username }}" disabled>
                            <small class="form-text text-muted">Username tidak dapat diubah</small>
                        </div>

                        <div class="form-group">
                            <label>Role</label>
                            <input type="text" class="form-control" value="{{ auth()->user()->role->role_name ?? '-' }}" disabled>
                            <small class="form-text text-muted">Role tidak dapat diubah</small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-fill btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>{{ __('Save') }}
                        </button>
                    </div>
                </form>
            </div>

            
            <div class="card">
                <div class="card-header border-0 pt-3 pb-2">
                    <h5 class="title">{{ __('Password') }}</h5>
                </div>
<form method="post" action="{{ route('profile.updatePassword') }}" autocomplete="off">
                    <div class="card-body">
                        @csrf
                        @method('put')

                        @include('alerts.success', ['key' => 'password_status'])

                        <div class="form-group{{ $errors->has('old_password') ? ' has-danger' : '' }}">
                            <label>{{ __('Current Password') }} <span class="text-danger">*</span></label>
                            <input type="password" name="old_password" class="form-control{{ $errors->has('old_password') ? ' is-invalid' : '' }}" placeholder="{{ __('Current Password') }}" value="" required>
                            @include('alerts.feedback', ['field' => 'old_password'])
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group{{ $errors->has('password') ? ' has-danger' : '' }}">
                                    <label>{{ __('New Password') }} <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" placeholder="{{ __('New Password') }}" value="" required>
                                    @include('alerts.feedback', ['field' => 'password'])
                                    <small class="form-text text-muted">Minimal 8 karakter</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('Confirm New Password') }} <span class="text-danger">*</span></label>
                                    <input type="password" name="password_confirmation" class="form-control" placeholder="{{ __('Confirm New Password') }}" value="" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-fill btn-primary">
                            <i class="ti ti-lock me-1"></i>{{ __('Change password') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let cameraStream = null;
        let cameraReady = false;
        let photoInput = null;
        let fotoDataInput = null;
        let previewImageEl = null;
        let fotoStatusEl = null;
        let cameraModalEl = null;
        let cameraVideo = null;
        let cameraCanvas = null;
        let keepCapturedPhoto = false;

        function previewImage(event) {
            const input = event.target;
            const preview = document.getElementById('preview-image');
            const file = input.files && input.files[0] ? input.files[0] : null;

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    if (fotoStatusEl) {
                        fotoStatusEl.style.display = 'block';
                        fotoStatusEl.textContent = 'Foto siap disimpan. Tekan tombol Save untuk menyimpan.';
                    }
                }
                reader.readAsDataURL(file);
                if (fotoDataInput) {
                    fotoDataInput.value = '';
                }
            } else if (fotoDataInput && fotoDataInput.value) {
                preview.src = fotoDataInput.value;
                if (fotoStatusEl) {
                    fotoStatusEl.style.display = 'block';
                    fotoStatusEl.textContent = 'Foto siap disimpan. Tekan tombol Save untuk menyimpan.';
                }
            } else {
                if (previewImageEl) {
                    previewImageEl.src = previewImageEl.dataset.defaultSrc || previewImageEl.src;
                }
                if (fotoStatusEl) {
                    fotoStatusEl.style.display = 'none';
                }
            }
        }

        function startCamera() {
            cameraReady = false;

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert('Browser Anda tidak mendukung akses kamera.');
                return;
            }

            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false })
                .then(function(stream) {
                    cameraStream = stream;
                    cameraVideo.srcObject = stream;
                    cameraVideo.onloadedmetadata = function() {
                        cameraReady = true;
                        const playPromise = cameraVideo.play();
                        if (playPromise !== undefined) {
                            playPromise.catch(function(error) {
                                if (error.name !== 'AbortError') {
                                    console.error('Camera play failed:', error);
                                }
                            });
                        }
                    };
                    cameraVideo.oncanplay = function() {
                        if (!cameraReady) {
                            cameraReady = true;
                        }
                    };
                })
                .catch(function(error) {
                    console.error('Camera access error:', error);
                    alert('Tidak dapat mengakses kamera. Pastikan izin kamera diberikan.');
                    const modal = bootstrap.Modal.getOrCreateInstance(cameraModalEl);
                    if (modal) modal.hide();
                });
        }

        function stopCamera() {
            cameraReady = false;
            if (cameraStream) {
                cameraStream.getTracks().forEach(function(track) {
                    track.stop();
                });
                cameraStream = null;
            }
            if (cameraVideo) {
                cameraVideo.onloadedmetadata = null;
                cameraVideo.oncanplay = null;
                cameraVideo.pause();
                cameraVideo.srcObject = null;
            }
        }

        let capturedPhotoURL = null;
        let cameraCapturePreview = null;
        let retakePhotoBtn = null;
        let usePhotoBtn = null;

        function updateCameraModalState(captured) {
            if (!cameraCapturePreview || !retakePhotoBtn || !usePhotoBtn) return;
            cameraCapturePreview.style.display = captured ? 'block' : 'none';
            retakePhotoBtn.style.display = captured ? 'inline-block' : 'none';
            usePhotoBtn.style.display = captured ? 'inline-block' : 'none';
            cameraVideo.style.display = captured ? 'none' : 'block';
        }

        function resetCameraModal() {
            cameraReady = false;
            if (capturedPhotoURL) {
                URL.revokeObjectURL(capturedPhotoURL);
                capturedPhotoURL = null;
            }
            if (cameraCapturePreview) {
                cameraCapturePreview.src = '';
            }
            if (!keepCapturedPhoto && fotoDataInput) {
                fotoDataInput.value = '';
            }
            if (!keepCapturedPhoto && photoInput) {
                photoInput.value = '';
                previewImage({ target: photoInput });
            }
            updateCameraModalState(false);
        }

        function takePhoto() {
            if (!cameraVideo || !cameraCanvas) return;
            if (!cameraReady || cameraVideo.videoWidth === 0 || cameraVideo.videoHeight === 0) {
                alert('Kamera belum siap. Tunggu beberapa detik lalu coba lagi.');
                return;
            }

            const context = cameraCanvas.getContext('2d');
            const width = cameraVideo.videoWidth || 640;
            const height = cameraVideo.videoHeight || 480;
            cameraCanvas.width = width;
            cameraCanvas.height = height;
            context.drawImage(cameraVideo, 0, 0, width, height);

            cameraCanvas.toBlob(function(blob) {
                if (!blob) return;
                    const reader = new FileReader();
                reader.onloadend = function() {
                    const result = reader.result;
                    if (fotoDataInput) {
                        fotoDataInput.value = result;
                    }
                    if (previewImageEl) {
                        previewImageEl.src = result;
                    }
                    if (cameraCapturePreview) {
                        if (capturedPhotoURL) {
                            URL.revokeObjectURL(capturedPhotoURL);
                        }
                        capturedPhotoURL = URL.createObjectURL(blob);
                        cameraCapturePreview.src = capturedPhotoURL;
                    }
                    if (fotoStatusEl) {
                        fotoStatusEl.style.display = 'block';
                        fotoStatusEl.textContent = 'Foto siap disimpan. Tekan tombol Save untuk menyimpan.';
                    }
                    keepCapturedPhoto = true;
                    updateCameraModalState(true);
                };
                reader.readAsDataURL(blob);
            }, 'image/jpeg', 0.92);
        }

        function retakePhoto() {
            if (photoInput) {
                photoInput.value = '';
            }
            if (fotoDataInput) {
                fotoDataInput.value = '';
            }
            if (previewImageEl) {
                previewImageEl.src = previewImageEl.dataset.defaultSrc || previewImageEl.src;
            }
            if (cameraCapturePreview) {
                cameraCapturePreview.src = '';
            }
            if (fotoStatusEl) {
                fotoStatusEl.style.display = 'none';
            }
            keepCapturedPhoto = false;
            resetCameraModal();
            startCamera();
        }

        function useCapturedPhoto() {
            if (fotoStatusEl) {
                fotoStatusEl.style.display = 'block';
                fotoStatusEl.textContent = 'Foto siap disimpan. Tekan tombol Save untuk menyimpan.';
            }
            keepCapturedPhoto = true;
        }

        document.addEventListener('DOMContentLoaded', function() {
            photoInput = document.getElementById('fotoInput');
            fotoDataInput = document.getElementById('fotoDataInput');
            previewImageEl = document.getElementById('preview-image');
            if (previewImageEl) {
                previewImageEl.dataset.defaultSrc = previewImageEl.src;
            }
            fotoStatusEl = document.getElementById('fotoStatus');
            cameraModalEl = document.getElementById('cameraModal');
            cameraVideo = document.getElementById('cameraVideo');
            cameraCanvas = document.getElementById('cameraCanvas');
            cameraCapturePreview = document.getElementById('cameraCapturePreview');
            retakePhotoBtn = document.getElementById('retakePhotoBtn');
            usePhotoBtn = document.getElementById('usePhotoBtn');

            if (cameraModalEl) {
                cameraModalEl.addEventListener('shown.bs.modal', function() {
                    resetCameraModal();
                    keepCapturedPhoto = false;
                    startCamera();
                });
                cameraModalEl.addEventListener('hide.bs.modal', function(event) {
                    stopCamera();
                    if (!keepCapturedPhoto) {
                        resetCameraModal();
                    }
                });
            }
        });
    </script>

    <!-- Camera capture modal -->
    <div class="modal fade" id="cameraModal" tabindex="-1" aria-labelledby="cameraModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cameraModalLabel">Ambil Foto Profil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body text-center">
                    <video id="cameraVideo" class="w-100" style="max-height: 360px; background: #000; border-radius: 0.75rem;"></video>
                    <img id="cameraCapturePreview" src="" alt="Foto Hasil Kamera" class="w-100 rounded" style="display:none; max-height: 360px; object-fit: cover;">
                    <canvas id="cameraCanvas" style="display:none;"></canvas>
                    <p class="mt-3 text-muted">Tekan tombol ambil untuk menangkap foto, lalu gunakan atau ambil ulang sebelum menutup.</p>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" onclick="retakePhoto()" id="retakePhotoBtn" style="display:none;">Ambil Ulang</button>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-success" onclick="useCapturedPhoto()" data-bs-dismiss="modal" id="usePhotoBtn" style="display:none;">Gunakan Foto</button>
                        <button type="button" class="btn btn-primary" onclick="takePhoto()">Ambil Foto</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
