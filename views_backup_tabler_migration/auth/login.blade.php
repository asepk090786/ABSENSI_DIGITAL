<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=5, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - SIMADIS</title>
    
    <!-- Tabler CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    
    <!-- Mobile Login Optimization -->
    <link rel="stylesheet" href="{{ asset('css/login-mobile.css') }}">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        .page { 
            min-height: 100vh; 
            position: relative;
            display: flex;
            margin: 0;
            padding: 0;
        }
        
        .login-split-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        
        .login-left-section {
            flex: 3;
            background: url('{{ asset('images/bg.jpeg') }}') center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
        }
        
        .login-left-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.6) 0%, rgba(118, 75, 162, 0.6) 100%);
            pointer-events: none;
        }
        
        .login-illustration {
            max-width: 500px;
            width: 100%;
            z-index: 1;
            animation: fadeInLeft 0.8s ease-out;
        }
        
        .login-illustration img {
            width: 100%;
            height: auto;
            filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.2));
        }
        
        .login-right-section {
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
        }
        
        .login-right-section::before {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.08), transparent 70%);
            pointer-events: none;
        }
        
        .form-footer { margin-top: 1.5rem; }
        
        .login-card {
            background: #ffffff;
            backdrop-filter: blur(10px);
            border: none;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.1);
            max-width: 420px;
            width: 100%;
            z-index: 1;
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
        
        .logo-display {
            display: flex;
            align-items: center;
            justify-content: center;
            animation: slideDown 0.6s ease-out;
            padding: 0;
            margin-bottom: 0;
            min-width: auto;
            height: auto;
        }
        
        .logo-display img {
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
            width: 100%;
            height: auto;
            max-width: 360px;
            object-fit: contain;
        }
        
        .school-info {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .school-name {
            color: #ffffff;
            font-weight: 700;
            font-size: 1.25rem;
            margin-bottom: 0.25rem;
            letter-spacing: 0.5px;
        }
        
        .school-address {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 400;
            font-size: 0.875rem;
            line-height: 1.4;
        }

        .school-logo-login {
            margin-bottom: 0.75rem;
            display: flex;
            justify-content: center;
        }

        .school-logo-login img {
            max-width: 96px;
            max-height: 96px;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
            background: #ffffff;
            padding: 0.75rem;
            border-radius: 0.75rem;
            border: 2px solid #ffffff;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
        
        .logos-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .school-logo-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            width: auto;
            height: auto;
            animation: slideDown 0.6s ease-out 0.1s backwards;
        }
        
        .school-logo-wrapper img {
            max-width: 90px;
            max-height: 90px;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }
        
        .simadis-logo-wrapper {
            animation: slideUp 0.6s ease-out;
        }

        .simadis-logo-wrapper img {
            width: 100%;
            height: auto;
            max-width: 360px;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideRight {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideLeft {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @media (max-width: 992px) {
            .login-split-container {
                flex-direction: column;
            }
            .login-left-section {
                display: none;
            }
            .login-right-section {
                flex: 1;
                padding: 1.5rem 1rem;
            }
        }
        
        /* ===== MOBILE RESPONSIVE STYLES ===== */
        
        /* Tablet and small devices (768px and below) */
        @media (max-width: 768px) {
            .page {
                padding: 0;
            }
            
            .login-right-section {
                padding: 1rem 0.75rem;
            }
            
            .login-card {
                max-width: 100%;
                margin: 0;
            }
            
            .school-info {
                margin-bottom: 1.5rem;
            }
            
            .school-name {
                font-size: 1.125rem;
            }
            
            .school-address {
                font-size: 0.8rem;
            }
            
            .school-logo-login img {
                max-width: 84px;
                max-height: 84px;
            }
            
            /* Form improvements for mobile */
            .card-body {
                padding: 1.5rem 1rem;
            }
            
            .card-body h2 {
                font-size: 1.5rem;
                margin-bottom: 1.5rem;
            }
            
            .form-label {
                font-size: 0.95rem;
                font-weight: 600;
                margin-bottom: 0.5rem;
                display: block;
            }
            
            .form-label-description {
                display: inline;
                margin-left: 0.5rem;
            }
            
            .form-label-description a {
                font-size: 0.85rem;
            }
            
            .form-control,
            .input-group-text {
                font-size: 16px !important;
                padding: 0.875rem 0.75rem !important;
                height: auto;
                min-height: 44px;
                display: flex;
                align-items: center;
            }
            
            .input-group {
                margin-bottom: 1rem;
            }
            
            .input-group-text {
                width: 44px;
                justify-content: center;
            }
            
            .form-check {
                padding: 0.5rem 0;
                margin-bottom: 1rem;
            }
            
            .form-check-label {
                font-size: 0.95rem;
                margin-bottom: 0;
                padding-left: 0.5rem;
            }
            
            .mb-3 {
                margin-bottom: 1.25rem !important;
            }
            
            .form-footer {
                margin-top: 1.5rem;
            }
            
            .btn-primary {
                font-size: 1rem;
                padding: 0.875rem 1.25rem !important;
                min-height: 48px;
                display: flex;
                align-items: center;
                justify-content: center;
                width: 100%;
                border-radius: 0.375rem;
                font-weight: 600;
            }
            
            .btn-primary:active {
                transform: scale(0.98);
            }
            
            /* Error messages */
            .invalid-feedback {
                font-size: 0.875rem;
                margin-top: 0.375rem;
            }
            
            /* Demo card */
            .demo-card {
                font-size: 0.9rem;
                padding: 0.75rem;
            }
            
            .demo-card .card-body {
                padding: 0.75rem !important;
            }
            
            /* Footer */
            .footer-text {
                font-size: 0.85rem;
            }
        }
        
        /* Extra small devices (< 576px) - ENLARGED FOR MOBILE */
        @media (max-width: 575px) {
            .page {
                padding: 0;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
            }
            
            .login-split-container {
                flex-direction: column;
                min-height: 100vh;
            }
            
            .login-right-section {
                padding: 0;
                margin: 0;
                width: 100%;
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            
            .login-right-section > div {
                width: 100%;
                height: 100%;
                padding: 1rem;
                display: flex;
                flex-direction: column;
                justify-content: center;
                box-sizing: border-box;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .school-info {
                margin-bottom: 2rem;
                text-align: center;
            }
            
            .school-logo-login {
                margin-bottom: 1rem;
                display: flex;
                justify-content: center;
            }
            
            .school-logo-login img {
                max-width: 96px;
                max-height: 96px;
                object-fit: contain;
                padding: 0.75rem;
                border-radius: 0.75rem;
            }
            
            .school-name {
                font-size: 1.3rem;
                font-weight: 700;
                letter-spacing: 1px;
                text-transform: uppercase;
            }
            
            .school-address {
                font-size: 0.85rem;
                line-height: 1.5;
            }
            
            .login-card {
                max-width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
                background: transparent;
                border: none;
            }
            
            .card {
                border: none;
                margin-bottom: 1.5rem;
                background: #ffffff;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            }
            
            .card-body {
                padding: 2rem 1.5rem !important;
                background: #ffffff;
            }
            
            .card-body h2 {
                font-size: 1.75rem;
                font-weight: 700;
                margin-bottom: 2rem;
                line-height: 1.3;
                letter-spacing: -0.5px;
                color: #1f2937;
            }
            
            .form-label {
                font-size: 1rem;
                font-weight: 600;
                margin-bottom: 0.75rem;
                display: block;
                color: #374151;
            }
            
            .form-label-description {
                float: right;
                font-size: 0.85rem;
                font-weight: 500;
            }
            
            .form-control,
            input.form-control,
            input[type="text"],
            input[type="email"],
            input[type="password"],
            input[type="number"],
            textarea,
            select {
                font-size: 18px !important;
                padding: 1rem 0.875rem !important;
                min-height: 56px;
                border-radius: 0.5rem;
                height: auto;
                border: 2px solid #e5e7eb;
                transition: border-color 0.3s ease;
            }
            
            .form-control:focus,
            input[type="text"]:focus,
            input[type="email"]:focus,
            input[type="password"]:focus,
            textarea:focus,
            select:focus {
                border-color: #667eea;
                box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
                outline: none;
            }
            
            .input-group {
                margin-bottom: 1.5rem;
                display: flex;
                gap: 0;
            }
            
            .input-group-text {
                width: 56px;
                min-width: 56px;
                padding: 0 !important;
                justify-content: center;
                align-items: center;
                background: #f3f4f6;
                border: 2px solid #e5e7eb;
                border-right: none;
                border-radius: 0.5rem 0 0 0.5rem;
                font-size: 1.25rem;
                color: #667eea;
            }
            
            .input-group .form-control {
                border-radius: 0 0.5rem 0.5rem 0 !important;
                border-left: none !important;
            }
            
            .form-check {
                margin: 1.5rem 0;
                padding: 0.75rem 0;
                display: flex;
                align-items: center;
            }
            
            .form-check-input {
                width: 24px;
                height: 24px;
                margin-top: 0;
                margin-right: 0.75rem;
                cursor: pointer;
                accent-color: #667eea;
            }
            
            .form-check-label {
                font-size: 1rem;
                margin-left: 0;
                margin-bottom: 0;
                cursor: pointer;
                color: #4b5563;
                font-weight: 500;
            }
            
            .mb-3 {
                margin-bottom: 1.5rem !important;
            }
            
            .form-footer {
                margin-top: 2rem;
            }
            
            .btn {
                font-size: 1.1rem;
                min-height: 56px;
                padding: 1rem !important;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 0.5rem;
                -webkit-user-select: none;
                user-select: none;
                -webkit-appearance: none;
                appearance: none;
                font-weight: 600;
                letter-spacing: 0.5px;
                transition: all 0.3s ease;
            }
            
            .btn-primary {
                width: 100%;
                margin: 0 auto;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            }
            
            .btn-primary:active {
                transform: scale(0.98);
                box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
            }
            
            .btn-primary i {
                font-size: 1.2rem;
                margin-right: 0.5rem;
            }
            
            .invalid-feedback {
                font-size: 0.9rem;
                display: block;
                margin-top: 0.5rem;
                color: #dc3545;
                font-weight: 500;
            }
            
            .mt-3 {
                margin-top: 1.5rem !important;
            }
            
            .mt-4 {
                margin-top: 2rem !important;
            }
            
            .demo-card {
                margin: 0 auto;
                max-width: 100%;
                background: rgba(255, 255, 255, 0.95);
                border: 2px solid #e5e7eb;
            }
            
            .demo-card .card-body {
                padding: 1rem !important;
                font-size: 0.9rem;
                text-align: center;
            }
            
            .footer-text {
                font-size: 0.85rem;
                text-align: center;
                display: block;
            }
            
            /* Improve touch targets */
            input:focus,
            select:focus,
            textarea:focus,
            button:focus,
            a:focus {
                outline: none;
            }
        }
    </style>
</head>
<body>
    @php
        use Illuminate\Support\Facades\Storage;
    @endphp
    <div class="page">
        <div class="login-split-container">
            <!-- Left Section - Illustration -->
            <div class="login-left-section">
                
            </div>
            
            <!-- Right Section - Login Form -->
            <div class="login-right-section">
                <div style="max-width: 420px; width: 100%; z-index: 1;">
            @php
                $sekolah = \App\Models\Sekolah::first();
            @endphp
            
            @if($sekolah)
                <div class="school-info">
                    @if($sekolah->logo)
                        <div class="school-logo-login">
                            <img src="{{ asset('storage/' . $sekolah->logo) }}" alt="Logo Sekolah">
                        </div>
                    @endif
                    <div class="school-name">{{ $sekolah->nama_sekolah }}</div>
                    @if($sekolah->alamat)
                        <div class="school-address">{{ $sekolah->alamat }}</div>
                    @endif
                </div>
            @endif
            
            <!-- Login Form Section -->
            
            <!-- Login Card -->
            <div class="card card-md login-card">
                <div class="card-body">
                    <h2 class="h2 text-center mb-4">Masuk ke Akun Anda</h2>
                    
                    <form method="POST" action="{{ route('login') }}" autocomplete="off">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Email atau Username</label>
                            <div class="input-group input-group-flat">
                                <span class="input-group-text">
                                    <i class="ti ti-mail"></i>
                                </span>
                                <input type="text" name="login" class="form-control @error('login') is-invalid @enderror" 
                                       placeholder="email@contoh.com" value="{{ old('login') }}" required autofocus>
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
                                    <a href="{{ route('password.request') }}">Lupa?</a>
                                </span>
                                @endif
                            </label>
                            <div class="input-group input-group-flat">
                                <span class="input-group-text">
                                    <i class="ti ti-lock"></i>
                                </span>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                                       placeholder="Password" required>
                                <span class="input-group-text">
                                    <a href="#" class="link-secondary" data-toggle="tooltip" title="Tampilkan password" 
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
                                <span class="form-check-label">Ingat saya</span>
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
                            <small><strong>#</strong> ### / ###</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="text-center mt-4">
                <small class="footer-text" style="color: rgba(255,255,255,0.9);">&copy; {{ date('Y') }} SIMADIS - Sistem Manajemen Absensi Digital</small>
            </div>
                </div>
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
