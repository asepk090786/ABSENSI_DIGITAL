<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title', 'Verifikasi Sertifikat')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/icon_simadisnew.png') }}">
    <link rel="stylesheet" href="{{ asset('vendor/tabler/dist/css/tabler.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/tabler/dist/css/tabler-vendors.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/tabler/dist/css/tabler-icons.min.css') }}">
    <style>
        body {
            font-family: 'Poppins', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            margin: 0;
            background: #f8fafc;
            color: #0f172a;
        }
        .public-container {
            max-width: 900px;
            margin: 3rem auto;
            padding: 1.5rem;
        }
        .public-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
            padding: 2rem;
        }
        .public-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .public-header h1 {
            margin: 0;
            font-size: 1.65rem;
            letter-spacing: -0.02em;
        }
        .public-note {
            color: #475569;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="public-container">
        <div class="public-card">
            <div class="public-header">
                <div>
                    <h1>@yield('title', 'Verifikasi Sertifikat')</h1>
                    <p class="public-note">Pindai QR atau masukkan kode untuk melihat status sertifikat.</p>
                </div>
            </div>
            @yield('content')
        </div>
    </div>
</body>
</html>
