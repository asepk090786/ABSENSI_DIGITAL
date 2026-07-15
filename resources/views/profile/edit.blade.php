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
                            <input type="file" name="foto" class="form-control{{ $errors->has('foto') ? ' is-invalid' : '' }}" accept="image/*" onchange="previewImage(event)">
                            <small class="form-text text-muted">Format: JPG, PNG. Maksimal 2MB</small>
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
                <form method="post" action="{{ route('profile.password') }}" autocomplete="off">
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
        function previewImage(event) {
            const input = event.target;
            const preview = document.getElementById('preview-image');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection
