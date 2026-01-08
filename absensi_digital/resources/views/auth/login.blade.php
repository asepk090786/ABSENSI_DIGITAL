<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Absensi Digital</title>
    
    <!-- Tabler CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    
    <style>
        .page { min-height: 100vh; }
        .form-footer { margin-top: 1.5rem; }
    </style>
</head>
<body class="d-flex flex-column bg-white">
    <div class="page page-center">
        <div class="container container-tight py-4">
            <!-- Logo -->
            <div class="text-center mb-4">
                <a href="/" class="navbar-brand navbar-brand-autodark">
                    <div class="d-flex align-items-center justify-content-center">
                        <div class="bg-primary text-white rounded-3 p-2 me-2">
                            <i class="ti ti-school" style="font-size: 1.5rem;"></i>
                        </div>
                        <span class="fs-2 fw-bold text-primary">Absensi Digital</span>
                    </div>
                </a>
            </div>
            
            <!-- Login Card -->
            <div class="card card-md shadow-sm">
                <div class="card-body">
                    <h2 class="h2 text-center mb-4">Masuk ke Akun Anda</h2>
                    
                    <form method="POST" action="{{ route('login') }}" autocomplete="off">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Alamat Email</label>
                            <div class="input-group input-group-flat">
                                <span class="input-group-text">
                                    <i class="ti ti-mail"></i>
                                </span>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                       placeholder="email@contoh.com" value="{{ old('email') }}" required autofocus>
                            </div>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                Password
                                @if (Route::has('password.request'))
                                <span class="form-label-description">
                                    <a href="{{ route('password.request') }}">Lupa password?</a>
                                </span>
                                @endif
                            </label>
                            <div class="input-group input-group-flat">
                                <span class="input-group-text">
                                    <i class="ti ti-lock"></i>
                                </span>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                                       placeholder="Password Anda" required>
                                <span class="input-group-text">
                                    <a href="#" class="link-secondary" data-bs-toggle="tooltip" title="Tampilkan password" 
                                       onclick="togglePassword(event)">
                                        <i class="ti ti-eye" id="toggleIcon"></i>
                                    </a>
                                </span>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-check">
                                <input type="checkbox" class="form-check-input" name="remember">
                                <span class="form-check-label">Ingat saya di perangkat ini</span>
                            </label>
                        </div>
                        
                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ti ti-login me-2"></i>Masuk
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Demo Info -->
            <div class="text-center text-muted mt-3">
                <div class="card card-sm">
                    <div class="card-body py-2">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="ti ti-info-circle me-2 text-blue"></i>
                            <small><strong>Demo:</strong> admin@example.com / password</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="text-center text-muted mt-4">
                <small>&copy; {{ date('Y') }} Absensi Digital - Sistem Manajemen Sekolah</small>
            </div>
        </div>
    </div>
    
    <!-- Tabler JS -->
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/js/tabler.min.js"></script>
    <script>
        function togglePassword(e) {
            e.preventDefault();
            const passwordInput = document.querySelector('input[name="password"]');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('ti-eye');
                toggleIcon.classList.add('ti-eye-off');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('ti-eye-off');
                toggleIcon.classList.add('ti-eye');
            }
        }
    </script>
</body>
</html>
