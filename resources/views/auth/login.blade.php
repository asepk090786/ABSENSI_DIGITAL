<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Login — SIMADIS</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2563EB">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.0.0/dist/tabler-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler-flags.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler-vendors.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #2563EB;
            --primary-dark: #1d4ed8;
            --primary-light: #dbeafe;
            --accent: #10B981;
            --font: 'Poppins', system-ui, -apple-system, sans-serif;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: var(--font);
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 50%, #f1f5f9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .login-wrapper {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2rem;
        }

        .login-illustration {
            display: none;
            flex: 1;
            text-align: center;
        }
        .login-illustration img {
            max-width: 100%;
            height: auto;
            max-height: 400px;
            object-fit: contain;
        }
        .login-illustration h2 {
            font-weight: 700;
            font-size: 1.3rem;
            color: #1e293b;
            margin-top: 1rem;
        }
        .login-illustration p {
            color: #64748b;
            font-size: 0.85rem;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15);
            padding: 2.5rem 2rem;
        }

        .login-logo {
            display: block;
            width: 72px;
            height: 72px;
            margin: 0 auto 1.25rem;
            border-radius: 1rem;
            object-fit: cover;
            box-shadow: 0 4px 12px rgba(37,99,235,0.2);
        }

        .login-title {
            text-align: center;
            font-weight: 700;
            font-size: 1.4rem;
            color: #1e293b;
            margin-bottom: 0.15rem;
        }
        .login-subtitle {
            text-align: center;
            color: #94a3b8;
            font-size: 0.82rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }
        .form-label {
            display: block;
            font-weight: 500;
            font-size: 0.82rem;
            color: #475569;
            margin-bottom: 0.35rem;
        }
        .input-group {
            position: relative;
        }
        .input-group .input-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.15rem;
            pointer-events: none;
            z-index: 2;
        }
        .input-group .form-control {
            padding-left: 2.5rem;
        }
        .form-control {
            width: 100%;
            border-radius: 0.6rem;
            border: 1.5px solid #e2e8f0;
            padding: 0.6rem 0.85rem;
            font-size: 0.85rem;
            font-family: var(--font);
            color: #334155;
            background: #fff;
            transition: all 0.15s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }
        .form-control::placeholder {
            color: #cbd5e1;
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.2rem;
            font-size: 0.8rem;
        }
        .form-check-label {
            color: #64748b;
            cursor: pointer;
        }
        .forgot-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.15s ease;
        }
        .forgot-link:hover {
            color: var(--primary-dark);
        }

        .btn-login {
            width: 100%;
            padding: 0.65rem;
            border-radius: 0.6rem;
            border: none;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: #fff;
            font-weight: 600;
            font-size: 0.9rem;
            font-family: var(--font);
            cursor: pointer;
            transition: all 0.15s ease;
            letter-spacing: 0.01em;
        }
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(37,99,235,0.35);
        }
        .btn-login:active {
            transform: translateY(0);
        }

        .qr-login-section {
            margin: 0 0 1.5rem 0;
            padding: 0;
            border-top: none;
            text-align: center;
        }
        .qr-divider {
            text-align: center;
            margin: 1.2rem 0;
            position: relative;
        }
        .qr-divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e2e8f0;
            z-index: 1;
        }
        .qr-divider span {
            background: #fff;
            padding: 0 0.75rem;
            position: relative;
            z-index: 2;
            color: #cbd5e1;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .btn-qr-login {
            width: 100%;
            padding: 0.6rem;
            border-radius: 0.6rem;
            border: 1.5px solid var(--primary);
            background: #fff;
            color: var(--primary);
            font-weight: 600;
            font-size: 0.85rem;
            font-family: var(--font);
            cursor: pointer;
        }
        .btn-qr-login:hover {
            background: var(--primary-light);
        }
        .qr-scanner-panel {
            display: none;
            margin-top: 1rem;
        }
        .qr-scanner-panel.is-visible {
            display: block;
        }
        #qrReader {
            overflow: hidden;
            width: 100%;
            border: 1px solid #dbe4f0;
            border-radius: 0.6rem;
        }
        .qr-scanner-status {
            margin: 0.6rem 0 0;
            color: #64748b;
            font-size: 0.75rem;
        }

        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: #94a3b8;
            font-size: 0.72rem;
        }

        .alert {
            border-radius: 0.6rem;
            border: none;
            font-size: 0.82rem;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
        }
        .alert-danger {
            background: #fef2f2;
            color: #dc2626;
            border-left: 3px solid #dc2626;
        }

        @media (min-width: 768px) {
            .login-illustration {
                display: block;
            }
            .login-wrapper {
                gap: 3rem;
            }
        }

        @media (max-width: 576px) {
            body {
                align-items: flex-start;
                justify-content: flex-start;
                padding: calc(env(safe-area-inset-top) + 1rem) 0 1rem;
                overflow-y: auto;
            }
            .login-wrapper {
                padding: 1rem;
                min-height: 100vh;
                align-items: flex-start;
            }
            .login-card {
                padding: 1.75rem 1.25rem;
                border-radius: 0.85rem;
            }
        }
    </style>
