<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title', 'SIMADIS — Absensi Digital')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2563EB">

    <link rel="icon" type="image/png" href="{{ asset('images/icon_simadisnew.png') }}">

    <!-- Font Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tabler CSS -->
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.0.0/dist/tabler-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler-flags.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler-vendors.min.css" rel="stylesheet">

    <style>
        /* ========== VARIABLES ========== */
        :root {
            --tblr-font-family-sans-serif: 'Poppins', system-ui, -apple-system, sans-serif;
            --tblr-sidebar-width: 15.5rem;
            --tblr-navbar-height: 3rem;
            --primary: #2563EB;
            --primary-rgb: 37, 99, 235;
            --primary-dark: #1d4ed8;
            --primary-light: #dbeafe;
            --accent: #10B981;
            --accent-rgb: 16, 185, 129;
            --accent-dark: #059669;
            --accent-light: #d1fae5;
            --sidebar-bg: #0f172a;
            --sidebar-text: #cbd5e1;
            --sidebar-hover: rgba(255,255,255,0.08);
            --sidebar-active: rgba(37,99,235,0.35);
            --sidebar-group-title: #64748b;
            --topbar-bg: #ffffff;
            --topbar-border: #e2e8f0;
            --body-bg: #f1f5f9;
            --card-bg: #ffffff;
            --card-border: #e2e8f0;
            --card-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.03);
            --card-shadow-hover: 0 10px 25px -5px rgba(0,0,0,0.08), 0 4px 10px -6px rgba(0,0,0,0.04);
        }

        /* ========== RESET & BASE ========== */
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            font-family: var(--tblr-font-family-sans-serif);
            background: var(--body-bg);
            color: #334155;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            margin: 0;
            min-height: 100vh;
            overflow-x: hidden;
            padding-top: max(env(safe-area-inset-top), 0px);
            padding-bottom: max(env(safe-area-inset-bottom), 0px);
            padding-left: max(env(safe-area-inset-left), 0px);
            padding-right: max(env(safe-area-inset-right), 0px);
        }
        html {
            -webkit-text-size-adjust: 100%;
            text-size-adjust: 100%;
            background: var(--body-bg);
        }

        /* ========== LAYOUT STRUCTURE ========== */
        .app-layout {
            display: flex;
            min-height: 100vh;
        }

        /* ========== SIDEBAR ========== */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: var(--tblr-sidebar-width);
            background: var(--sidebar-bg);
            z-index: 1040;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }
        .sidebar.collapsed {
            transform: translateX(-100%);
        }

        /* Sidebar Brand */
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            text-decoration: none !important;
            min-height: var(--tblr-navbar-height);
        }
        .sidebar-brand-icon {
            width: 2.4rem;
            height: 2.4rem;
            border-radius: 0.6rem;
            background: linear-gradient(135deg, var(--primary), #3b82f6);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .sidebar-brand-icon img {
            width: 1.6rem;
            height: 1.6rem;
            object-fit: contain;
        }
        .sidebar-brand-text {
            color: #f1f5f9;
            font-weight: 700;
            font-size: 0.95rem;
            line-height: 1.2;
            letter-spacing: -0.02em;
        }
        .sidebar-brand-sub {
            color: #64748b;
            font-size: 0.65rem;
            display: block;
            font-weight: 400;
            letter-spacing: 0;
        }

        /* Sidebar Scroll */
        .sidebar-nav-wrap {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.1) transparent;
            padding: 0.5rem 0.75rem;
        }
        .sidebar-nav-wrap::-webkit-scrollbar { width: 4px; }
        .sidebar-nav-wrap::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 4px; }

        /* Sidebar Nav */
        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
        }

        .sidebar-admin-nav {
            gap: 0.35rem;
        }
        .sidebar-admin-group {
            padding: 0.2rem 0 0.15rem;
        }
        .sidebar-admin-group-label {
            font-size: 0.64rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--sidebar-group-title);
            padding: 0.8rem 0.7rem 0.35rem;
            font-weight: 700;
        }
        .sidebar-admin-trigger,
        .sidebar-admin-link,
        .sidebar-admin-sublink {
            width: 100%;
            border: 0;
            background: transparent;
            color: var(--sidebar-text);
            text-align: left;
            display: flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.55rem 0.75rem;
            border-radius: 0.5rem;
            font: inherit;
            font-size: 0.82rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .sidebar-admin-trigger:hover,
        .sidebar-admin-link:hover,
        .sidebar-admin-sublink:hover {
            background: var(--sidebar-hover);
            color: #f1f5f9;
        }
        .sidebar-admin-trigger.is-active,
        .sidebar-admin-link.is-active,
        .sidebar-admin-sublink.is-active {
            background: var(--sidebar-active);
            color: #ffffff;
            font-weight: 600;
            box-shadow: inset 3px 0 0 var(--primary);
        }
        .sidebar-admin-trigger .nav-arrow {
            margin-left: auto;
            font-size: 0.7rem;
            transition: transform 0.2s ease;
            opacity: 0.6;
        }
        .sidebar-admin-trigger[aria-expanded="true"] .nav-arrow {
            transform: rotate(180deg);
            opacity: 1;
        }
        .sidebar-admin-submenu {
            display: grid;
            grid-template-rows: 0fr;
            transition: grid-template-rows 0.22s ease;
        }
        .sidebar-admin-submenu.is-open {
            grid-template-rows: 1fr;
        }
        .sidebar-admin-submenu-inner {
            overflow: hidden;
            margin-left: 1rem;
            padding-left: 0.75rem;
            border-left: 1px solid rgba(148, 163, 184, 0.24);
        }
        .sidebar-admin-sublink {
            font-size: 0.78rem;
            padding: 0.4rem 0.7rem;
            opacity: 0.85;
            margin-top: 0.1rem;
        }
        .sidebar-admin-sublink:hover { opacity: 1; }

        /* Sidebar Group Title */
        .sidebar-group-title {
            font-size: 0.64rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--sidebar-group-title);
            padding: 0.85rem 0.75rem 0.3rem;
            font-weight: 600;
        }

        /* Sidebar Nav Item */
        .sidebar-nav .nav-item {
            position: relative;
        }
        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            padding: 0.55rem 0.75rem;
            border-radius: 0.5rem;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 0.82rem;
            font-weight: 500;
            transition: all 0.15s ease;
            cursor: pointer;
            white-space: nowrap;
            position: relative;
        }
        .sidebar-nav .nav-link:hover {
            background: var(--sidebar-hover);
            color: #f1f5f9;
        }
        .sidebar-nav .nav-link.active {
            background: var(--sidebar-active);
            color: #ffffff;
            font-weight: 600;
            box-shadow: inset 3px 0 0 var(--primary);
        }
        .sidebar-nav .nav-link i {
            font-size: 1.15rem;
            width: 1.25rem;
            text-align: center;
            flex-shrink: 0;
        }
        .sidebar-nav .nav-link .nav-arrow {
            margin-left: auto;
            font-size: 0.7rem;
            transition: transform 0.2s ease;
            opacity: 0.6;
        }
        .sidebar-nav .nav-link[aria-expanded="true"] .nav-arrow {
            transform: rotate(90deg);
            opacity: 1;
        }

        /* Sidebar Sub Nav */
        .sidebar-subnav {
            list-style: none;
            padding: 0 0.25rem 0.15rem 1.6rem;
            margin: 0;
        }
        .sidebar-subnav .nav-link {
            font-size: 0.74rem;
            padding: 0.35rem 0.6rem;
            opacity: 0.85;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar-subnav .nav-link:hover { opacity: 1; }
        .sidebar-subnav .nav-link i {
            font-size: 0.4rem;
            color: #94a3b8;
            flex-shrink: 0;
        }

        /* Sidebar Footer */
        .sidebar-footer {
            padding: 0.75rem 1rem;
            border-top: 1px solid rgba(255,255,255,0.08);
            font-size: 0.7rem;
            color: #64748b;
            text-align: center;
        }

        /* ========== MAIN CONTENT AREA ========== */
        .main-content {
            flex: 1;
            min-width: 0;
            margin-left: var(--tblr-sidebar-width);
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .main-content.sidebar-hidden {
            margin-left: 0;
        }

        /* ========== TOP BAR ========== */
        .topbar {
            background: var(--topbar-bg);
            border-bottom: 1px solid var(--topbar-border);
            height: var(--tblr-navbar-height);
            min-height: var(--tblr-navbar-height);
            display: flex;
            align-items: center;
            padding: 0 1.25rem;
            position: sticky;
            top: 0;
            z-index: 1060;
            gap: 0.75rem;
        }
        .topbar-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.5rem;
            border: 1px solid var(--topbar-border);
            background: transparent;
            cursor: pointer;
            color: #475569;
            font-size: 1.2rem;
            transition: all 0.15s ease;
            flex-shrink: 0;
        }
        .topbar-toggle:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }
        .topbar-brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            text-decoration: none !important;
            flex-shrink: 0;
        }
        .topbar-brand-icon {
            width: 2rem;
            height: 2rem;
            border-radius: 0.5rem;
            background: linear-gradient(135deg, var(--primary), #3b82f6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 0.85rem;
        }
        .topbar-brand-text {
            font-weight: 700;
            font-size: 0.9rem;
            color: #1e293b;
            line-height: 1.2;
            letter-spacing: -0.02em;
        }
        .topbar-brand-sub {
            font-size: 0.62rem;
            color: #94a3b8;
            display: block;
            line-height: 1;
        }

        .topbar-spacer { flex: 1; }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            flex-shrink: 0;
        }
        .topbar-actions .btn-icon {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.5rem;
            border: 1px solid transparent;
            background: transparent;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.15rem;
            transition: all 0.15s ease;
        }
        .topbar-actions .btn-icon:hover {
            background: #f1f5f9;
            border-color: var(--topbar-border);
            color: #334155;
        }

        /* Topbar dropdown nav */
        .topbar-nav {
            display: flex;
            align-items: center;
            gap: 0.2rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .topbar-nav .nav-link {
            color: #475569;
            font-weight: 500;
            font-size: 0.82rem;
            padding: 0.35rem 0.6rem;
            border-radius: 0.4rem;
            text-decoration: none;
            transition: all 0.15s ease;
            white-space: nowrap;
        }
        .topbar-nav .nav-link:hover {
            background: #f1f5f9;
            color: #1e293b;
        }
        .topbar-nav .nav-link.active {
            background: var(--primary-light);
            color: var(--primary);
            font-weight: 600;
        }

        /* User avatar in topbar */
        .topbar-user-avatar {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none !important;
        }
        .topbar-user-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 0 0 3px rgba(37,99,235,0.2);
        }

        /* ========== PAGE CONTENT ========== */
        .page-content {
            flex: 1;
            padding: 1.25rem;
        }

        /* ========== MOBILE OVERLAY ========== */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.5);
            z-index: 1039;
            transition: opacity 0.3s ease;
        }
        .sidebar-overlay.show {
            display: block;
        }

        /* ========== CARDS ========== */
        .card {
            border-radius: 0.75rem;
            border: 1px solid var(--card-border);
            box-shadow: var(--card-shadow);
            background: var(--card-bg);
            transition: box-shadow 0.2s ease;
            margin-bottom: 1.25rem;
        }
        .card:hover {
            box-shadow: var(--card-shadow-hover);
        }
        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--card-border);
            padding: 0.85rem 1.1rem !important;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .card-header:first-child {
            border-radius: 0.75rem 0.75rem 0 0;
        }
        .card-body {
            padding: 1.1rem;
        }
        .card-title {
            font-weight: 600;
            font-size: 0.92rem;
            color: #1e293b;
            margin: 0;
            letter-spacing: -0.01em;
        }
        .card-footer {
            background: transparent;
            border-top: 1px solid var(--card-border);
            padding: 0.75rem 1.1rem;
        }

        /* ========== STAT CARDS ========== */
        .stat-card {
            border-radius: 0.75rem;
            border: 1px solid var(--card-border);
            box-shadow: var(--card-shadow);
            background: var(--card-bg);
            padding: 1.1rem;
            transition: all 0.2s ease;
            height: 100%;
        }
        .stat-card:hover {
            box-shadow: var(--card-shadow-hover);
            transform: translateY(-1px);
        }
        .stat-icon {
            width: 2.8rem;
            height: 2.8rem;
            border-radius: 0.65rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }
        .stat-label {
            font-size: 0.72rem;
            color: #94a3b8;
            font-weight: 500;
            margin-bottom: 0.1rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1;
            letter-spacing: -0.02em;
            color: #1e293b;
        }
        .stat-change {
            font-size: 0.72rem;
            font-weight: 500;
            margin-top: 0.3rem;
        }

        /* ========== QUICK MENU CARDS ========== */
        .quick-menu-card {
            border-radius: 0.65rem;
            border: 1px solid var(--card-border);
            padding: 1rem 0.75rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 0.5rem;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s ease;
            background: var(--card-bg);
            height: 100%;
        }
        .quick-menu-card:hover {
            border-color: var(--primary);
            box-shadow: 0 4px 12px rgba(37,99,235,0.1);
            transform: translateY(-2px);
            text-decoration: none;
            color: inherit;
        }
        .quick-menu-card .qm-icon {
            width: 2.6rem;
            height: 2.6rem;
            border-radius: 0.6rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #fff;
        }
        .quick-menu-card .qm-label {
            font-weight: 500;
            font-size: 0.78rem;
            color: #475569;
        }

        /* ========== PAGE HEADER ========== */
        .page-header {
            margin-bottom: 1.25rem;
        }
        .page-pretitle {
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            margin-bottom: 0.1rem;
            font-weight: 600;
        }
        .page-title {
            font-weight: 700;
            font-size: 1.25rem;
            letter-spacing: -0.02em;
            color: #1e293b;
            margin: 0;
        }

        /* ========== DASHBOARD TEXT ========== */
        .card-category {
            color: #64748b !important;
            font-size: 0.78rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .card-title {
            font-weight: 600;
            font-size: 0.92rem;
            color: #1e293b;
            margin: 0;
            letter-spacing: -0.01em;
        }

        /* ========== FORM CONTROLS ========== */
        .form-control, .form-select {
            border-radius: 0.5rem;
            border: 1.5px solid var(--card-border);
            font-size: 0.85rem;
            padding: 0.55rem 0.75rem;
            transition: all 0.15s ease;
            font-family: var(--tblr-font-family-sans-serif);
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }
        .form-label {
            font-weight: 500;
            font-size: 0.82rem;
            color: #475569;
            margin-bottom: 0.35rem;
        }

        /* ========== BUTTONS ========== */
        .btn {
            border-radius: 0.5rem;
            font-weight: 500;
            font-size: 0.82rem;
            padding: 0.5rem 1rem;
            transition: all 0.15s ease;
            font-family: var(--tblr-font-family-sans-serif);
        }
        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
        }
        .btn-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37,99,235,0.3);
        }
        .btn-success {
            background: var(--accent);
            border-color: var(--accent);
        }
        .btn-success:hover {
            background: var(--accent-dark);
            border-color: var(--accent-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16,185,129,0.3);
        }
        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
        }
        .btn-outline-primary:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        /* ========== TABLES ========== */
        .table thead th {
            background: #f8fafc;
            border-bottom: 2px solid var(--card-border);
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
            padding: 0.65rem 0.75rem;
            white-space: nowrap;
        }
        .table tbody td {
            padding: 0.65rem 0.75rem;
            vertical-align: middle;
            font-size: 0.82rem;
            color: #475569;
            border-bottom: 1px solid #f1f5f9;
        }
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        .table tbody tr:hover {
            background: #f8fafc;
        }

        /* ========== ATTENDANCE SUMMARY ========== */
        .attendance-summary-card {
            border-radius: 0.75rem;
            border: 1px solid var(--card-border);
            box-shadow: var(--card-shadow);
            background: var(--card-bg);
        }
        .attendance-card-header {
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 1rem;
        }
        .attendance-header-icon {
            width: 2.5rem;
            height: 2.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        .attendance-items {
            display: flex;
            flex-wrap: wrap;
            margin: -0.5rem;
        }
        .attendance-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            border-radius: 0.5rem;
            background: #f8fafc;
            margin: 0.5rem;
            flex: 1 1 calc(50% - 1rem);
            min-width: 140px;
        }
        .attendance-item-icon {
            width: 2.2rem;
            height: 2.2rem;
            border-radius: 0.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }
        .attendance-item-content {
            display: flex;
            flex-direction: column;
            gap: 0.1rem;
        }
        .attendance-item-label {
            font-size: 0.72rem;
            font-weight: 500;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .attendance-item-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.1;
        }
        .attendance-summary-footer {
            border-top: 1px solid #f1f5f9;
            padding-top: 0.75rem;
            color: #475569;
        }
        .attendance-item-success .attendance-item-icon { background-color: #10b981; color: #fff; }
        .attendance-item-warning .attendance-item-icon { background-color: #ffc107; color: #1e293b; }
        .attendance-item-info .attendance-item-icon { background-color: #0dcaf0; color: #fff; }
        .attendance-item-secondary .attendance-item-icon { background-color: #6c757d; color: #fff; }
        .attendance-item-danger .attendance-item-icon { background-color: #dc3545; color: #fff; }

        /* ========== BADGES ========== */
        .badge,
        .badge[class*="bg-"],
        .badge-primary,
        .badge-success,
        .badge-info,
        .badge-warning,
        .badge-danger,
        .badge-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.72rem;
            line-height: 1.2;
            letter-spacing: 0.01em;
            padding: 0.38em 0.7em;
            border-radius: 0.38rem;
        }
        .badge-primary {
            background: var(--primary-light);
            color: #1e40af !important;
        }
        .badge-success {
            background: var(--accent-light);
            color: #065f46 !important;
        }
        .badge-warning {
            background: #fbbf24;
            color: #1f2937 !important;
        }
        .badge-info {
            background: #0ea5e9;
            color: #fff !important;
        }
        .badge-danger {
            background: #ef4444;
            color: #fff !important;
        }
        .badge-secondary {
            background: #6b7280;
            color: #fff !important;
        }

        /* Pastel / light badges must keep dark readable text. */
        .badge.bg-primary-subtle,
        .badge.bg-success-subtle,
        .badge.bg-warning-subtle,
        .badge.bg-info-subtle,
        .badge.bg-danger-subtle,
        .badge.bg-secondary-subtle,
        .badge.bg-light,
        .badge.bg-primary-light,
        .badge.bg-blue-lt,
        .badge.bg-pink-lt,
        .badge.bg-green-lt,
        .badge.bg-red-lt,
        .badge.bg-azure-lt,
        .badge.bg-indigo-lt,
        .badge.bg-purple-lt,
        .badge.bg-teal-lt,
        .badge.bg-cyan-lt,
        .badge.bg-orange-lt,
        .badge.bg-yellow-lt,
        .badge.bg-warning.text-dark,
        .badge.bg-light.text-primary,
        .badge.bg-primary-light.text-primary {
            color: #111827 !important;
        }

        .badge[class*="bg-"] {
            color: #fff !important;
        }

        .badge.bg-warning,
        .badge.bg-warning.text-dark,
        .badge.bg-warning-subtle,
        .badge.bg-orange-lt,
        .badge.bg-yellow-lt {
            color: #1f2937 !important;
        }
        .badge-laki {
            background: #2563eb;
            color: #fff !important;
        }
        .badge-perempuan {
            background: #db2777;
            color: #fff !important;
        }

        /* ========== ALERTS ========== */
        .alert {
            border-radius: 0.65rem;
            border: none;
            font-size: 0.85rem;
            padding: 0.85rem 1.1rem;
        }

        /* ========== DROPDOWN ========== */
        .dropdown {
            position: relative;
        }
        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            border-radius: 0.65rem;
            border: 1px solid var(--card-border);
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 4px 10px -6px rgba(0,0,0,0.05);
            padding: 0.35rem 0;
            z-index: 1100;
            display: none;
        }
        .dropdown-menu.dropdown-menu-end {
            right: 0;
            left: auto;
        }
        .dropdown-menu.show {
            display: block;
        }
        .dropdown-item {
            font-size: 0.82rem;
            padding: 0.55rem 1rem;
            font-weight: 500;
        }

        /* ========== TOAST ========== */
        .toast {
            border-radius: 0.65rem;
            border: 1px solid var(--card-border);
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15);
        }
        .toast-header {
            border-bottom: 1px solid rgba(255,255,255,0.15);
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 991.98px) {
            .sidebar {
                width: min(82vw, 19rem);
                transform: translateX(-100%);
                box-shadow: 10px 0 30px rgba(0,0,0,0.2);
            }
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0 !important;
            }
            .topbar-toggle {
                display: inline-flex;
            }
            .topbar-brand .topbar-brand-sub { display: none; }
            .topbar-nav { display: none; }
            .page-content {
                padding: 1rem 0.75rem;
            }
            .card-body { padding: 0.85rem; }
        }

        @media (max-width: 576px) {
            :root {
                --tblr-navbar-height: 2.75rem;
            }
            .topbar {
                padding: 0 0.7rem;
                gap: 0.5rem;
                height: var(--tblr-navbar-height);
            }
            .topbar-brand-text {
                font-size: 0.82rem;
            }
            .topbar-brand-sub {
                display: none;
            }
            .topbar-actions .btn-icon,
            .topbar-toggle {
                width: 2rem;
                height: 2rem;
                font-size: 1rem;
            }
            .page-content {
                padding: 0.75rem 0.5rem;
            }
            .card-body { padding: 0.75rem; }
            .stat-value { font-size: 1.25rem; }
            .page-title { font-size: 1.1rem; }
            .btn,
            .form-control,
            .form-select,
            .btn-sm,
            .btn-lg {
                min-height: 2.5rem;
            }
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }

        @media (max-width: 420px) {
            .sidebar {
                width: min(86vw, 18rem);
            }
            .topbar-brand {
                min-width: 0;
            }
            .topbar-user-avatar {
                width: 1.8rem;
                height: 1.8rem;
                font-size: 0.72rem;
            }
            .page-content {
                padding: 0.6rem 0.4rem;
            }
            .card {
                border-radius: 0.65rem;
            }
            .card-header {
                padding: 0.7rem 0.8rem !important;
            }
        }

        /* ========== UTILITIES ========== */
        .text-primary { color: var(--primary) !important; }
        .text-accent { color: var(--accent) !important; }
        .bg-primary { background: var(--primary) !important; }
        .bg-primary-light { background: var(--primary-light) !important; }
        .bg-accent { background: var(--accent) !important; }
        .bg-accent-light { background: var(--accent-light) !important; }

        /* Welcome banner */
        .welcome-banner {
            background: linear-gradient(135deg, #2563EB 0%, #1d4ed8 50%, #1e40af 100%);
            border-radius: 0.75rem;
            padding: 1.25rem 1.5rem;
            color: #fff;
            margin-bottom: 1.25rem;
        }
        .welcome-banner h3 {
            font-weight: 700;
            margin-bottom: 0.25rem;
            font-size: 1.15rem;
        }
        .welcome-banner p {
            opacity: 0.85;
            margin: 0;
            font-size: 0.85rem;
        }

        /* Timeline (preserved from original) */
        .timeline { position: relative; padding-left: 2rem; }
        .timeline::before {
            content: ''; position: absolute; left: .55rem; top: .3rem; bottom: .3rem;
            width: 2px; background: var(--card-border);
        }
        .timeline .t-item { position: relative; margin-bottom: 1.2rem; }
        .timeline .t-item::before {
            content: ''; position: absolute; left: -1.55rem; top: .35rem;
            width: .75rem; height: .75rem; border-radius: 50%;
            background: var(--primary); border: 2px solid #fff;
            box-shadow: 0 0 0 2px var(--primary);
        }
        .timeline .t-time { font-size: .7rem; color: #94a3b8; margin-top: .1rem; }
    </style>

    @stack('css')
</head>
<body>
<div class="app-layout">
    <!-- Sidebar Overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <!-- ========== DESKTOP SIDEBAR ========== -->
    <aside class="sidebar" id="sidebar">
        <!-- Brand -->
        <a href="{{ route('home') }}" class="sidebar-brand">
            <span class="sidebar-brand-icon">
                <img src="{{ asset('images/icon_simadisnew.png') }}" alt="S">
            </span>
            <span>
                <span class="sidebar-brand-text">SIMADIS</span>
                <span class="sidebar-brand-sub">Absensi Digital</span>
            </span>
        </a>

        <!-- Nav -->
        <nav class="sidebar-nav-wrap">
            @php
                $user = auth()->user();
                $isGuru = $user && $user->hasAnyRole(['Guru','Guru Mapel','Guru Kelas','Wali Kelas','Guru BK','Guru Piket','Pembina']);
                $isGuruPiket = false;
                if ($isGuru && $user->guru) {
                    $hrPkt = $user->guru->hari_piket ?? [];
                    $todayEng = \Carbon\Carbon::now()->format('l');
                    $map = [
                        'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
                        'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
                    ];
                    $todayIndo = $map[$todayEng] ?? null;
                    $isGuruPiket = in_array($todayIndo, (array) $hrPkt, true) || ($user && $user->hasAnyRole(['Admin','Kepala Sekolah']));
                }
                $isModeAcademic = request()->query('mode') === 'academic';
                $isModePiket = ! $isModeAcademic;
                $isWali = false; $kelasBindaan = null;
                if ($isGuru && $user->guru) {
                    $kelasBindaan = DB::table('kelas')->where('wali_kelas_id', $user->guru->id)->first();
                    $isWali = !is_null($kelasBindaan);
                }
            @endphp

            @php
                $isAdminSidebar = $user && $user->hasAnyRole(['Admin','Kepala Sekolah','Pengawas Pembina']);
                
                // AKADEMIK Group - Jadwal & Akademik submenu
                $isAdminJadwalAkademikActive = request()->routeIs(['jadwal-kbm.*','jadwal_kbm.*','guru_piket.*','rencana_pembelajaran.*','editor_modul.*']);
                
                // AKADEMIK Group - Beban Kerja & Tugas submenu
                $isAdminBebanKerjaActive = request()->routeIs(['tugas_guru.*','tugas_tambahan.*','sk_tugas.*']);
                
                // AKADEMIK Group - Penilaian submenu
                $isAdminPenilaianActive = request()->routeIs(['komponen_nilai.*','nilai_akhir.*','akademik.supervisi']);
                
                // AKADEMIK Group - Main active state
                $isAdminAkademikActive = request()->routeIs(['jadwal-kbm.*','jadwal_kbm.*','komponen_nilai.*','rencana_pembelajaran.*','sk_tugas.*','akademik.*','editor_modul.*','tugas_guru.*','tugas_tambahan.*','guru_piket.*','nilai_akhir.*']);
                
                // MASTER DATA active state (updated for new structure)
                $isAdminMasterActive = request()->routeIs(['sekolah.*','kepala_sekolah.*','wakil_kepala_sekolah.*','guru.*','guru_bk.*','guru_piket.*','pembina.*','tenaga_pendidikan.*','users.*','siswa.*','kelas.*','mata_pelajaran.*','tugas_guru.*','asc_timetable.*','ekskul.*','jenis_pelanggaran.*','role_permission.*','kartu_login.*']);
                
                // ADMINISTRASI PTK active state (new)
                $isAdminPtkActive = request()->routeIs(['administrasi_ptk.*','dokumen_kepegawaian.*','template_dokumen.*','pengembangan.*','pengajuan.*','verifikasi.*']);
                
                // PENGATURAN SISTEM active state
                $isAdminSettingActive = request()->routeIs(['tahun_ajaran.index','setting.semester*','setting.header*','setting.absensi*']);
                
                // BACKUP DATABASE active state
                $isAdminBackupActive = request()->routeIs(['setting.backup']);
                
                // PANDUAN & INFORMASI active state
                $isAdminInfoActive = request()->routeIs(['profile.panduan','setting.about','help.admin.*']);
            @endphp

            @if($isAdminSidebar)
            <ul class="sidebar-nav sidebar-admin-nav">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                        <i class="ti ti-layout-dashboard"></i> Dashboard
                    </a>
                </li>

                <!-- AKADEMIK Group -->
                <li class="nav-item">
                    <div class="sidebar-admin-group">
                        <div class="sidebar-admin-group-label">Akademik</div>
                        
                        <!-- Jadwal & Akademik Submenu -->
                        <button type="button" class="sidebar-admin-trigger {{ $isAdminJadwalAkademikActive ? 'is-active' : '' }}" data-target="#subJadwalAkademikAdmin" aria-expanded="{{ $isAdminJadwalAkademikActive ? 'true' : 'false' }}" aria-controls="subJadwalAkademikAdmin">
                            <i class="ti ti-calendar"></i>
                            <span>Jadwal &amp; Akademik</span>
                            <i class="ti ti-chevron-down nav-arrow"></i>
                        </button>
                        <div class="sidebar-admin-submenu {{ $isAdminJadwalAkademikActive ? 'is-open' : '' }}" id="subJadwalAkademikAdmin">
                            <div class="sidebar-admin-submenu-inner">
                                <a href="{{ route('jadwal-kbm.index') }}" class="sidebar-admin-sublink {{ request()->routeIs(['jadwal-kbm.*','jadwal_kbm.*']) ? 'is-active' : '' }}">Jadwal KBM</a>
                                <a href="{{ route('guru_piket.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('guru_piket.*') ? 'is-active' : '' }}">Jadwal Piket</a>
                                <a href="{{ url('/pengaturan-jam') }}" class="sidebar-admin-sublink {{ request()->is('pengaturan-jam*') || request()->routeIs('jadwal_kbm.*') ? 'is-active' : '' }}">Pengaturan Jam</a>
                                <a href="{{ route('rencana_pembelajaran.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('rencana_pembelajaran.*') ? 'is-active' : '' }}">Modul Ajar</a>
                                @if(request()->routeIs('rencana_pembelajaran.edit'))
                                    <a href="{{ request()->fullUrl() }}" class="sidebar-admin-sublink is-active">Edit Modul Ajar</a>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Beban Kerja & Tugas Submenu -->
                        <button type="button" class="sidebar-admin-trigger {{ $isAdminBebanKerjaActive ? 'is-active' : '' }}" data-target="#subBebanKerjaAdmin" aria-expanded="{{ $isAdminBebanKerjaActive ? 'true' : 'false' }}" aria-controls="subBebanKerjaAdmin">
                            <i class="ti ti-briefcase"></i>
                            <span>Beban Kerja &amp; Tugas</span>
                            <i class="ti ti-chevron-down nav-arrow"></i>
                        </button>
                        <div class="sidebar-admin-submenu {{ $isAdminBebanKerjaActive ? 'is-open' : '' }}" id="subBebanKerjaAdmin">
                            <div class="sidebar-admin-submenu-inner">
                                <a href="{{ route('tugas_guru.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('tugas_guru.*') ? 'is-active' : '' }}">Beban Kerja Guru</a>
                                <a href="{{ route('tugas_guru.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('tugas_guru.*') ? 'is-active' : '' }}">Tugas Guru</a>
                                <a href="{{ route('tugas_tambahan.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('tugas_tambahan.*') ? 'is-active' : '' }}">Tugas Tambahan</a>
                                <a href="{{ route('sk_tugas.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('sk_tugas.*') ? 'is-active' : '' }}">SK Tugas</a>
                            </div>
                        </div>
                        
                        <!-- Penilaian Submenu -->
                        <button type="button" class="sidebar-admin-trigger {{ $isAdminPenilaianActive ? 'is-active' : '' }}" data-target="#subPenilaianAdmin" aria-expanded="{{ $isAdminPenilaianActive ? 'true' : 'false' }}" aria-controls="subPenilaianAdmin">
                            <i class="ti ti-checklist"></i>
                            <span>Penilaian</span>
                            <i class="ti ti-chevron-down nav-arrow"></i>
                        </button>
                        <div class="sidebar-admin-submenu {{ $isAdminPenilaianActive ? 'is-open' : '' }}" id="subPenilaianAdmin">
                            <div class="sidebar-admin-submenu-inner">
                                <a href="{{ route('komponen_nilai.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('komponen_nilai.*') ? 'is-active' : '' }}">Komponen Penilaian</a>
                                <a href="{{ route('nilai_akhir.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('nilai_akhir.*') ? 'is-active' : '' }}">Penilaian Akhir</a>
                                <a href="{{ route('nilai_akhir.rekap') }}" class="sidebar-admin-sublink {{ request()->routeIs('nilai_akhir.rekap') ? 'is-active' : '' }}">Rekap Penilaian</a>
                                @if($user && $user->hasAnyRole(['Admin','Kepala Sekolah','Pengawas Pembina']))
                                <a href="{{ route('akademik.supervisi') }}" class="sidebar-admin-sublink {{ request()->routeIs('akademik.supervisi') || request()->routeIs('akademik.supervisi.*') ? 'is-active' : '' }}">Supervisi</a>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Mata Pelajaran Link -->
                        <a href="{{ route('mata_pelajaran.index') }}" class="sidebar-admin-link {{ request()->routeIs('mata_pelajaran.*') && !request()->routeIs('tugas_guru.*') ? 'is-active' : '' }}">
                            <i class="ti ti-book"></i> Mata Pelajaran
                        </a>
                    </div>
                </li>

                <!-- MASTER DATA Group -->
                <li class="nav-item">
                    <div class="sidebar-admin-group">
                        <div class="sidebar-admin-group-label">Master Data</div>
                        <button type="button" class="sidebar-admin-trigger {{ $isAdminMasterActive ? 'is-active' : '' }}" data-target="#subMasterAdmin" aria-expanded="{{ $isAdminMasterActive ? 'true' : 'false' }}" aria-controls="subMasterAdmin">
                            <i class="ti ti-database"></i>
                            <span>Master Data</span>
                            <i class="ti ti-chevron-down nav-arrow"></i>
                        </button>
                        <div class="sidebar-admin-submenu {{ $isAdminMasterActive ? 'is-open' : '' }}" id="subMasterAdmin">
                            <div class="sidebar-admin-submenu-inner">
                                <a href="{{ route('sekolah.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('sekolah.*') ? 'is-active' : '' }}">Data Sekolah</a>
                                <a href="{{ route('kepala_sekolah.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('kepala_sekolah.*') ? 'is-active' : '' }}">Kepala Sekolah</a>
                                <a href="{{ route('wakil_kepala_sekolah.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('wakil_kepala_sekolah.*') ? 'is-active' : '' }}">Wakil Kepala</a>
                                <a href="{{ route('guru.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('guru.*') ? 'is-active' : '' }}">Tenaga Pendidik</a>
                                <a href="{{ route('guru_bk.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('guru_bk.*') ? 'is-active' : '' }}">Guru BK</a>
                                <a href="{{ route('guru_piket.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('guru_piket.*') ? 'is-active' : '' }}">Guru Piket</a>
                                <a href="{{ route('pembina.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('pembina.*') ? 'is-active' : '' }}">Pembina</a>
                                <a href="{{ route('tenaga_pendidikan.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('tenaga_pendidikan.*') ? 'is-active' : '' }}">Tenaga Pendidikan</a>
                                <a href="{{ route('siswa.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('siswa.*') ? 'is-active' : '' }}">Siswa</a>
                                <a href="{{ route('kelas.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('kelas.*') ? 'is-active' : '' }}">Kelas</a>
                                <a href="{{ route('mata_pelajaran.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('mata_pelajaran.*') && !request()->routeIs('tugas_guru.*') ? 'is-active' : '' }}">Mata Pelajaran</a>
                                <a href="{{ route('ekskul.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('ekskul.*') ? 'is-active' : '' }}">Ekstrakurikuler</a>
                                <a href="{{ route('jenis_pelanggaran.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('jenis_pelanggaran.*') ? 'is-active' : '' }}">Jenis Pelanggaran</a>
                                <a href="{{ route('users.admin') }}" class="sidebar-admin-sublink {{ request()->routeIs('users.admin') || (request()->routeIs('users.index') && request()->input('role') === 'Admin') ? 'is-active' : '' }}">Admin</a>
                                <a href="{{ route('users.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('users.index') && !request()->has('role') ? 'is-active' : '' }}">Akun Pengguna</a>
                                <a href="{{ route('role_permission.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('role_permission.*') ? 'is-active' : '' }}">Role &amp; Permission</a>
                                <a href="{{ route('kartu_login.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('kartu_login.*') ? 'is-active' : '' }}">Kartu Login</a>
                                <a href="{{ route('asc_timetable.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('asc_timetable.*') ? 'is-active' : '' }}">ASC Time Table</a>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- ADMINISTRASI PTK Group (NEW) -->
                <li class="nav-item">
                    <div class="sidebar-admin-group">
                        <div class="sidebar-admin-group-label">Administrasi PTK</div>
                        <button type="button" class="sidebar-admin-trigger {{ $isAdminPtkActive ? 'is-active' : '' }}" data-target="#subAdministrasiPtkAdmin" aria-expanded="{{ $isAdminPtkActive ? 'true' : 'false' }}" aria-controls="subAdministrasiPtkAdmin">
                            <i class="ti ti-folder"></i>
                            <span>Administrasi PTK</span>
                            <i class="ti ti-chevron-down nav-arrow"></i>
                        </button>
                        <div class="sidebar-admin-submenu {{ $isAdminPtkActive ? 'is-open' : '' }}" id="subAdministrasiPtkAdmin">
                            <div class="sidebar-admin-submenu-inner">
                                <a href="{{ route('administrasi_ptk.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('administrasi_ptk.*') ? 'is-active' : '' }}">Administrasi</a>
                                <a href="{{ route('dokumen_kepegawaian.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('dokumen_kepegawaian.*') ? 'is-active' : '' }}">Dokumen Kepegawaian</a>
                                <a href="{{ route('template_dokumen.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('template_dokumen.*') ? 'is-active' : '' }}">Template Dokumen</a>
                                <a href="{{ route('pengembangan.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('pengembangan.*') ? 'is-active' : '' }}">Pengembangan Diri</a>
                                <a href="{{ route('pengajuan.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('pengajuan.*') ? 'is-active' : '' }}">Pengajuan</a>
                                <a href="{{ route('verifikasi.index') }}" class="sidebar-admin-sublink {{ request()->routeIs('verifikasi.*') ? 'is-active' : '' }}">Verifikasi</a>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- PENGATURAN SISTEM Group -->
                <li class="nav-item">
                    <div class="sidebar-admin-group">
                        <div class="sidebar-admin-group-label">Pengaturan Sistem</div>
                        <a href="{{ route('profile.edit') }}" class="sidebar-admin-link {{ request()->routeIs('profile.edit') ? 'is-active' : '' }}">
                            <i class="ti ti-user"></i> Profile
                        </a>
                        <a href="{{ route('setting.tahun_ajaran') }}" class="sidebar-admin-link {{ request()->routeIs('setting.tahun_ajaran*') ? 'is-active' : '' }}">
                            <i class="ti ti-calendar-range"></i> Tahun Ajaran
                        </a>
                        <a href="{{ route('setting.semester') }}" class="sidebar-admin-link {{ request()->routeIs('setting.semester*') ? 'is-active' : '' }}">
                            <i class="ti ti-layers"></i> Semester
                        </a>
                        <a href="{{ route('setting.header') }}" class="sidebar-admin-link {{ request()->routeIs('setting.header*') ? 'is-active' : '' }}">
                            <i class="ti ti-panel-top"></i> Edit Header
                        </a>
                        <a href="{{ route('setting.absensi') }}" class="sidebar-admin-link {{ request()->routeIs('setting.absensi*') ? 'is-active' : '' }}">
                            <i class="ti ti-clock-check"></i> Pengaturan Absensi
                        </a>
                        <a href="{{ route('setting.agenda') }}" class="sidebar-admin-link {{ request()->routeIs('setting.agenda*') ? 'is-active' : '' }}">
                            <i class="ti ti-calendar-event"></i> Pengaturan Agenda
                        </a>
                        <a href="{{ route('setting.menu') }}" class="sidebar-admin-link {{ request()->routeIs('setting.menu*') ? 'is-active' : '' }}">
                            <i class="ti ti-menu-2"></i> Pengaturan Menu
                        </a>
                        <a href="{{ route('setting.editor') }}" class="sidebar-admin-link {{ request()->routeIs('setting.editor*') ? 'is-active' : '' }}">
                            <i class="ti ti-file-code"></i> Editor Modul
                        </a>
                    </div>
                </li>

                <!-- BACKUP DATABASE -->
                <li class="nav-item">
                    <a href="{{ route('setting.backup') }}" class="nav-link {{ request()->routeIs('setting.backup') ? 'active' : '' }}">
                        <i class="ti ti-database"></i> Backup Database
                    </a>
                </li>

                <!-- PANDUAN & INFORMASI Group -->
                <li class="nav-item">
                    <div class="sidebar-admin-group">
                        <div class="sidebar-admin-group-label">Panduan &amp; Informasi</div>
                        <a href="{{ route('profile.panduan') }}" class="sidebar-admin-link {{ request()->routeIs('profile.panduan') ? 'is-active' : '' }}">
                            <i class="ti ti-book"></i> Panduan
                        </a>
                        <a href="{{ route('setting.about') }}" class="sidebar-admin-link {{ request()->routeIs('setting.about') ? 'is-active' : '' }}">
                            <i class="ti ti-info-circle"></i> About
                        </a>
                        <a href="{{ route('help.admin.index') }}" class="sidebar-admin-link {{ request()->routeIs('help.admin.*') ? 'is-active' : '' }}">
                            <i class="ti ti-help-circle"></i> Help
                        </a>
                    </div>
                </li>
            </ul>
            @else
            @php
                $guruEnabledMenus = $menuVisibility['guru'] ?? [];
                $siswaEnabledMenus = $menuVisibility['siswa'] ?? [];
            @endphp
            <ul class="sidebar-nav">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                        <i class="ti ti-layout-dashboard"></i> Dashboard
                    </a>
                </li>

                <!-- Akademik -->
                @if($user && $user->hasAnyRole(['Admin','Kepala Sekolah','Pengawas Pembina','Guru','Guru Mapel','Guru Kelas','Guru BK','Guru Piket','Wali Kelas','Siswa']) && (empty($guruEnabledMenus) || !empty(array_intersect(['akademik_jadwal_kbm','akademik_jadwal_piket','akademik_pengaturan_jam','akademik_pengembangan_diri','akademik_beban_kerja_guru','akademik_sk_tugas','akademik_komponen_penilaian','akademik_mata_pelajaran','akademik_modul_ajar','akademik_editor_modul'], $guruEnabledMenus))))
                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#subAkademik" aria-expanded="{{ request()->routeIs(['jadwal-kbm.*','jadwal_kbm.*','tugas_guru.*','komponen_nilai.*','mata_pelajaran.*','rencana_pembelajaran.*','editor_modul.*']) ? 'true' : 'false' }}">
                        <i class="ti ti-school"></i> Akademik
                        <i class="ti ti-chevron-right nav-arrow"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs(['jadwal-kbm.*','jadwal_kbm.*','tugas_guru.*','komponen_nilai.*','mata_pelajaran.*','rencana_pembelajaran.*','editor_modul.*','akademik.supervisi','akademik.supervisi.*','akademik.tool']) ? 'show' : '' }}" id="subAkademik">
                        <ul class="sidebar-subnav">
                            @if(empty($guruEnabledMenus) || in_array('akademik_jadwal_kbm', $guruEnabledMenus))
                            <li class="nav-item"><a href="{{ route('jadwal-kbm.index') }}" class="nav-link {{ request()->routeIs(['jadwal-kbm.*','jadwal_kbm.*']) ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> Jadwal KBM</a></li>
                            @endif
                            @if(($isGuruPiket || ($user && $user->hasAnyRole(['Admin','Kepala Sekolah']))) && (empty($guruEnabledMenus) || in_array('akademik_jadwal_piket', $guruEnabledMenus)))
                                <li class="nav-item"><a href="{{ route('guru_piket.index') }}" class="nav-link {{ request()->routeIs('guru_piket.*') ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> Jadwal Piket</a></li>
                            @endif
                            @if($user && $user->hasAnyRole(['Admin','Kepala Sekolah']) && (empty($guruEnabledMenus) || in_array('akademik_pengaturan_jam', $guruEnabledMenus)))
                                <li class="nav-item"><a href="{{ url('/pengaturan-jam') }}" class="nav-link {{ request()->is('pengaturan-jam*') || request()->routeIs('jadwal_kbm.*') ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> Pengaturan Jam</a></li>
                            @endif
                            @if($user && $user->hasAnyRole(['Admin','Kepala Sekolah']) && (empty($guruEnabledMenus) || in_array('akademik_pengembangan_diri', $guruEnabledMenus)))
                                <li class="nav-item"><a href="{{ route('pengembangan.index') }}" class="nav-link {{ request()->routeIs('pengembangan.*') ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> Pengembangan Diri</a></li>
                            @endif
                            @if($user && ($user->guru_id || $user->siswa_id) && (empty($guruEnabledMenus) || in_array('akademik_pengembangan_diri', $guruEnabledMenus)))
                                <li class="nav-item"><a href="{{ route('pengembangan.my_certificates') }}" class="nav-link {{ request()->routeIs('pengembangan.my_certificates') ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> Pengembangan Diri</a></li>
                            @endif
                            @if(!($user && $user->hasRole('Siswa')) && (empty($guruEnabledMenus) || in_array('akademik_beban_kerja_guru', $guruEnabledMenus)))
                                <li class="nav-item"><a href="{{ route('tugas_guru.index') }}" class="nav-link {{ request()->routeIs('tugas_guru.*') ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> Beban Kerja Guru</a></li>
                            @endif
                            @if(!($user && $user->hasRole('Siswa')) && (empty($guruEnabledMenus) || in_array('akademik_sk_tugas', $guruEnabledMenus)))
                                <li class="nav-item"><a href="{{ route('sk_tugas.index') }}" class="nav-link {{ request()->routeIs('sk_tugas.*') ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> SK TUGAS</a></li>
                            @endif
                            @if($isGuru && (empty($guruEnabledMenus) || in_array('akademik_komponen_penilaian', $guruEnabledMenus)))
                                <li class="nav-item"><a href="{{ route('komponen_nilai.index') }}" class="nav-link {{ request()->routeIs('komponen_nilai.*') ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> Komponen Penilaian</a></li>
                            @endif
                            @if($isGuru && (empty($guruEnabledMenus) || in_array('akademik_mata_pelajaran', $guruEnabledMenus)))
                                <li class="nav-item"><a href="{{ route('mata_pelajaran.guru') }}" class="nav-link {{ request()->routeIs(['mata_pelajaran.guru','mata_pelajaran.*']) ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> Mata Pelajaran</a></li>
                            @endif
                            @if(($isGuru && (empty($guruEnabledMenus) || in_array('akademik_modul_ajar', $guruEnabledMenus))) || request()->routeIs('rencana_pembelajaran.edit'))
                                <li class="nav-item"><a href="{{ url('/modul-ajar') }}" class="nav-link {{ request()->is('modul-ajar*') && !request()->is('modul-ajar/editor*') ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> Modul Ajar</a></li>
                            @endif
                            @if($isGuru && (empty($guruEnabledMenus) || in_array('akademik_editor_modul', $guruEnabledMenus)))
                                <li class="nav-item"><a href="{{ route('akademik.editor_modul.index') }}" class="nav-link {{ request()->routeIs('akademik.editor_modul.*') ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> Edit Modul Ajar</a></li>
                            @endif
                            @if($user && $user->hasAnyRole(['Admin','Kepala Sekolah','Pengawas Pembina','Guru','Guru Mapel','Guru Kelas','Guru BK','Guru Piket','Wali Kelas','Siswa']) && (empty($guruEnabledMenus) || in_array('akademik_tool', $guruEnabledMenus)))
                                <li class="nav-item"><a href="{{ route('akademik.tool') }}" class="nav-link {{ request()->routeIs('akademik.tool') ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> Tool</a></li>
                            @endif
                            @if($user && $user->hasAnyRole(['Admin','Kepala Sekolah','Pengawas Pembina']))
                                <li class="nav-item"><a href="{{ route('akademik.supervisi') }}" class="nav-link {{ request()->routeIs('akademik.supervisi') || request()->routeIs('akademik.supervisi.*') ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> Supervisi</a></li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                <!-- Pembelajaran (Guru) -->
                @if($isGuru && (empty($guruEnabledMenus) || !empty(array_intersect(['pembelajaran_absensi','pembelajaran_agenda_kelas','pembelajaran_agenda_guru','pembelajaran_nilai','pembelajaran_rekap_nilai','pembelajaran_nilai_akhir','pembelajaran_materi','pembelajaran_pembina_ekskul'], $guruEnabledMenus))))
                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#subPembelajaran" aria-expanded="{{ request()->routeIs(['agenda_kelas.*','agenda_guru.*','absensi.*','nilai.*','rekap_nilai.*','nilai_akhir.*','materi_pembelajaran.*','ekskul.*']) ? 'true' : 'false' }}">
                        <i class="ti ti-book"></i> Pembelajaran
                        <i class="ti ti-chevron-right nav-arrow"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs(['agenda_kelas.*','agenda_guru.*','absensi.*','nilai.*','rekap_nilai.*','nilai_akhir.*','materi_pembelajaran.*','ekskul.*']) ? 'show' : '' }}" id="subPembelajaran">
                        <ul class="sidebar-subnav">
                            @if(empty($guruEnabledMenus) || in_array('pembelajaran_absensi', $guruEnabledMenus))
                            <li class="nav-item"><a href="{{ route('absensi.index', ['mode' => 'academic']) }}" class="nav-link {{ request()->routeIs('absensi.*') && $isModeAcademic ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> Absensi</a></li>
                            @endif
                            @if(empty($guruEnabledMenus) || in_array('pembelajaran_agenda_kelas', $guruEnabledMenus))
                            <li class="nav-item"><a href="{{ route('agenda_kelas.index') }}" class="nav-link {{ request()->routeIs('agenda_kelas.*') ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> Agenda Kelas</a></li>
                            @endif
                            @if(empty($guruEnabledMenus) || in_array('pembelajaran_agenda_guru', $guruEnabledMenus))
                            <li class="nav-item"><a href="{{ route('agenda_guru.index', ['mode' => 'academic']) }}" class="nav-link {{ request()->routeIs('agenda_guru.*') && $isModeAcademic ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> Agenda Guru</a></li>
                            @endif
                            @if(empty($guruEnabledMenus) || in_array('pembelajaran_nilai', $guruEnabledMenus))
                            <li class="nav-item"><a href="{{ route('nilai.index') }}" class="nav-link {{ request()->routeIs('nilai.*') ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> Nilai</a></li>
                            @endif
                            @if(empty($guruEnabledMenus) || in_array('pembelajaran_rekap_nilai', $guruEnabledMenus))
                            <li class="nav-item"><a href="{{ route('rekap_nilai.index') }}" class="nav-link {{ request()->routeIs('rekap_nilai.*') ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> Rekap Nilai</a></li>
                            @endif
                            @if(empty($guruEnabledMenus) || in_array('pembelajaran_nilai_akhir', $guruEnabledMenus))
                            <li class="nav-item"><a href="{{ route('nilai_akhir.index') }}" class="nav-link {{ request()->routeIs('nilai_akhir.*') ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> Penilaian Akhir</a></li>
                            @endif
                            @if(empty($guruEnabledMenus) || in_array('pembelajaran_materi', $guruEnabledMenus))
                            <li class="nav-item"><a href="{{ route('materi_pembelajaran.index') }}" class="nav-link {{ request()->routeIs('materi_pembelajaran.*') ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> Materi</a></li>
                            @endif
                            @if(empty($guruEnabledMenus) || in_array('pembelajaran_pembina_ekskul', $guruEnabledMenus))
                            <li class="nav-item"><a href="{{ route('ekskul.index') }}" class="nav-link {{ request()->routeIs('ekskul.*') ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> Pembina Ekskul</a></li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                <!-- Pembelajaran (Siswa) -->
                @if($user && $user->hasRole('Siswa') && (empty($siswaEnabledMenus) || in_array('pembelajaran_materi', $siswaEnabledMenus)))
                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#subPembelajaranSiswa" aria-expanded="{{ request()->routeIs('siswa.pembelajaran.*') ? 'true' : 'false' }}">
                        <i class="ti ti-book"></i> Pembelajaran
                        <i class="ti ti-chevron-right nav-arrow"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs('siswa.pembelajaran.*') ? 'show' : '' }}" id="subPembelajaranSiswa">
                        <ul class="sidebar-subnav">
                            @if(empty($siswaEnabledMenus) || in_array('pembelajaran_materi', $siswaEnabledMenus))
                            <li class="nav-item"><a href="{{ route('siswa.pembelajaran.materi') }}" class="nav-link {{ request()->routeIs('siswa.pembelajaran.materi') ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> Materi</a></li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                <!-- Piket KBM -->
                @if($isGuruPiket && (empty($guruEnabledMenus) || !empty(array_intersect(['piket_kbm_absensi_guru','piket_kbm_absensi_siswa','piket_kbm_pelanggaran'], $guruEnabledMenus))))
                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#subPiket" aria-expanded="{{ request()->routeIs(['agenda_guru.*','absensi.*','piket.pelanggaran.*']) ? 'true' : 'false' }}">
                        <i class="ti ti-shield-checkered"></i> Piket KBM
                        <i class="ti ti-chevron-right nav-arrow"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs(['agenda_guru.*','absensi.*','piket.pelanggaran.*']) ? 'show' : '' }}" id="subPiket">
                        <ul class="sidebar-subnav">
                            @if(empty($guruEnabledMenus) || in_array('piket_kbm_absensi_guru', $guruEnabledMenus))
                            <li class="nav-item"><a href="{{ route('agenda_guru.index') }}" class="nav-link {{ request()->routeIs('agenda_guru.*') && $isModePiket ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> Absensi Guru</a></li>
                            @endif
                            @if(empty($guruEnabledMenus) || in_array('piket_kbm_absensi_siswa', $guruEnabledMenus))
                            <li class="nav-item"><a href="{{ route('absensi.index') }}" class="nav-link {{ request()->routeIs('absensi.*') && $isModePiket ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> Absensi Siswa</a></li>
                            @endif
                            @if(empty($guruEnabledMenus) || in_array('piket_kbm_pelanggaran', $guruEnabledMenus))
                            <li class="nav-item"><a href="{{ route('piket.pelanggaran.index') }}" class="nav-link {{ request()->routeIs('piket.pelanggaran.*') ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> Pelanggaran</a></li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                <!-- Wali Kelas -->
                @if($isWali && (empty($guruEnabledMenus) || !empty(array_intersect(['wali_kelas_dashboard','wali_kelas_data_siswa','wali_kelas_absensi_kelas','wali_kelas_laporan_guru','wali_kelas_nilai_siswa','wali_kelas_rekap_nilai'], $guruEnabledMenus))))
                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#subWali" aria-expanded="{{ request()->routeIs('wali_kelas.*') ? 'true' : 'false' }}">
                        <i class="ti ti-users-group"></i> Wali Kelas
                        <i class="ti ti-chevron-right nav-arrow"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs('wali_kelas.*') ? 'show' : '' }}" id="subWali">
                        <ul class="sidebar-subnav">
                            @if(empty($guruEnabledMenus) || in_array('wali_kelas_dashboard', $guruEnabledMenus))
                            <li class="nav-item"><a href="{{ route('wali_kelas.index') }}" class="nav-link {{ request()->routeIs('wali_kelas.index') ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> Dashboard</a></li>
                            @endif
                            @if(empty($guruEnabledMenus) || in_array('wali_kelas_data_siswa', $guruEnabledMenus))
                            <li class="nav-item"><a href="{{ route('wali_kelas.siswa') }}" class="nav-link {{ request()->routeIs('wali_kelas.siswa') ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> Data Siswa</a></li>
                            @endif
                            @if(empty($guruEnabledMenus) || in_array('wali_kelas_absensi_kelas', $guruEnabledMenus))
                            <li class="nav-item"><a href="{{ route('wali_kelas.absensi') }}" class="nav-link {{ request()->routeIs('wali_kelas.absensi') ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> Absensi Kelas</a></li>
                            @endif
                            @if(empty($guruEnabledMenus) || in_array('wali_kelas_laporan_guru', $guruEnabledMenus))
                            <li class="nav-item"><a href="{{ route('wali_kelas.laporan_guru') }}" class="nav-link {{ request()->routeIs('wali_kelas.laporan_guru') ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> Laporan Guru</a></li>
                            @endif
                            @if(empty($guruEnabledMenus) || in_array('wali_kelas_nilai_siswa', $guruEnabledMenus))
                            <li class="nav-item"><a href="{{ route('wali_kelas.nilai') }}" class="nav-link {{ request()->routeIs('wali_kelas.nilai') ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> Nilai Siswa</a></li>
                            @endif
                            @if(($kelasBindaan) && (empty($guruEnabledMenus) || in_array('wali_kelas_rekap_nilai', $guruEnabledMenus)))
                            <li class="nav-item"><a href="{{ route('rekap_nilai.index', ['wali_kelas' => 1, 'kelas_id' => $kelasBindaan->id]) }}" class="nav-link {{ request()->routeIs('rekap_nilai.*') && request()->boolean('wali_kelas') ? 'active' : '' }}"><i class="ti ti-circle-filled"></i> Rekap Nilai</a></li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                <!-- Guru BK -->
                @if($user && $user->hasRole('Guru BK') && (empty($guruEnabledMenus) || in_array('guru_bk', $guruEnabledMenus)))
                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#subBk" aria-expanded="{{ request()->routeIs('guru_bk_layanan.*') ? 'true' : 'false' }}">
                        <i class="ti ti-user-plus"></i> Guru BK
                        <i class="ti ti-chevron-right nav-arrow"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs('guru_bk_layanan.*') ? 'show' : '' }}" id="subBk">
                        <ul class="sidebar-subnav">
                            @forelse($kelasBinaanBk ?? [] as $kelasBk)
                            <li class="nav-item">
                                @php
                                    $currentKelasRoute = request()->route('kelas');
                                    $currentKelasId = is_object($currentKelasRoute) ? ($currentKelasRoute->id ?? null) : $currentKelasRoute;
                                @endphp
                                <a href="{{ route('guru_bk_layanan.menu', ['kelas' => $kelasBk->id]) }}" class="nav-link {{ request()->routeIs('guru_bk_layanan.*') && (int) $currentKelasId === (int) $kelasBk->id ? 'active' : '' }}">
                                    <i class="ti ti-circle-filled"></i> {{ $kelasBk->nama_kelas }}
                                </a>
                            </li>
                            @empty
                            <li class="nav-item"><span class="nav-link text-muted" style="font-size:0.75rem;opacity:0.6;">Belum ada kelas binaan</span></li>
                            @endforelse
                        </ul>
                    </div>
                </li>
                @endif

                <!-- Data Master -->
                @if($user && $user->hasAnyRole(['Admin','Kepala Sekolah','Wakil Kepala Sekolah']))
                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#subMaster" aria-expanded="{{ request()->routeIs(['sekolah.*','kepala_sekolah.*','wakil_kepala_sekolah.*','guru_bk.*','guru.*','pembina.*','guru_piket.*','users.*','siswa.*','kelas.*','mata_pelajaran.*','tugas_guru.*','kegiatan.*','jenis_pelanggaran.*','ekskul.*','asc_timetable.*']) ? 'true' : 'false' }}">
                        <i class="ti ti-database"></i> Data Master
                        <i class="ti ti-chevron-right nav-arrow"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs(['sekolah.*','kepala_sekolah.*','wakil_kepala_sekolah.*','guru_bk.*','guru.*','pembina.*','guru_piket.*','users.*','siswa.*','kelas.*','mata_pelajaran.*','tugas_guru.*','kegiatan.*','jenis_pelanggaran.*','ekskul.*','asc_timetable.*']) ? 'show' : '' }}" id="subMaster">
                        <ul class="sidebar-subnav">
                            <li class="nav-item">
                                <a href="{{ route('sekolah.index') }}" class="nav-link {{ request()->routeIs('sekolah.*') ? 'active' : '' }}">
                                    <i class="ti ti-circle-filled"></i> Data Sekolah
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('kepala_sekolah.index') }}" class="nav-link {{ request()->routeIs('kepala_sekolah.*') ? 'active' : '' }}">
                                    <i class="ti ti-circle-filled"></i> Kepala Sekolah
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('wakil_kepala_sekolah.index') }}" class="nav-link {{ request()->routeIs('wakil_kepala_sekolah.*') ? 'active' : '' }}">
                                    <i class="ti ti-circle-filled"></i> Wakil Kepala
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('guru_bk.index') }}" class="nav-link {{ request()->routeIs('guru_bk.*') ? 'active' : '' }}">
                                    <i class="ti ti-circle-filled"></i> Guru BK
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('guru.index') }}" class="nav-link {{ request()->routeIs('guru.*') ? 'active' : '' }}">
                                    <i class="ti ti-circle-filled"></i> Guru
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('pembina.index') }}" class="nav-link {{ request()->routeIs('pembina.*') ? 'active' : '' }}">
                                    <i class="ti ti-circle-filled"></i> Pembina
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('guru_piket.index') }}" class="nav-link {{ request()->routeIs('guru_piket.*') ? 'active' : '' }}">
                                    <i class="ti ti-circle-filled"></i> Guru Piket
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.index') && !request()->has('role') ? 'active' : '' }}">
                                    <i class="ti ti-circle-filled"></i> Akun Pengguna
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('users.admin') }}" class="nav-link {{ request()->routeIs('users.admin') || (request()->routeIs('users.index') && request()->input('role') === 'Admin') ? 'active' : '' }}">
                                    <i class="ti ti-circle-filled"></i> Admin
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('siswa.index') }}" class="nav-link {{ request()->routeIs('siswa.*') ? 'active' : '' }}">
                                    <i class="ti ti-circle-filled"></i> Siswa
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('kelas.index') }}" class="nav-link {{ request()->routeIs('kelas.*') ? 'active' : '' }}">
                                    <i class="ti ti-circle-filled"></i> Kelas
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('mata_pelajaran.index') }}" class="nav-link {{ request()->routeIs('mata_pelajaran.*') && !request()->routeIs('tugas_guru.*') ? 'active' : '' }}">
                                    <i class="ti ti-circle-filled"></i> Mata Pelajaran
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('tugas_guru.index') }}" class="nav-link {{ request()->routeIs('tugas_guru.*') ? 'active' : '' }}">
                                    <i class="ti ti-circle-filled"></i> Tugas Guru
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('kegiatan.index') }}" class="nav-link {{ request()->routeIs('kegiatan.*') ? 'active' : '' }}">
                                    <i class="ti ti-circle-filled"></i> Kegiatan
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('jenis_kegiatan.index') }}" class="nav-link {{ request()->routeIs('jenis_kegiatan.*') ? 'active' : '' }}">
                                    <i class="ti ti-circle-filled"></i> Jenis Kegiatan
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('jenis_pelanggaran.index') }}" class="nav-link {{ request()->routeIs('jenis_pelanggaran.*') ? 'active' : '' }}">
                                    <i class="ti ti-circle-filled"></i> Jenis Pelanggaran
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('ekskul.index') }}" class="nav-link {{ request()->routeIs('ekskul.*') ? 'active' : '' }}">
                                    <i class="ti ti-circle-filled"></i> Ekstrakurikuler
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('asc_timetable.index') }}" class="nav-link {{ request()->routeIs('asc_timetable.*') ? 'active' : '' }}">
                                    <i class="ti ti-circle-filled"></i> ASC Time Table
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endif

                <!-- Pengaturan -->
                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#subSetting" aria-expanded="{{ request()->routeIs(['profile.edit','profile.panduan','tahun_ajaran.index','setting.*']) ? 'true' : 'false' }}">
                        <i class="ti ti-settings"></i> Pengaturan
                        <i class="ti ti-chevron-right nav-arrow"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs(['profile.edit','profile.panduan','tahun_ajaran.index','setting.*']) ? 'show' : '' }}" id="subSetting">
                        <ul class="sidebar-subnav">
                            <li class="nav-item">
                                <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                                    <i class="ti ti-circle-filled"></i> Profile
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('profile.panduan') }}" class="nav-link {{ request()->routeIs('profile.panduan') ? 'active' : '' }}">
                                    <i class="ti ti-circle-filled"></i> Panduan
                                </a>
                            </li>
                            @if($user && $user->hasAnyRole(['Admin','Kepala Sekolah']))
                            <li class="nav-item">
                                <a href="{{ route('tahun_ajaran.index') }}" class="nav-link {{ request()->routeIs('tahun_ajaran.index') ? 'active' : '' }}">
                                    <i class="ti ti-circle-filled"></i> Dashboard Pengaturan
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('setting.tahun_ajaran') }}" class="nav-link {{ request()->routeIs('setting.tahun_ajaran*') ? 'active' : '' }}">
                                    <i class="ti ti-circle-filled"></i> Tahun Ajaran
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('setting.semester') }}" class="nav-link {{ request()->routeIs('setting.semester*') ? 'active' : '' }}">
                                    <i class="ti ti-circle-filled"></i> Semester
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('setting.header') }}" class="nav-link {{ request()->routeIs('setting.header*') ? 'active' : '' }}">
                                    <i class="ti ti-circle-filled"></i> Edit Header
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('setting.absensi') }}" class="nav-link {{ request()->routeIs(['setting.absensi*','setting.menu*']) ? 'active' : '' }}">
                                    <i class="ti ti-circle-filled"></i> Pengaturan
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('setting.backup') }}" class="nav-link {{ request()->routeIs('setting.backup') ? 'active' : '' }}">
                                    <i class="ti ti-circle-filled"></i> Backup Database
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('setting.about') }}" class="nav-link {{ request()->routeIs('setting.about') ? 'active' : '' }}">
                                    <i class="ti ti-circle-filled"></i> About
                                </a>
                            </li>
                            @if($user && $user->hasAnyRole(['Admin','Kepala Sekolah']))
                            <li class="nav-item">
                                <a href="{{ route('help.admin.index') }}" class="nav-link {{ request()->routeIs('help.admin.*') ? 'active' : '' }}">
                                    <i class="ti ti-circle-filled"></i> Help
                                </a>
                            </li>
                            @endif
                            @endif
                        </ul>
                    </div>
                </li>
            </ul>
            @endif
        </nav>

        <!-- Footer -->
        <div class="sidebar-footer">
            &copy; {{ date('Y') }} SIMADIS — SMAN 1 Pontang
        </div>
    </aside>

    <!-- ========== MAIN CONTENT ========== -->
    <div class="main-content" id="mainContent">
        <!-- Topbar -->
        <header class="topbar">
            <button class="topbar-toggle" id="sidebarToggle" type="button" aria-label="Toggle sidebar" onclick="toggleSidebar()">
                <i class="ti ti-menu-2"></i>
            </button>
            <a href="{{ route('home') }}" class="topbar-brand">
                <span class="topbar-brand-icon">
                    <img src="{{ asset('images/icon_simadisnew.png') }}" alt="S" style="width:1.5rem;height:1.5rem;object-fit:contain;">
                </span>
                <span>
                    <span class="topbar-brand-text">SIMADIS</span>
                    <span class="topbar-brand-sub">Absensi Digital</span>
                </span>
            </a>

            <!-- Desktop nav links -->
            <div class="topbar-spacer"></div>
            <ul class="topbar-nav">
                <li><a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}"><i class="ti ti-layout-dashboard me-1"></i> Dashboard</a></li>
                <li><a href="{{ route('absensi.index') }}" class="nav-link {{ request()->routeIs('absensi.*') ? 'active' : '' }}"><i class="ti ti-clipboard-list me-1"></i> Absensi</a></li>
                @if($user && $user->hasAnyRole(['Admin','Kepala Sekolah','Wakil Kepala Sekolah']))
                <li><a href="{{ route('siswa.index') }}" class="nav-link {{ request()->routeIs('siswa.*') ? 'active' : '' }}"><i class="ti ti-users me-1"></i> Siswa</a></li>
                @endif
            </ul>

            <div class="topbar-actions">
                <!-- User dropdown -->
                <div class="dropdown">
                    <a href="#" id="userDropdown" class="topbar-user-avatar dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="User menu">
                        {{ mb_substr($user->name ?? '?', 0, 1) }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <div class="px-3 py-2">
                                <div class="fw-semibold">{{ $user->name ?? 'User' }}</div>
                                <div class="text-muted small">{{ $user?->role?->role_name ?? 'User' }}</div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider m-0"></li>
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="ti ti-user me-2"></i> Profil</a></li>
                        <li><a class="dropdown-item" href="{{ route('profile.panduan') }}"><i class="ti ti-book me-2"></i> Panduan</a></li>
                        <li><a class="dropdown-item text-danger" href="#" onclick="event.preventDefault();document.getElementById('logout-form').submit();"><i class="ti ti-logout me-2"></i> Keluar</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="page-content">
            <!-- Alerts -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ti ti-check me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ti ti-alert-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ti ti-alert-triangle me-2"></i> Terdapat kesalahan pada formulir.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <!-- Page Header (optional) -->
            @yield('page-header')

            <!-- Main Yield -->
            @yield('content')
        </main>
    </div>
</div>

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
@stack('js')

<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
    @if(session('success'))
    <div class="toast show" role="alert">
        <div class="toast-header bg-success text-white">
            <i class="ti ti-check me-2"></i>
            <strong class="me-auto">Berhasil</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">{{ session('success') }}</div>
    </div>
    @endif
    @if(session('error'))
    <div class="toast show" role="alert">
        <div class="toast-header bg-danger text-white">
            <i class="ti ti-alert-circle me-2"></i>
            <strong class="me-auto">Gagal</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">{{ session('error') }}</div>
    </div>
    @endif
</div>

<script>
    // Sidebar toggle functionality
    const SIDEBAR_STATE_KEY = 'simadis_sidebar_collapsed';

    function setSidebarState(collapsed) {
        try {
            window.localStorage.setItem(SIDEBAR_STATE_KEY, collapsed ? '1' : '0');
        } catch (e) {
            console.warn('Cannot persist sidebar state', e);
        }
    }

    function getSidebarState() {
        try {
            return window.localStorage.getItem(SIDEBAR_STATE_KEY) === '1';
        } catch (e) {
            return false;
        }
    }

    function applySidebarState() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        if (!sidebar || !mainContent) return;

        const collapsed = getSidebarState();
        if (window.innerWidth >= 992) {
            sidebar.classList.toggle('collapsed', collapsed);
            mainContent.classList.toggle('sidebar-hidden', collapsed);
        } else {
            sidebar.classList.remove('collapsed');
            mainContent.classList.remove('sidebar-hidden');
        }
    }

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const overlay = document.getElementById('sidebarOverlay');

        if (window.innerWidth < 992) {
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('show');
        } else {
            const isCollapsed = !sidebar.classList.contains('collapsed');
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('sidebar-hidden');
            setSidebarState(isCollapsed);
        }
    }

    function closeSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('show');
    }

    // Close sidebar on window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            document.getElementById('sidebarOverlay').classList.remove('show');
            document.getElementById('sidebar').classList.remove('mobile-open');
            applySidebarState();
        }
    });

    // Initialize Toast and Topbar interactions
    document.addEventListener('DOMContentLoaded', function() {
        applySidebarState();

        document.querySelectorAll('.sidebar-admin-trigger').forEach(function(trigger) {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                var targetSelector = this.getAttribute('data-target');
                var target = targetSelector ? document.querySelector(targetSelector) : null;
                var willOpen = this.getAttribute('aria-expanded') !== 'true';

                document.querySelectorAll('.sidebar-admin-trigger').forEach(function(otherTrigger) {
                    otherTrigger.setAttribute('aria-expanded', 'false');
                    var otherTarget = otherTrigger.getAttribute('data-target') ? document.querySelector(otherTrigger.getAttribute('data-target')) : null;
                    if (otherTarget) {
                        otherTarget.classList.remove('is-open');
                    }
                });

                this.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                if (target) {
                    target.classList.toggle('is-open', willOpen);
                }
            });
        });

        document.querySelectorAll('.sidebar-admin-link, .sidebar-admin-sublink').forEach(function(item) {
            item.addEventListener('click', function() {
                document.querySelectorAll('.sidebar-admin-link, .sidebar-admin-sublink').forEach(function(otherItem) {
                    otherItem.classList.remove('is-active');
                });
                this.classList.add('is-active');
            });
        });

        // Manual collapse toggle for sidebar nav to fix freeze issue
        document.querySelectorAll('.sidebar-nav .nav-link[data-bs-toggle="collapse"]').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                var targetId = this.getAttribute('data-bs-target');
                if (!targetId) return;
                var target = document.querySelector(targetId);
                if (!target) return;

                // Toggle using Bootstrap Collapse API if available
                if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
                    var collapse = bootstrap.Collapse.getOrCreateInstance(target, { toggle: false });
                    if (target.classList.contains('show')) {
                        collapse.hide();
                    } else {
                        // Hide other open collapses in same parent to keep only one open
                        var parent = this.closest('.sidebar-nav');
                        if (parent) {
                            parent.querySelectorAll('.collapse.show').forEach(function(other) {
                                if (other.id !== targetId.replace('#', '')) {
                                    bootstrap.Collapse.getOrCreateInstance(other, { toggle: false }).hide();
                                }
                            });
                        }
                        collapse.show();
                    }
                } else {
                    // Fallback: manual toggle
                    target.classList.toggle('show');
                    this.setAttribute('aria-expanded', target.classList.contains('show'));
                }
            });
        });
        var toastElList = [].slice.call(document.querySelectorAll('.toast'));
        toastElList.forEach(function(toastEl) {
            var toast = new bootstrap.Toast(toastEl, { delay: 4000, autohide: true });
            toast.show();
        });
        document.querySelectorAll('.toast .btn-close').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var toast = bootstrap.Toast.getInstance(this.closest('.toast'));
                if (toast) toast.hide();
            });
        });

        var profileToggle = document.getElementById('userDropdown');
        if (profileToggle) {
            var profileMenu = profileToggle.nextElementSibling;
            // Debug logs to help diagnose dropdown issues
            console.log('Topbar: profileToggle', !!profileToggle, 'profileMenu', !!profileMenu);
            console.log('Topbar: bootstrap defined?', typeof bootstrap !== 'undefined');

            // If Bootstrap Dropdown is available, use its instance for proper show/hide handling
            if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
                var dropdownInstance = bootstrap.Dropdown.getOrCreateInstance(profileToggle);

                profileToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Topbar: avatar clicked — toggling Bootstrap dropdown');
                    try {
                        dropdownInstance.toggle();
                    } catch (err) {
                        console.error('Topbar: dropdownInstance.toggle error', err);
                    }
                    console.log('Topbar: menu classes after toggle', profileMenu ? profileMenu.className : null);
                });

                // Click outside should hide the dropdown via the Bootstrap instance
                document.addEventListener('click', function(event) {
                    if (!profileToggle.contains(event.target) && profileMenu && !profileMenu.contains(event.target)) {
                        try {
                            dropdownInstance.hide();
                            console.log('Topbar: clicked outside — hid dropdown via Bootstrap');
                        } catch (err) {
                            profileMenu.classList.remove('show');
                            profileToggle.setAttribute('aria-expanded', 'false');
                            console.warn('Topbar: fallback hide applied', err);
                        }
                    }
                });
            } else {
                // Fallback: simple class toggle when Bootstrap is not present
                console.warn('Topbar: Bootstrap Dropdown not available — using fallback toggle');
                profileToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Topbar: avatar clicked — using fallback toggle');
                    if (profileMenu && profileMenu.classList.contains('dropdown-menu')) {
                        profileMenu.classList.toggle('show');
                        profileToggle.setAttribute('aria-expanded', profileMenu.classList.contains('show') ? 'true' : 'false');
                        console.log('Topbar: menu classes after fallback toggle', profileMenu.className);
                    }
                });

                document.addEventListener('click', function(event) {
                    if (!profileToggle.contains(event.target) && profileMenu && !profileMenu.contains(event.target)) {
                        profileMenu.classList.remove('show');
                        profileToggle.setAttribute('aria-expanded', 'false');
                        console.log('Topbar: clicked outside — fallback hide applied');
                    }
                });
            }
        }
    });
</script>
</body>
</html>