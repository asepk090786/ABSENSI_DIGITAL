<!doctype html>
<html lang="en">
<head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title','Absensi Digital')</title>

        <!-- Bootstrap CSS (CDN) -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <style>
            body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; }
        </style>
</head>
<body>

    @unless(Route::is('login'))
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Absensi Digital</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('jam_belajar.index') }}">Jam Belajar</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('agenda_kelas.index') }}">Agenda</a></li>
                    <li class="nav-item"><a class="nav-link" href="#absensi">Absensi</a></li>
                    <li class="nav-item"><a class="nav-link" href="#nilai">Nilai</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('tahun_ajaran.index') }}">Pengaturan</a></li>
                </ul>

                <div class="d-flex">
                    @auth
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button class="btn btn-outline-danger btn-sm">Keluar</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Masuk</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
    @endunless

    <main class="py-4">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <!-- Bootstrap JS Bundle (for collapse, dropdowns) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        /* Custom theme touches */
        .hero-bg { background: linear-gradient(180deg, rgba(124,58,237,0.08), rgba(14,165,233,0.04)); }
        .stat-card { border-radius: .75rem; }
    </style>
</body>
</html>
