<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="theme-color" content="#0D6EFD">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Trijaya">

    <!-- Icons -->
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/icons/icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('images/icons/icon-96x96.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/icons/icon-192x192.png') }}">

    <!-- Manifest Link -->
    <link rel="manifest" href="/manifest.json">


    <title>Toko Trijaya - @yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ auth()->id() }}">
    <meta name="vapid-public-key" content="{{ config('services.webpush.vapid_public_key') }}">
    <meta name="app-base-path" content="{{ request()->getBasePath() }}">
    <script src="{{ secure_asset('js/idb-keyval.min.js') }}"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-bg: #1a1a2e;
            --primary-color: #fffb00b7;
            --hover-color: rgba(255, 255, 255, 0.1);
            --transition-speed: 0.3s;
            --header-height: 70px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fc;
            overflow-x: hidden;
        }

        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            transition: all var(--transition-speed) ease;
            position: fixed;
            z-index: 1040;
            background: var(--sidebar-bg);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }

        .sidebar-collapsed {
            transform: translateX(calc(var(--sidebar-width) * -1));
        }

        .sidebar-header {
            padding: 15px;
            background: rgba(0, 0, 0, 0.1);
            text-align: center;
            flex-shrink: 0;
            height: var(--header-height);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .sidebar-content {
            flex: 1;
            overflow-y: auto;
            padding-bottom: 20px;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            transition: all var(--transition-speed) ease;
            width: calc(100% - var(--sidebar-width));
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-content-expanded {
            margin-left: 0;
            width: 100%;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(calc(var(--sidebar-width) * -1));
                z-index: 1040 !important;
            }

            .sidebar-collapsed {
                transform: translateX(calc(var(--sidebar-width) * -1));
            }

            .main-content {
                margin-left: 0;
                width: 100%;
                position: relative;
                z-index: 1;
            }

            .sidebar-show {
                transform: translateX(0);
                box-shadow: 5px 0 15px rgba(0, 0, 0, 0.2);
                z-index: 1040 !important;
            }

            .navbar {
                position: sticky !important;
                top: 0 !important;
                z-index: 1030 !important;
                background: white !important;
            }

            .navbar-brand {
                font-size: 1rem !important;
            }

            .content-container {
                padding: 15px 10px !important;
            }

            /* Card Improvements */
            .card {
                margin-bottom: 1rem;
            }

            .card-body {
                padding: 1rem;
            }

            .card-header {
                padding: 0.75rem 1rem;
            }

            /* Table Responsive */
            .table-responsive {
                margin: 0 -1rem;
                padding: 0 1rem;
            }

            .table {
                font-size: 0.85rem;
            }

            .table th,
            .table td {
                padding: 0.5rem 0.3rem;
                white-space: nowrap;
            }

            /* Button Improvements */
            .btn-sm {
                padding: 0.4rem 0.75rem;
                font-size: 0.8rem;
            }

            .btn-group {
                flex-wrap: wrap;
                gap: 0.25rem;
            }

            /* Badge & User Badge */
            .user-badge {
                padding: 6px 10px !important;
                font-size: 0.8rem;
            }

            /* Form Controls */
            .form-control,
            .form-select {
                font-size: 0.9rem;
            }

            /* Alert */
            .alert {
                font-size: 0.9rem;
                padding: 0.75rem;
            }

            /* Stat Cards on Dashboard */
            .h5 {
                font-size: 1rem;
            }

            .text-xs {
                font-size: 0.65rem;
            }

            /* Row Spacing */
            .row {
                margin-left: -0.5rem;
                margin-right: -0.5rem;
            }

            .row > * {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }

            /* Dropdown Menu */
            .dropdown-menu {
                font-size: 0.9rem;
            }

            /* Back to Top Button */
            .back-to-top {
                width: 45px;
                height: 45px;
                line-height: 45px;
                font-size: 18px;
                bottom: 15px;
                right: 15px;
            }

            /* Chart Container */
            canvas {
                max-height: 200px !important;
            }
        }

        .toggle-btn {
            cursor: pointer;
            font-size: 1.5rem;
            padding: 10px;
            background: rgba(0,0,0,0.1);
            border-radius: 5px;
            transition: all 0.2s ease;
        }

        .toggle-btn:hover {
            background: rgba(0,0,0,0.2);
            transform: scale(1.05);
        }

        .nav-link {
            padding: 12px 15px;
            border-radius: 5px;
            margin: 3px 0;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.8);
        }

        .nav-link:hover {
            background: var(--hover-color);
            color: white;
            transform: translateX(5px);
        }

        .nav-link.active {
            background: var(--primary-color);
            color: white;
            font-weight: 500;
        }

        .sidebar-brand {
            color: white;
            font-weight: 600;
            font-size: 1.2rem;
            letter-spacing: 1px;
        }

        .navbar {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            background: white !important;
        }

        .content-container {
            flex: 1;
            padding: 20px;
            background-color: #f8f9fc;
        }

        .user-badge {
            transition: all 0.2s ease;
            padding: 8px 12px;
            border-radius: 20px;
        }

        .user-badge:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* Animation for alerts */
        .alert {
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Smooth scroll behavior */
        html {
            scroll-behavior: smooth;
        }

        /* Sidebar backdrop for mobile */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1035;
            transition: opacity 0.3s ease;
        }

        @media (max-width: 768px) {
            .sidebar-backdrop.show {
                display: block;
            }
        }

        /* Back to top button */
        .back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            text-align: center;
            line-height: 50px;
            font-size: 20px;
            cursor: pointer;
            z-index: 999;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .back-to-top:hover {
            background: #ffae00ff;
            transform: translateY(-3px);
        }

        /* PWA Banner Styles */
        .pwa-banner {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            z-index: 1050;
            max-width: 90%;
            width: 500px;
            animation: slideUp 0.3s ease-out;
        }

        @keyframes slideUp {
            from {
                transform: translateX(-50%) translateY(100px);
                opacity: 0;
            }
            to {
                transform: translateX(-50%) translateY(0);
                opacity: 1;
            }
        }

        .pwa-banner-content {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .pwa-banner-icon {
            font-size: 24px;
            flex-shrink: 0;
        }

        .pwa-banner-text {
            flex: 1;
        }

        .pwa-banner-text strong {
            display: block;
            font-size: 16px;
            margin-bottom: 4px;
        }

        .pwa-banner-text small {
            display: block;
            font-size: 12px;
            opacity: 0.9;
        }

        .pwa-banner-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .pwa-update-banner {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        /* PWA Offline Indicator */
        .pwa-offline-indicator {
            position: fixed;
            top: 70px;
            left: 50%;
            transform: translateX(-50%);
            background: #dc3545;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            z-index: 1040;
            display: flex;
            align-items: center;
            font-size: 14px;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                transform: translateX(-50%) translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateX(-50%) translateY(0);
                opacity: 1;
            }
        }

        /* PWA Sync Indicator */
        .pwa-sync-indicator {
            position: fixed;
            top: 110px;
            left: 50%;
            transform: translateX(-50%);
            background: #ffc107;
            color: #000;
            padding: 8px 16px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            z-index: 1040;
            display: flex;
            align-items: center;
            font-size: 13px;
            animation: slideDown 0.3s ease-out;
        }

        .pwa-sync-indicator i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .pwa-banner {
                width: calc(100% - 40px);
                max-width: none;
                bottom: 10px;
                padding: 12px 15px;
            }

            .pwa-banner-content {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }

            .pwa-banner-actions {
                width: 100%;
                justify-content: center;
            }

            .pwa-banner-text strong {
                font-size: 14px;
            }

            .pwa-banner-text small {
                font-size: 11px;
            }
        }

        /* Small Mobile Devices */
        @media (max-width: 480px) {
            .navbar-brand {
                font-size: 0.9rem !important;
            }

            .content-container {
                padding: 10px 8px !important;
            }

            .card-body {
                padding: 0.75rem;
            }

            .table {
                font-size: 0.75rem;
            }

            .btn {
                font-size: 0.8rem;
                padding: 0.375rem 0.65rem;
            }

            .btn-sm {
                padding: 0.3rem 0.6rem;
                font-size: 0.75rem;
            }

            h5, .h5 {
                font-size: 0.95rem;
            }

            h6, .h6 {
                font-size: 0.85rem;
            }
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Sidebar scrollbar */
        .sidebar-content::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar-content::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
        }

        .sidebar-content::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Pulse animation for active menu */
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(206, 223, 78, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(78, 115, 223, 0); }
            100% { box-shadow: 0 0 0 0 rgba(78, 115, 223, 0); }
        }

        .nav-link.active {
            animation: pulse 1.5s infinite;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar Backdrop (Mobile Only) -->
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <!-- Sidebar -->
        <div class="sidebar text-white" id="sidebar">
            <div class="sidebar-header">
                <h4 class="sidebar-brand mb-0 animate__animated animate__fadeIn">
                    <a href="{{ route('dashboard') }}" class="text-white text-decoration-none">
                        <i class="bi-bag-fill"></i> <span>Toko Trijaya</span>
                    </a>
                </h4>

                <div class="toggle-btn d-md-none text-white mt-2 animate__animated animate__fadeIn" id="sidebarCollapseMobile">
                    <i class="bi bi-x-lg"></i>
                </div>
            </div>
            @auth
            <div class="sidebar-content">
                <hr class="bg-light mx-3">
                <ul class="nav flex-column px-3">
                     @if(auth()->user()->role == 'admin')

                        <li class="nav-item animate__animated animate__fadeInLeft" style="animation-delay: 0.2s;">
                            <a class="nav-link {{ request()->is('transaction*') ? 'active' : '' }}" href="{{ route('transactions.index') }}">
                                <i class="bi bi-cash-coin me-2"></i> Kelola Transaksi
                            </a>
                        </li>
                         <li class="nav-item animate__animated animate__fadeInLeft" style="animation-delay: 0.3s;">
                            <a class="nav-link {{ request()->is('reports/daily') ? 'active' : '' }}" href="{{ route('reports.daily') }}">
                                <i class="bi bi-file-earmark-text me-2"></i> Laporan
                            </a>
                        </li>
                        <li class="nav-item animate__animated animate__fadeInLeft" style="animation-delay: 0.5s;">
                            <a class="nav-link {{ request()->is('charts*') ? 'active' : '' }}" href="{{ route('charts.index') }}">
                                <i class="bi bi-bar-chart-line me-2"></i> Grafik Penjualan
                            </a>
                        </li>
                        <li class="nav-item animate__animated animate__fadeInLeft" style="animation-delay: 0.5s;">
                            <a class="nav-link {{ request()->is('inventories*') ? 'active' : '' }}" href="{{ route('inventories.index') }}">
                                <i class="bi bi-box-seam me-2"></i> Inventory Produk
                                @if(isset($inventoryAlertCount) && $inventoryAlertCount > 0)
                                    <span class="badge bg-danger ms-2">{{ $inventoryAlertCount }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item animate__animated animate__fadeInLeft" style="animation-delay: 0.55s;">
                            <a class="nav-link {{ request()->is('whatsapp/orders*') ? 'active' : '' }}" href="{{ route('whatsapp.orders.index') }}">
                                <i class="bi bi-whatsapp me-2"></i> Whatsapp
                            </a>
                        </li>

                    @else

                        <li class="nav-item animate__animated animate__fadeInLeft" style="animation-delay: 0.2s;">
                            <a class="nav-link {{ request()->is('transactions*') ? 'active' : '' }}" href="{{ route('transactions.index') }}">
                                <i class="bi bi-cash-coin me-2"></i> Kelola Transaksi
                            </a>
                        </li>
                        <li class="nav-item animate__animated animate__fadeInLeft" style="animation-delay: 0.5s;">
                            <a class="nav-link {{ request()->is('purchases*') ? 'active' : '' }}" href="{{ route('purchases.index') }}">
                                <i class="bi bi-cart-check me-2"></i> Pembelian Produk
                            </a>
                        </li>
                        <!-- <li class="nav-item animate__animated animate__fadeInLeft" style="animation-delay: 0.3s;">
                            <a class="nav-link {{ request()->is('reports/x') ? 'active' : '' }}" href="">
                                <i class="bi bi-file-earmark-text me-2"></i> Laporan Harian (X)
                            </a>
                        </li>
                        <li class="nav-item animate__animated animate__fadeInLeft" style="animation-delay: 0.4s;">
                            <a class="nav-link {{ request()->is('reports/z') ? 'active' : '' }}" href="">
                                <i class="bi bi-file-earmark-lock me-2"></i> Laporan Harian (Z)
                            </a>
                        </li> -->
                        <li class="nav-item animate__animated animate__fadeInLeft" style="animation-delay: 0.3s;">
                            <a class="nav-link {{ request()->is('reports/daily') ? 'active' : '' }}" href="{{ route('reports.daily') }}">
                                <i class="bi bi-file-earmark-text me-2"></i> Laporan Harian
                            </a>
                        </li>
                        <li class="nav-item animate__animated animate__fadeInLeft" style="animation-delay: 0.5s;">
                            <a class="nav-link {{ request()->is('charts*') ? 'active' : '' }}" href="{{ route('charts.index') }}">
                                <i class="bi bi-bar-chart-line me-2"></i> Grafik Penjualan
                            </a>
                        </li>

                        <li class="nav-item animate__animated animate__fadeInLeft" style="animation-delay: 0.5s;">
                            <a class="nav-link {{ request()->is('inventories*') ? 'active' : '' }}" href="{{ route('inventories.index') }}">
                                <i class="bi bi-box-seam me-2"></i> Inventory Produk
                                @if(isset($inventoryAlertCount) && $inventoryAlertCount > 0)
                                    <span class="badge bg-danger ms-2">{{ $inventoryAlertCount }}</span>
                                @endif
                            </a>
                        </li>

                        <li class="nav-item animate__animated animate__fadeInLeft" style="animation-delay: 0.6s;">
                            <a class="nav-link {{ request()->is('products*') ? 'active' : '' }}" href="{{ route ('products.index') }}">
                                <i class="bi-bag-fill me-2"></i> Manajemen Produk
                            </a>
                        </li>
                        <li class="nav-item animate__animated animate__fadeInLeft" style="animation-delay: 0.7s;">
                            <a class="nav-link {{ request()->is('users*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                <i class="bi bi-people me-2"></i> Manajemen User
                            </a>
                        </li>
                        <li class="nav-item animate__animated animate__fadeInLeft" style="animation-delay: 0.7s;">
                            <a class="nav-link {{ request()->is('cashflow*') ? 'active' : '' }}" href="{{ route('cashflow.index') }}">
                                <i class="bi bi-wallet2 me-2"></i> Buku Kas (Cash Flow)
                            </a>
                        </li>
                        <li class="nav-item animate__animated animate__fadeInLeft" style="animation-delay: 0.55s;">
                            <a class="nav-link {{ request()->is('whatsapp/orders*') ? 'active' : '' }}" href="{{ route('whatsapp.orders.index') }}">
                                <i class="bi bi-whatsapp me-2"></i> Whatsapp
                            </a>
                        </li>
                        <li class="nav-item animate__animated animate__fadeInLeft" style="animation-delay: 0.8s;">
                            <a class="nav-link {{ request()->is('reports/download') ? 'active' : '' }}" href="{{ route('reports.download') }}">
                                <i class="bi bi-download me-2"></i> Unduh Laporan
                            </a>
                        </li>
                    @endif
                    <li class="nav-item animate__animated animate__fadeInLeft" style="animation-delay: 0.9s;">
                        <a class="nav-link text-danger" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
             @endauth
        </div>

        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <nav class="navbar navbar-expand-md navbar-light shadow-sm" style="position: sticky; top: 0; z-index: 999; background: white !important;">
                <div class="container-fluid">
                    <div class="d-flex align-items-center flex-grow-1">
                        <div class="toggle-btn me-3 d-none d-md-block animate__animated animate__fadeIn" id="sidebarCollapse">
                            <i class="bi bi-list"></i>
                        </div>
                        <div class="toggle-btn me-2 d-md-none animate__animated animate__fadeIn" id="sidebarCollapseMobileNav" style="color: #333;">
                            <i class="bi bi-list"></i>
                        </div>
                        <span class="navbar-brand mb-0 h1 animate__animated animate__fadeIn text-truncate">@yield('title')</span>
                    </div>
                    @auth
                    <div class="text-end flex-shrink-0">
                        <a href="{{ route('profile.edit') }}" class="user-badge bg-primary text-white text-decoration-none animate__animated animate__fadeIn d-flex align-items-center">
                            <i class="bi bi-person-circle me-1"></i>
                            <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                            <span class="d-inline d-sm-none">{{ substr(auth()->user()->name, 0, 10) }}...</span>
                            <span class="d-none d-md-inline ms-1">({{ ucfirst(auth()->user()->role) }})</span>
                        </a>
                    </div>
                    @endauth
                </div>
            </nav>

            <div class="content-container">
                @if(request()->has('success'))
                    <div class="alert alert-success animate__animated animate__fadeInDown">
                        <i class="bi bi-check-circle-fill me-2"></i> Transaksi berhasil disimpan!
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success animate__animated animate__fadeInDown">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    {{-- ... (kode error) ... --}}
                @endif

                @if (session('error'))
                    <div class="alert alert-danger" role="alert">
                        {{ session('error') }}
                    </div>
                @endif

        @yield('content')
    </div>

    <!-- PWA Install Banner -->
    <div id="pwa-install-banner" class="pwa-banner pwa-install-banner" style="display: none;">
        <div class="pwa-banner-content">
            <div class="pwa-banner-icon">
                <i class="bi bi-download"></i>
            </div>
            <div class="pwa-banner-text">
                <strong>Install Aplikasi Toko Trijaya</strong>
                <small>Untuk pengalaman yang lebih baik, install aplikasi ke perangkat Anda</small>
            </div>
            <div class="pwa-banner-actions">
                <button id="pwa-install-button" class="btn btn-primary btn-sm">
                    <i class="bi bi-download me-1"></i> Install
                </button>
                <button id="pwa-dismiss-install" class="btn btn-link btn-sm text-white">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- PWA Update Banner -->
    <div id="pwa-update-banner" class="pwa-banner pwa-update-banner" style="display: none;">
        <div class="pwa-banner-content">
            <div class="pwa-banner-icon">
                <i class="bi bi-arrow-clockwise"></i>
            </div>
            <div class="pwa-banner-text">
                <strong>Update Tersedia</strong>
                <small>Versi baru aplikasi tersedia. Update sekarang untuk fitur terbaru.</small>
            </div>
            <div class="pwa-banner-actions">
                <button id="pwa-update-button" class="btn btn-success btn-sm">
                    <i class="bi bi-arrow-clockwise me-1"></i> Update
                </button>
                <button id="pwa-dismiss-update" class="btn btn-link btn-sm text-white">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- PWA Offline Indicator -->
    <div id="pwa-offline-indicator" class="pwa-offline-indicator" style="display: none;">
        <i class="bi bi-wifi-off me-2"></i>
        <span>Anda sedang offline</span>
    </div>

    <!-- PWA Sync Indicator -->
    <div id="pwa-sync-indicator" class="pwa-sync-indicator" style="display: none;">
        <i class="bi bi-arrow-repeat me-2"></i>
        <span id="pwa-sync-text">Menunggu sinkronisasi...</span>
    </div>
        </div>

        <!-- Back to Top Button -->
        <div class="back-to-top" id="backToTop">
            <i class="bi bi-arrow-up"></i>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/idb@7/build/umd.js"></script>

    <!-- Helper kita (akan kita buat di Langkah 2) -->
    <script src="{{ secure_asset('js/idb-helper.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarCollapse = document.getElementById('sidebarCollapse');
            const sidebarCollapseMobile = document.getElementById('sidebarCollapseMobile');
            const sidebarBackdrop = document.getElementById('sidebarBackdrop');
            const backToTop = document.getElementById('backToTop');
            let isCollapsed = false;

            // Toggle sidebar untuk desktop
            sidebarCollapse.addEventListener('click', function() {
                toggleSidebar();
            });

            // Toggle sidebar untuk mobile (dari sidebar)
            if (sidebarCollapseMobile) {
                sidebarCollapseMobile.addEventListener('click', function() {
                    sidebar.classList.remove('sidebar-show');
                    if (sidebarBackdrop) {
                        sidebarBackdrop.classList.remove('show');
                    }
                });
            }

            // Toggle sidebar untuk mobile (dari navbar)
            const sidebarCollapseMobileNav = document.getElementById('sidebarCollapseMobileNav');
            if (sidebarCollapseMobileNav) {
                sidebarCollapseMobileNav.addEventListener('click', function() {
                    sidebar.classList.toggle('sidebar-show');
                    if (sidebarBackdrop) {
                        sidebarBackdrop.classList.toggle('show');
                    }
                });
            }

            // Close sidebar when clicking backdrop
            if (sidebarBackdrop) {
                sidebarBackdrop.addEventListener('click', function() {
                    sidebar.classList.remove('sidebar-show');
                    sidebarBackdrop.classList.remove('show');
                });
            }

            // Back to top button
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    backToTop.style.display = 'block';
                } else {
                    backToTop.style.display = 'none';
                }
            });

            backToTop.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });

            // Cek state sidebar dari localStorage
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                collapseSidebar();
            }

            // Responsif: Sembunyikan sidebar saat resize ke mobile
            function handleResize() {
                if (window.innerWidth <= 768) {
                    collapseSidebar();
                } else {
                    if (localStorage.getItem('sidebarCollapsed') === 'true') {
                        collapseSidebar();
                    } else {
                        expandSidebar();
                    }
                }
            }

            function toggleSidebar() {
                isCollapsed = !isCollapsed;
                if (isCollapsed) {
                    collapseSidebar();
                } else {
                    expandSidebar();
                }
            }

            function collapseSidebar() {
                sidebar.classList.add('sidebar-collapsed');
                mainContent.classList.add('main-content-expanded');
                localStorage.setItem('sidebarCollapsed', 'true');
                isCollapsed = true;
            }

            function expandSidebar() {
                sidebar.classList.remove('sidebar-collapsed');
                mainContent.classList.remove('main-content-expanded');
                localStorage.setItem('sidebarCollapsed', 'false');
                isCollapsed = false;
            }

            // Jalankan saat pertama load dan saat resize
            handleResize();
            window.addEventListener('resize', handleResize);

            // Add animation to elements when they come into view
            const animateOnScroll = function() {
                const elements = document.querySelectorAll('.animate-on-scroll');
                elements.forEach(element => {
                    const elementPosition = element.getBoundingClientRect().top;
                    const windowHeight = window.innerHeight;

                    if (elementPosition < windowHeight - 100) {
                        element.classList.add('animate__animated', 'animate__fadeInUp');
                    }
                });
            };

            window.addEventListener('scroll', animateOnScroll);
            animateOnScroll(); // Run once on page load
        });
    </script>
    @stack('scripts')

    <!-- PWA Enhancements Script -->
    <script src="{{ secure_asset('js/pwa-enhancements.js') }}"></script>

    {{-- Service Worker Registration (Fallback jika @laravelPWA tidak load) --}}
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                // IMPORTANT: support subfolder/base-path deployments (ngrok -> /toko_trijaya, etc)
                var basePathMeta = document.querySelector('meta[name="app-base-path"]')?.content || '';
                if (basePathMeta.endsWith('/')) basePathMeta = basePathMeta.slice(0, -1);
                var swPath = basePathMeta + '/serviceworker.js';
                var scope = (basePathMeta ? (basePathMeta + '/') : '/');

                navigator.serviceWorker.register(swPath, { scope: scope })
                .then(function(registration) {
                    // ok
                }).catch(function(error) {
                    // silent fail in UI, but useful for debugging
                    console.error('Service Worker registration failed:', error);
                });
            });
        }
    </script>
</body>
</html>
