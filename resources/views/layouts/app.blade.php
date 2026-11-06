<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Machine Production Planner')</title>

    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --green: #16A34A;
            --green-dark: #15803D;
            --green-light: #DCFCE7;
            --green-soft: #F0FDF4;
            --bg: #F8FAFC;
            --surface: #FFFFFF;
            --text: #1F2937;
            --text-muted: #64748B;
            --border: #E2E8F0;
            --danger: #DC2626;
            --danger-light: #FEE2E2;
            --warning: #D97706;
            --warning-light: #FEF3C7;
            --info: #2563EB;
            --info-light: #DBEAFE;
            --sidebar-w: 238px;
            --header-h: 60px;
            --radius: 9px;
        }

        html {
            min-height: 100%;
            scroll-behavior: smooth;
        }

        body {
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 14px;
            line-height: 1.55;
            color: var(--text);
            background: var(--bg);
            overflow-x: hidden;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        input,
        select,
        textarea {
            font: inherit;
        }

        button {
            cursor: pointer;
        }

        /* Main Layout */

        .app-layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            display: flex;
            flex-direction: column;
            width: var(--sidebar-w);
            background: var(--surface);
            border-right: 1px solid var(--border);
            transition: transform .25s ease;
        }

        .sidebar-logo {
            min-height: 72px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 14px 18px;
            background: var(--green);
        }

        .sidebar-logo h1 {
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.35;
        }

        .sidebar-logo span {
            margin-top: 3px;
            color: rgba(255, 255, 255, .8);
            font-size: 11px;
        }

        .sidebar-nav {
            flex: 1;
            padding: 10px 0;
            overflow-y: auto;
        }

        .nav-section-title {
            padding: 15px 18px 7px;
            color: #94A3B8;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 11px;
            min-height: 42px;
            padding: 9px 17px;
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 500;
            border-right: 3px solid transparent;
            transition: .15s ease;
        }

        .nav-item:hover {
            color: var(--green-dark);
            background: var(--green-soft);
        }

        .nav-item.active {
            color: var(--green-dark);
            background: var(--green-light);
            border-right-color: var(--green);
            font-weight: 600;
        }

        .nav-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            font-size: 15px;
            flex-shrink: 0;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 99;
            background: rgba(15, 23, 42, .42);
        }

        .main-content {
            display: flex;
            flex: 1;
            flex-direction: column;
            min-width: 0;
            min-height: 100vh;
            margin-left: var(--sidebar-w);
        }

        /* Header */

        .header {
            position: sticky;
            top: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: var(--header-h);
            padding: 0 24px;
            background: rgba(255, 255, 255, .96);
            border-bottom: 1px solid var(--border);
        }

        .header-title {
            flex: 1;
            min-width: 0;
            color: var(--text);
            font-size: 16px;
            font-weight: 650;
        }

        .header-date {
            display: flex;
            align-items: center;
            gap: 7px;
            color: var(--text-muted);
            font-size: 12px;
            white-space: nowrap;
        }

        .header-hamburger {
            display: none;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            color: var(--text);
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 7px;
        }

        .page-content {
            flex: 1;
            width: 100%;
            padding: 22px 24px;
        }

        /* Cards */

        .card {
            min-width: 0;
            overflow: hidden;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
        }

        .card-title {
            color: var(--text);
            font-size: 14px;
            font-weight: 650;
        }

        .card-body {
            padding: 16px;
        }

        /* KPI */

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }

        .kpi-card {
            min-width: 0;
            padding: 15px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
        }

        .kpi-label {
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .035em;
            text-transform: uppercase;
        }

        .kpi-value {
            margin-top: 5px;
            color: var(--text);
            font-size: 23px;
            font-weight: 700;
            line-height: 1.3;
        }

        .kpi-sub {
            margin-top: 3px;
            color: var(--text-muted);
            font-size: 11px;
        }

        .kpi-card.green-accent {
            border-top: 3px solid var(--green);
        }

        .kpi-card.red-accent {
            border-top: 3px solid var(--danger);
        }

        .kpi-card.yellow-accent {
            border-top: 3px solid var(--warning);
        }

        /* Tables */

        .table-container {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        thead th {
            padding: 10px 12px;
            color: var(--text-muted);
            background: #F8FAFC;
            border-bottom: 1px solid var(--border);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .045em;
            text-align: left;
            text-transform: uppercase;
            white-space: nowrap;
        }

        tbody td {
            padding: 10px 12px;
            border-bottom: 1px solid #F1F5F9;
            vertical-align: middle;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background: #FAFCFA;
        }

        tfoot td {
            padding: 11px 12px;
            border-top: 1px solid var(--border);
        }

        /* Badges */

        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 3px 8px;
            border-radius: 99px;
            font-size: 10px;
            font-weight: 650;
            white-space: nowrap;
        }

        .badge-green {
            color: var(--green-dark);
            background: var(--green-light);
        }

        .badge-red {
            color: var(--danger);
            background: var(--danger-light);
        }

        .badge-yellow {
            color: #92400E;
            background: var(--warning-light);
        }

        .badge-blue {
            color: #1D4ED8;
            background: var(--info-light);
        }

        .badge-gray {
            color: #475569;
            background: #F1F5F9;
        }

        /* Buttons */

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 34px;
            padding: 7px 13px;
            border: 1px solid transparent;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.3;
            white-space: nowrap;
            transition: background .15s, border-color .15s;
        }

        .btn-primary {
            color: #fff;
            background: var(--green);
            border-color: var(--green);
        }

        .btn-primary:hover {
            background: var(--green-dark);
            border-color: var(--green-dark);
        }

        .btn-secondary {
            color: var(--text);
            background: #fff;
            border-color: var(--border);
        }

        .btn-secondary:hover {
            background: #F8FAFC;
            border-color: #CBD5E1;
        }

        .btn-danger {
            color: #fff;
            background: var(--danger);
            border-color: var(--danger);
        }

        .btn-danger:hover {
            background: #B91C1C;
        }

        .btn-warning {
            color: #fff;
            background: var(--warning);
            border-color: var(--warning);
        }

        .btn-sm {
            min-height: 29px;
            padding: 5px 10px;
            font-size: 11px;
        }

        .btn-icon {
            padding: 6px;
        }

        .btn:disabled {
            cursor: not-allowed;
            opacity: .6;
        }

        /* Forms */

        .form-group {
            min-width: 0;
            margin-bottom: 14px;
        }

        .form-label {
            display: block;
            margin-bottom: 5px;
            color: var(--text);
            font-size: 12px;
            font-weight: 600;
        }

        .form-label.required::after {
            color: var(--danger);
            content: ' *';
        }

        .form-control {
            display: block;
            width: 100%;
            min-width: 0;
            min-height: 37px;
            padding: 8px 10px;
            color: var(--text);
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 13px;
            transition: border-color .15s, box-shadow .15s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(22, 163, 74, .1);
        }

        .form-control.is-invalid {
            border-color: var(--danger);
        }

        select.form-control {
            cursor: pointer;
        }

        textarea.form-control {
            min-height: 80px;
            resize: vertical;
        }

        .form-error {
            margin-top: 4px;
            color: var(--danger);
            font-size: 11px;
        }

        .form-hint {
            margin-top: 4px;
            color: var(--text-muted);
            font-size: 11px;
        }

        .form-row {
            display: grid;
            gap: 12px;
        }

        .form-row-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .form-row-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        /* Filters */

        .filter-bar {
            display: flex;
            align-items: flex-end;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 16px;
            padding: 14px 16px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--radius);
        }

        .filter-item {
            display: flex;
            flex-direction: column;
            min-width: 130px;
            gap: 4px;
        }

        .filter-label {
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 600;
        }

        .filter-control {
            width: 100%;
            min-height: 35px;
            padding: 7px 9px;
            color: var(--text);
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 12px;
        }

        .filter-control:focus {
            outline: none;
            border-color: var(--green);
        }

        /* Alerts */

        .alert {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 14px;
            padding: 11px 14px;
            border: 1px solid transparent;
            border-radius: 7px;
            font-size: 12px;
        }

        .alert-success {
            color: var(--green-dark);
            background: var(--green-light);
            border-color: #BBF7D0;
        }

        .alert-error {
            color: var(--danger);
            background: var(--danger-light);
            border-color: #FECACA;
        }

        .alert-warning {
            color: #92400E;
            background: var(--warning-light);
            border-color: #FDE68A;
        }

        .alert-info {
            color: #1D4ED8;
            background: var(--info-light);
            border-color: #BFDBFE;
        }

        /* Toast */

        #toast-container {
            position: fixed;
            top: 16px;
            right: 16px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-width: calc(100vw - 32px);
        }

        .toast {
            max-width: 340px;
            padding: 11px 15px;
            color: #fff;
            border-radius: 7px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, .12);
            font-size: 12px;
            font-weight: 600;
            animation: slideIn .2s ease;
        }

        .toast-success {
            background: var(--green-dark);
        }

        .toast-error {
            background: var(--danger);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(15px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Modal */

        .modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 200;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
            background: rgba(15, 23, 42, .45);
        }

        .modal-backdrop.open {
            display: flex;
        }

        .modal {
            width: 100%;
            max-width: 520px;
            max-height: 90vh;
            overflow-y: auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .15);
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 18px;
            border-bottom: 1px solid var(--border);
        }

        .modal-title {
            font-size: 14px;
            font-weight: 700;
        }

        .modal-close {
            padding: 4px 7px;
            color: var(--text-muted);
            background: none;
            border: none;
            border-radius: 5px;
            font-size: 18px;
        }

        .modal-close:hover {
            background: var(--bg);
        }

        .modal-body {
            padding: 18px;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            padding: 12px 18px;
            border-top: 1px solid var(--border);
        }

        /* Pagination */

        .pagination {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 4px;
            padding: 14px 0;
        }

        .pagination a,
        .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 31px;
            height: 31px;
            padding: 0 8px;
            color: var(--text);
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 12px;
        }

        .pagination a:hover {
            color: var(--green-dark);
            background: var(--green-light);
            border-color: var(--green);
        }

        .pagination .active span {
            color: #fff;
            background: var(--green);
            border-color: var(--green);
            font-weight: 700;
        }

        .pagination .disabled span {
            opacity: .4;
        }

        /* Empty State */

        .empty-state {
            padding: 42px 20px;
            color: var(--text-muted);
            text-align: center;
        }

        .empty-state-icon {
            margin-bottom: 10px;
            font-size: 30px;
            opacity: .45;
        }

        .empty-state h3 {
            margin-bottom: 4px;
            color: var(--text);
            font-size: 13px;
            font-weight: 650;
        }

        .empty-state p {
            font-size: 12px;
        }

        /* Utilities */

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-muted {
            color: var(--text-muted);
        }

        .text-green {
            color: var(--green);
        }

        .text-danger {
            color: var(--danger);
        }

        .text-warning {
            color: var(--warning);
        }

        .font-bold {
            font-weight: 700;
        }

        .font-semibold {
            font-weight: 600;
        }

        .d-flex {
            display: flex;
        }

        .align-center {
            align-items: center;
        }

        .justify-between {
            justify-content: space-between;
        }

        .gap-8 {
            gap: 8px;
        }

        .gap-12 {
            gap: 12px;
        }

        .mt-4 {
            margin-top: 4px;
        }

        .mt-8 {
            margin-top: 8px;
        }

        .mt-12 {
            margin-top: 12px;
        }

        .mt-16 {
            margin-top: 16px;
        }

        .mt-20 {
            margin-top: 20px;
        }

        .mb-8 {
            margin-bottom: 8px;
        }

        .mb-12 {
            margin-bottom: 12px;
        }

        .mb-16 {
            margin-bottom: 16px;
        }

        .mb-20 {
            margin-bottom: 20px;
        }

        .w-full {
            width: 100%;
        }

        .hidden {
            display: none !important;
        }

        .nowrap {
            white-space: nowrap;
        }

        .num {
            font-variant-numeric: tabular-nums;
        }

        /* Loading */

        .spinner {
            display: inline-block;
            width: 15px;
            height: 15px;
            border: 2px solid #E5E7EB;
            border-top-color: var(--green);
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Responsive */

        @media (max-width: 1024px) {
            :root {
                --sidebar-w: 215px;
            }

            .page-content {
                padding: 18px;
            }

            .header {
                padding: 0 18px;
            }

            .kpi-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                box-shadow: 8px 0 30px rgba(15, 23, 42, .08);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .sidebar-overlay.open {
                display: block;
            }

            .main-content {
                margin-left: 0;
            }

            .header {
                min-height: 56px;
                padding: 0 14px;
            }

            .header-hamburger {
                display: inline-flex;
            }

            .header-title {
                font-size: 14px;
            }

            .header-date {
                font-size: 10px;
            }

            .page-content {
                padding: 15px;
            }

            .kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 9px;
            }

            .form-row-2,
            .form-row-3 {
                grid-template-columns: 1fr;
            }

            .filter-bar {
                align-items: stretch;
                flex-direction: column;
                padding: 12px;
            }

            .filter-item {
                width: 100%;
                min-width: 0;
            }

            .filter-control {
                width: 100%;
            }

            .d-flex.mobile-stack {
                flex-direction: column;
            }
        }

        @media (max-width: 480px) {
            .page-content {
                padding: 12px;
            }

            .header {
                padding: 0 12px;
            }

            .header-date {
                max-width: 110px;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .kpi-card {
                padding: 11px;
            }

            .kpi-label {
                font-size: 10px;
            }

            .kpi-value {
                font-size: 20px;
            }

            .kpi-sub {
                font-size: 10px;
            }

            .card-header {
                padding: 12px;
            }

            .card-body {
                padding: 12px;
            }

            .btn {
                padding: 7px 10px;
            }

            .btn-sm {
                padding: 5px 8px;
            }

            .modal-backdrop {
                align-items: flex-end;
                padding: 10px;
            }

            .modal {
                max-height: 85vh;
                border-radius: 12px;
            }

            .empty-state {
                padding: 30px 14px;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
    <div class="app-layout">

        <div
            class="sidebar-overlay"
            id="sidebarOverlay"
            onclick="closeSidebar()"></div>

        <nav class="sidebar" id="sidebar">
            <div class="sidebar-logo">
                <h1>Planning MP Mesin</h1>
                <span>Stationery Spiral</span>
            </div>

            <div class="sidebar-nav">
                <div class="nav-section-title">Menu Utama</div>

                <a
                    href="{{ route('manpower.index') }}"
                    class="nav-item {{ request()->routeIs('manpower.*') ? 'active' : '' }}">
                    <span class="nav-icon">👥</span>
                    <span>Manpower</span>
                </a>

                <a
                    href="{{ route('dashboard') }}"
                    class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="nav-icon">📊</span>
                    <span>Dashboard</span>
                </a>

                <a
                    href="{{ route('schedules.index') }}"
                    class="nav-item {{ request()->routeIs('schedules.*') ? 'active' : '' }}">
                    <span class="nav-icon">📅</span>
                    <span>Jadwal Produksi</span>
                </a>

                <a
                    href="{{ route('mes.index') }}"
                    class="nav-item {{ request()->routeIs('mes.*') ? 'active' : '' }}">
                    <span class="nav-icon">🏭</span>
                    <span>Aktual Produksi</span>
                </a>

                <a
                    href="{{ route('recap.index') }}"
                    class="nav-item {{ request()->routeIs('recap.*') ? 'active' : '' }}">
                    <span class="nav-icon">📈</span>
                    <span>Rekap Produksi</span>
                </a>

                <div class="nav-section-title">Konfigurasi</div>

                <a
                    href="{{ route('settings.index') }}"
                    class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <span class="nav-icon">⚙️</span>
                    <span>Pengaturan</span>
                </a>
            </div>
        </nav>

        <div class="main-content">

            <header class="header">
                <button
                    type="button"
                    class="header-hamburger"
                    onclick="toggleSidebar()"
                    aria-label="Buka menu"
                    aria-controls="sidebar"
                    aria-expanded="false"
                    id="sidebarToggle">
                    <svg
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round">
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <line x1="3" y1="12" x2="21" y2="12" />
                        <line x1="3" y1="18" x2="21" y2="18" />
                    </svg>
                </button>

                <div class="header-title">
                    @yield('page-title', 'Dashboard')
                </div>

                <div class="header-date">
                    <span>📅</span>
                    <span id="currentDate"></span>
                </div>
            </header>

            <main class="page-content">

                @if(session('success'))
                <div class="alert alert-success">
                    <span>✓</span>
                    <span>{{ session('success') }}</span>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-error">
                    <span>✕</span>
                    <span>{{ session('error') }}</span>
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-error">
                    <span>✕</span>
                    <div>
                        <strong>Terdapat kesalahan:</strong>
                        <ul style="margin-top:4px;padding-left:16px;">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                @yield('content')
            </main>

        </div>
    </div>

    <div id="toast-container"></div>

    <script>
        const currentDateElement = document.getElementById('currentDate');

        if (currentDateElement) {
            currentDateElement.textContent = new Date().toLocaleDateString('id-ID', {
                weekday: 'short',
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const toggle = document.getElementById('sidebarToggle');

            const isOpen = sidebar.classList.toggle('open');

            overlay.classList.toggle('open', isOpen);
            toggle.setAttribute('aria-expanded', String(isOpen));
        }

        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('sidebarOverlay').classList.remove('open');
            document.getElementById('sidebarToggle').setAttribute('aria-expanded', 'false');
        }

        document.querySelectorAll('.sidebar .nav-item').forEach(function(item) {
            item.addEventListener('click', closeSidebar);
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeSidebar();
            }
        });

        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');

            toast.className = 'toast toast-' + type;
            toast.textContent = message;

            container.appendChild(toast);

            setTimeout(function() {
                toast.remove();
            }, 4000);
        }

        function openModal(id) {
            const modal = document.getElementById(id);

            if (modal) {
                modal.classList.add('open');
            }
        }

        function closeModal(id) {
            const modal = document.getElementById(id);

            if (modal) {
                modal.classList.remove('open');
            }
        }

        const CSRF_TOKEN = document.querySelector(
            'meta[name="csrf-token"]'
        ).content;

        function confirmDelete(formId) {
            if (confirm('Yakin ingin menghapus data ini?')) {
                const form = document.getElementById(formId);

                if (form) {
                    form.submit();
                }
            }
        }

        setTimeout(function() {
            document.querySelectorAll(
                'main.page-content > .alert'
            ).forEach(function(alert) {
                alert.style.transition = 'opacity .4s';
                alert.style.opacity = '0';

                setTimeout(function() {
                    alert.remove();
                }, 400);
            });
        }, 5000);
    </script>

    @stack('scripts')
</body>

</html>