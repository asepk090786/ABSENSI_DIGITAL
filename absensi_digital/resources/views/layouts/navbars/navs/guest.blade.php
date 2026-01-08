<div class="topbar">
    <div class="brand">Absensi Digital</div>
    <div style="display:flex; align-items:center; gap:12px; font-size:14px;">
        <a href="{{ route('home') }}">Dashboard</a>
        @if (Route::has('register'))
            <a href="{{ route('register') }}">Register</a>
        @endif
        <a href="{{ route('login') }}">Login</a>
    </div>
</div>
