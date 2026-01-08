<nav class="navbar">
    <div class="nav-wrapper">
        <a href="{{ route('home') }}" class="brand-logo">
            <i class="material-icons">school</i> Absensi Digital
        </a>

        <ul class="right hide-on-med-and-down">
            <li><a href="{{ route('home') }}" class="nav-link"><i class="material-icons left">home</i>Home</a></li>
            @if (Route::has('register'))
                <li><a href="{{ route('register') }}" class="nav-link"><i class="material-icons left">person_add</i>Register</a></li>
            @endif
            <li><a href="{{ route('login') }}" class="btn btn-primary" style="margin: 0 10px;"><i class="material-icons left">login</i>Login</a></li>
        </ul>
    </div>
</nav>

<style>
    .nav-wrapper {
        padding: 0 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 64px;
    }

    .brand-logo {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.5rem !important;
        font-weight: 600;
        color: #fff;
    }

    .brand-logo i {
        font-size: 28px;
    }

    .navbar .right {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .nav-link {
        display: flex;
        align-items: center;
        padding: 8px 12px !important;
        color: #fff !important;
        transition: opacity 0.3s ease;
    }

    .nav-link:hover {
        opacity: 0.9;
    }
</style>