</head>
<body>
<div class="login-wrapper">
    <!-- Illustration (desktop only) -->
    <div class="login-illustration">
        <img src="{{ asset('images/icon_simadisnew.png') }}" alt="SIMADIS" style="max-width: 200px;">
        <h2>Sistem Manajemen<br>Absensi Digital</h2>
        <p>SMAN 1 Pontang — Kabupaten Serang</p>
    </div>

    <!-- Login Card -->
    <div class="login-card">
        <img src="{{ asset('images/icon_simadisnew.png') }}" class="login-logo" alt="SIMADIS">
        <h1 class="login-title">Masuk</h1>
        <p class="login-subtitle">Gunakan akun Anda untuk mengakses sistem</p>

        @if($errors->any())
        <div class="alert alert-danger">
            <i class="ti ti-alert-triangle me-2"></i>{{ $errors->first() }}
        </div>
        @endif

        <!-- QR Login Section (Moved to Top) -->
        <div class="qr-login-section">
            <button type="button" class="btn-qr-login" id="openQrLogin" style="width: 100%;">
                <i class="ti ti-qrcode me-2"></i>Login dengan QR Code
            </button>
            <div class="qr-scanner-panel" id="qrScannerPanel">
                <div id="qrReader"></div>
                <p class="qr-scanner-status" id="qrScannerStatus">Arahkan kamera ke QR code pada kartu login.</p>
                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="closeQrLogin">Tutup kamera</button>
            </div>
        </div>

        <!-- Divider -->
        <div class="qr-divider">
            <span>ATAU GUNAKAN AKUN</span>
        </div>

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="form-group">
                <label class="form-label" for="login">Username atau Email</label>
                <div class="input-group">
                    <span class="input-icon"><i class="ti ti-user"></i></span>
                    <input type="text" name="login" id="login" class="form-control" placeholder="nama@pengguna.id" value="{{ old('login') }}" required autofocus>
                </div>
                @error('login')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Kata Sandi</label>
                <div class="input-group">
                    <span class="input-icon"><i class="ti ti-lock"></i></span>
                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                </div>
                @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="form-options">
                <label class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember">
                    <span class="form-check-label">Ingat saya</span>
                </label>
                @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="forgot-link">Lupa kata sandi?</a>
                @endif
            </div>

            <button type="submit" class="btn-login">
                <i class="ti ti-login me-2"></i>Masuk
            </button>
        </form>

        <div class="login-footer">
            &copy; {{ date('Y') }} SIMADIS — SMAN 1 Pontang
            <br>
            <a href="{{ url('/help') }}" class="forgot-link" target="_blank">Butuh bantuan? Lihat Help</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const openButton = document.getElementById('openQrLogin');
        const closeButton = document.getElementById('closeQrLogin');
        const scannerPanel = document.getElementById('qrScannerPanel');
        const scannerStatus = document.getElementById('qrScannerStatus');
        let scanner = null;
        let scanHandled = false;

        function stopScanner() {
            if (!scanner) return Promise.resolve();

            return scanner.stop().catch(function () {}).then(function () {
                scanner.clear();
                scanner = null;
            });
        }

        openButton.addEventListener('click', function () {
            if (typeof Html5Qrcode === 'undefined') {
                scannerStatus.textContent = 'Pemindai QR gagal dimuat. Periksa koneksi internet.';
                scannerPanel.classList.add('is-visible');
                return;
            }

            scanHandled = false;
            scannerPanel.classList.add('is-visible');
            scannerStatus.textContent = 'Meminta akses kamera...';
            scanner = new Html5Qrcode('qrReader');
            scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 220, height: 220 } },
                function (decodedText) {
                    if (scanHandled) return;

                    let targetUrl;
                    try {
                        targetUrl = new URL(decodedText, window.location.origin);
                    } catch (error) {
                        scannerStatus.textContent = 'QR code tidak valid.';
                        return;
                    }

                    if (targetUrl.origin !== window.location.origin || !targetUrl.pathname.startsWith('/qr-login/')) {
                        scannerStatus.textContent = 'Gunakan QR code dari kartu login SIMADIS.';
                        return;
                    }

                    scanHandled = true;
                    scannerStatus.textContent = 'QR valid. Mengarahkan ke login...';
                    stopScanner().then(function () {
                        window.location.href = targetUrl.href;
                    });
                },
                function () {}
            ).catch(function () {
                scannerStatus.textContent = 'Kamera tidak dapat digunakan. Izinkan akses kamera lalu coba lagi.';
            });
        });

        closeButton.addEventListener('click', function () {
            stopScanner().then(function () {
                scannerPanel.classList.remove('is-visible');
                scannerStatus.textContent = 'Arahkan kamera ke QR code pada kartu login.';
            });
        });
    });
</script>
</body>
</html>