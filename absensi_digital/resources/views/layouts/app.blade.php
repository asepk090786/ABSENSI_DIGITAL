<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Absensi Digital') }}</title>

        <style>
            :root {
                --bg: #f7f7f7;
                --panel: #ffffff;
                --border: #e2e2e2;
                --text: #1f2933;
                --muted: #5f6b76;
                --primary: #2b7a78;
                --primary-dark: #205e5d;
            }
            * { box-sizing: border-box; }
            body { margin: 0; font-family: "Segoe UI", Tahoma, sans-serif; background: var(--bg); color: var(--text); }
            a { color: var(--primary); text-decoration: none; }
            a:hover { color: var(--primary-dark); }
            header { background: var(--panel); border-bottom: 1px solid var(--border); padding: 12px 20px; }
            .topbar { display:flex; align-items:center; justify-content:space-between; gap:12px; }
            .topbar .brand { font-weight: 700; color: var(--primary-dark); }
            .layout { display:flex; min-height: calc(100vh - 56px); }
            .sidebar { width: 220px; background: var(--panel); border-right:1px solid var(--border); padding:16px 12px; }
            .sidebar h4 { margin:0 0 12px 8px; font-size:14px; color: var(--muted); text-transform:uppercase; letter-spacing:0.5px; }
            .sidebar ul { list-style:none; padding:0; margin:0; }
            .sidebar li { margin-bottom:6px; }
            .sidebar a { display:flex; gap:10px; align-items:center; padding:10px 12px; border-radius:6px; color: var(--text); }
            .sidebar a:hover, .sidebar .active > a { background:#e8f3f3; color: var(--primary-dark); }
            main.container { flex:1; padding:20px; }
            .card { background: var(--panel); border:1px solid var(--border); border-radius:8px; padding:16px; margin-bottom:16px; box-shadow:0 1px 2px rgba(0,0,0,0.04); }
            .card-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; }
            .card-title { margin:0; font-size:16px; font-weight:600; }
            .btn { display:inline-block; padding:8px 14px; border-radius:6px; border:1px solid transparent; font-size:14px; cursor:pointer; text-align:center; }
            .btn-primary { background: var(--primary); color:#fff; }
            .btn-primary:hover { background: var(--primary-dark); }
            .btn-outline-secondary { background:#fff; color: var(--text); border:1px solid var(--border); }
            .btn-danger { background:#c0392b; color:#fff; }
            table { width:100%; border-collapse:collapse; }
            th, td { padding:10px; border-bottom:1px solid var(--border); text-align:left; font-size:14px; }
            th { background:#f0f4f5; font-weight:600; }
            .alert { padding:10px 12px; border-radius:6px; margin-bottom:12px; }
            .alert-success { background:#e7f6ef; border:1px solid #bfe3cf; color:#1f7a4c; }
            .form-group, .input-field { margin-bottom:12px; display:block; }
            label { display:block; margin-bottom:6px; color: var(--muted); font-size:13px; }
            input, select, textarea { width:100%; padding:10px; border:1px solid var(--border); border-radius:6px; font-size:14px; }
            footer { padding:16px 20px; color: var(--muted); font-size:13px; text-align:center; }
            @media (max-width: 900px) {
                .layout { flex-direction:column; }
                .sidebar { width:100%; border-right:none; border-bottom:1px solid var(--border); }
                main.container { padding:16px; }
            }
        </style>
    </head>
    <body>
        @auth
            <header>
                @include('layouts.navbars.navbar')
            </header>
            <div class="layout">
                @include('layouts.navbars.sidebar')
                <main class="container">
                    @yield('content')
                </main>
            </div>
            @include('layouts.footer')
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
        @else
            <header>
                @include('layouts.navbars.navbar')
            </header>
            <main class="container">
                @yield('content')
            </main>
            @include('layouts.footer')
        @endauth

        @stack('js')
    </body>
</html>
