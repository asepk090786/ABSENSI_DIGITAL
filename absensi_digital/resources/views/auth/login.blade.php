<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - SIMADIS</title>
    
    <!-- Tabler CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .page { 
            min-height: 100vh; 
            position: relative;
        }
        
        .page::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(120, 119, 198, 0.3), transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 135, 135, 0.3), transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(135, 206, 235, 0.3), transparent 50%);
            pointer-events: none;
        }
        
        .form-footer { margin-top: 1.5rem; }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }
        
        .logo-container {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.35);
            min-width: 260px;
        }
        
        .logo-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 0.75rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-subtitle {
            display: block;
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: #6b7280;
            margin-bottom: 0.2rem;
            text-transform: uppercase;
        }

        .brand-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 0.75rem;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .input-group-flat .input-group-text {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.15);
        }
        
        .demo-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .footer-text {
            color: rgba(255, 255, 255, 0.9);
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="d-flex flex-column">
    @php
        use Illuminate\Support\Facades\Storage;
    @endphp
    <div class="page page-center">
        <div class="container container-tight py-4">
            <!-- Logo -->
            <div class="text-center mb-4">
                <div class="logo-container d-inline-block">
                    <div class="d-flex align-items-center justify-content-center">
                        @php
                            $logoUrl = null;
                            if(isset($sekolah) && $sekolah && $sekolah->logo){
                                $logoPath = $sekolah->logo;
                                if (Storage::disk('public')->exists($logoPath)) {
                                    $logoUrl = Storage::url($logoPath);
                                }
                            }
                        @endphp
                        @if($logoUrl)
                            <div class="me-3">
                                <img src="{{ $logoUrl }}" alt="Logo Sekolah" style="height: 56px; width: 56px; object-fit: contain; border-radius: 12px; border: 1px solid rgba(0,0,0,0.05); background: #fff;">
                            </div>
                        @else
                            <div class="logo-icon text-white me-2">
                                <i class="ti ti-school" style="font-size: 1.5rem;"></i>
                            </div>
                        @endif
                        <div class="text-center">
                            <span class="brand-subtitle">SIMADIS</span>
                            <p class="brand-title mb-0">{{ $sekolah->nama_sekolah ?? 'Sistem Manajemen Absensi Digital' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Login Card -->
            <div class="card card-md login-card">
                <div class="card-body">
                    <h2 class="h2 text-center mb-1">Masuk ke Akun Anda</h2>
                    <p class="text-center text-muted mb-4">SIMADIS - Sistem Manajemen Absensi Digital</p>
                    
                    <form method="POST" action="{{ route('login') }}" autocomplete="off">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Email atau Username</label>
                            <div class="input-group input-group-flat">
                                <span class="input-group-text">
                                    <i class="ti ti-mail"></i>
                                </span>
                                <input type="text" name="login" class="form-control @error('login') is-invalid @enderror" 
                                       placeholder="email@contoh.com atau username" value="{{ old('login') }}" required autofocus>
                            </div>
                            @error('login')
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
            <div class="text-center mt-3">
                <div class="card card-sm demo-card">
                    <div class="card-body py-2">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="ti ti-info-circle me-2" style="color: #667eea;"></i>
                            <small><strong>Demo:</strong> admin@example.com / password</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="text-center mt-4">
                            <small class="footer-text">&copy; {{ date('Y') }} SIMADIS - Sistem Manajemen Absensi Digital</small>
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
