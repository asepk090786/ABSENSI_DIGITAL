<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h6>Sistem Absensi Digital</h6>
            <p>Platform manajemen absensi, jadwal, dan nilai siswa terintegrasi untuk sekolah modern.</p>
        </div>
        <div class="footer-section">
            <h6>Navigasi</h6>
            <ul>
                <li><a href="{{ route('home') }}">Dashboard</a></li>
                <li><a href="{{ route('login') }}">Login</a></li>
                @if (Route::has('register'))
                    <li><a href="{{ route('register') }}">Register</a></li>
                @endif
            </ul>
        </div>
        <div class="footer-section">
            <h6>Informasi</h6>
            <ul>
                <li><a href="#">Tentang Kami</a></li>
                <li><a href="#">Kontak</a></li>
                <li><a href="#">Privacy Policy</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; {{ now()->year }} Sistem Absensi Digital. Semua Hak Dilindungi.</p>
    </div>
</footer>

<style>
    .footer {
        background: #f5f5f5;
        border-top: 1px solid var(--gray-border);
        padding: 40px 20px 20px;
        margin-top: 40px;
    }

    .footer-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
        max-width: 1200px;
        margin: 0 auto 20px;
    }

    .footer-section h6 {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 15px;
        color: #333;
    }

    .footer-section p {
        font-size: 14px;
        color: #666;
        line-height: 1.6;
    }

    .footer-section ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-section ul li {
        margin-bottom: 8px;
    }

    .footer-section a {
        color: #666;
        transition: color 0.3s ease;
    }

    .footer-section a:hover {
        color: var(--primary-color);
    }

    .footer-bottom {
        text-align: center;
        padding-top: 20px;
        border-top: 1px solid var(--gray-border);
        color: #999;
        font-size: 13px;
    }
</style>
