<nav class="navbar">
    <div class="nav-wrapper">
        <a href="{{ route('home') }}" class="brand-logo">
            <i class="material-icons">school</i> Absensi Digital
        </a>

        <!-- Mobile Menu Toggle -->
        <a href="#" data-target="mobile-demo" class="sidenav-trigger menu-toggle">
            <i class="material-icons">menu</i>
        </a>

        <ul class="right hide-on-med-and-down">
            <li class="user-menu-item">
                <a href="#" class="dropdown-trigger user-dropdown-trigger" data-target="user-dropdown">
                    <i class="material-icons">account_circle</i>
                    <span>{{ auth()->user()->name ?? 'User' }}</span>
                    <i class="material-icons">arrow_drop_down</i>
                </a>
                <ul id="user-dropdown" class="dropdown-content user-dropdown">
                    <li class="dropdown-header">
                        <i class="material-icons">account_circle</i>
                        <div>
                            <div class="user-name">{{ auth()->user()->name ?? 'User' }}</div>
                            <div class="user-role">Administrator</div>
                        </div>
                    </li>
                    <li class="divider"></li>
                    <li><a href="#"><i class="material-icons">person</i> Profile</a></li>
                    <li class="divider"></li>
                    <li>
                        <a href="#" class="menu-action">
                            <i class="material-icons">settings</i> Pengaturan
                        </a>
                    </li>
                    <li>
                        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="menu-logout">
                            <i class="material-icons">exit_to_app</i> Keluar
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>

<style>
    :root {
        --primary: #5366dd;
        --primary-dark: #3f4fb8;
        --gray-50: #fafbfc;
        --gray-100: #f5f7fa;
        --gray-200: #ebedf0;
        --gray-300: #dfe3e8;
        --gray-500: #8891a1;
        --gray-600: #556987;
        --gray-700: #2e3d5a;
        --danger: #ff5757;
    }

    .nav-wrapper {
        padding: 0 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 64px;
        width: 100%;
    }

    .brand-logo {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 1.3rem !important;
        font-weight: 700;
        color: #fff !important;
        margin-left: 0 !important;
        white-space: nowrap;
        letter-spacing: 0.5px;
    }

    .brand-logo i {
        font-size: 28px;
    }

    .navbar .right {
        display: flex !important;
        align-items: center;
        list-style: none;
        margin: 0;
        padding: 0;
        height: 100%;
    }

    .user-menu-item {
        height: 100%;
        display: flex;
        align-items: center;
        margin: 0;
        position: relative;
    }

    .user-dropdown-trigger {
        display: flex !important;
        align-items: center;
        gap: 10px;
        color: #fff !important;
        padding: 0 16px !important;
        height: 100%;
        font-size: 14px;
        font-weight: 600;
        transition: background 0.3s ease;
        cursor: pointer;
    }

    .user-dropdown-trigger:hover {
        background-color: rgba(255, 255, 255, 0.15);
    }

    .user-dropdown-trigger i {
        font-size: 20px;
    }

    .user-dropdown-trigger span {
        display: inline-block;
        max-width: 130px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Dropdown Container */
    .user-dropdown {
        right: 0 !important;
        left: auto !important;
        min-width: 280px;
        top: 100% !important;
        z-index: 9999 !important;
        background: #fff !important;
        box-shadow: 0 6px 20px rgba(0,0,0,0.12) !important;
        border-radius: 8px !important;
        padding: 0 !important;
        margin: 12px 0 0 0 !important;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.08) !important;
    }

    .user-dropdown li {
        border: none !important;
        list-style: none;
        margin: 0;
    }

    /* Header Section */
    .user-dropdown .dropdown-header {
        display: flex !important;
        align-items: center;
        gap: 14px;
        padding: 18px 20px !important;
        background: var(--gray-50);
        color: var(--gray-700);
        border-bottom: 1px solid var(--gray-200);
    }

    .user-dropdown .dropdown-header i {
        font-size: 36px;
        color: var(--primary);
        min-width: 36px;
        text-align: center;
    }

    .user-name {
        font-weight: 700;
        font-size: 15px;
        color: var(--gray-700);
        line-height: 1.3;
    }

    .user-role {
        font-size: 12px;
        color: var(--gray-500);
        margin-top: 4px;
        font-weight: 500;
    }

    /* Menu Items */
    .user-dropdown li a {
        display: flex !important;
        align-items: center;
        gap: 14px;
        padding: 13px 20px !important;
        color: var(--gray-700) !important;
        font-size: 14px !important;
        font-weight: 500;
        transition: all 0.2s ease !important;
        text-decoration: none !important;
        white-space: nowrap;
    }

    .user-dropdown li a i {
        font-size: 20px;
        min-width: 20px;
        color: var(--gray-500);
    }

    .user-dropdown li a:hover {
        background-color: var(--gray-50) !important;
        color: var(--primary) !important;
    }

    .user-dropdown li a:hover i {
        color: var(--primary) !important;
    }

    /* Logout Button Special Styling */
    .user-dropdown li a.menu-logout {
        color: var(--danger) !important;
        border-top: 1px solid var(--gray-200);
        font-weight: 600;
    }

    .user-dropdown li a.menu-logout i {
        color: var(--danger) !important;
    }

    .user-dropdown li a.menu-logout:hover {
        background-color: #fef3f3 !important;
        color: #ff4444 !important;
    }

    .user-dropdown li a.menu-logout:hover i {
        color: #ff4444 !important;
    }

    /* Dividers */
    .user-dropdown .divider {
        height: 1px;
        background-color: var(--gray-200) !important;
        margin: 0 !important;
    }

    .menu-toggle {
        cursor: pointer !important;
        color: #fff !important;
        display: none !important;
    }

    .menu-toggle i {
        font-size: 24px;
    }

    @media (max-width: 768px) {
        .menu-toggle {
            display: flex !important;
            align-items: center;
        }

        .navbar .right {
            display: none !important;
        }
    }
</style>
