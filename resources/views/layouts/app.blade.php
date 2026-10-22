<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} - {{ config('app.name') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16.png') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS (utilities only for modal/grid) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-w: 272px;
            --topbar-h: 64px;
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --accent: #8b5cf6;
            --bg-dark: #0f172a;
            --bg-card: #1e293b;
            --bg-surface: #1a2332;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --border: rgba(255,255,255,0.06);
            --shadow: 0 4px 24px rgba(0,0,0,0.2);
            --radius: 16px;
            --radius-sm: 12px;
            --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif !important;
            background: var(--bg-dark) !important;
            color: var(--text-primary) !important;
            min-height: 100vh;
            overflow-x: hidden;
        }
        /* Override Bootstrap defaults for dark theme */
        .card { background: transparent; border: none; }
        .modal-backdrop { background: rgba(0,0,0,0.7) !important; backdrop-filter: blur(4px); }
        .btn-close { filter: invert(1) brightness(2); }
        h1, h2, h3, h4, h5, h6 { color: #f1f5f9 !important; }
        p, span, label, a { color: inherit; }
        .text-muted { color: #94a3b8 !important; }
        .modal-title { color: #f1f5f9 !important; }
        .nav-section { color: #64748b !important; }

        /* ===== SIDEBAR ===== */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-w);
            height: 100vh;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(40px) saturate(180%);
            -webkit-backdrop-filter: blur(40px) saturate(180%);
            border-right: 1px solid rgba(255, 255, 255, 0.06);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transition: var(--transition);
        }

        .sidebar-brand {
            padding: 1.5rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid var(--border);
        }
        .sidebar-brand .logo {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(59,130,246,0.3);
        }
        .sidebar-brand .logo i { color: #fff; font-size: 1.1rem; }
        .sidebar-brand h2 { font-size: 1rem; font-weight: 700; color: var(--text-primary); }
        .sidebar-brand span { font-size: 0.7rem; color: var(--text-muted); }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 1rem 0.75rem;
        }
        .sidebar-nav::-webkit-scrollbar { width: 3px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: var(--text-muted); border-radius: 3px; }

        .nav-section {
            font-size: 0.68rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: var(--text-muted);
            padding: 1.2rem 0.75rem 0.5rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.7rem 0.75rem;
            border-radius: var(--radius-sm);
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: var(--transition);
            cursor: pointer;
            position: relative;
            margin-bottom: 2px;
        }
        .nav-item:hover {
            color: var(--text-primary);
            background: rgba(59, 130, 246, 0.08);
        }
        .nav-item.active {
            color: #fff;
            background: rgba(59, 130, 246, 0.12);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(59, 130, 246, 0.2);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }
        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0; top: 50%;
            transform: translateY(-50%);
            width: 3px; height: 60%;
            background: linear-gradient(180deg, var(--primary), var(--accent));
            border-radius: 0 3px 3px 0;
        }
        .nav-item i { font-size: 1.1rem; width: 20px; text-align: center; }

        /* ===== MAIN ===== */
        .main {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ===== TOPBAR ===== */
        .topbar {
            position: sticky;
            top: 0;
            height: var(--topbar-h);
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(40px) saturate(180%);
            -webkit-backdrop-filter: blur(40px) saturate(180%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            z-index: 100;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .topbar-left .page-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #f1f5f9 !important;
        }
        .breadcrumb-nav {
            display: flex;
            gap: 0.3rem;
            font-size: 0.78rem;
            color: var(--text-muted);
        }
        .breadcrumb-nav a { color: var(--text-secondary); text-decoration: none; }
        .breadcrumb-nav a:hover { color: var(--primary); }
        .breadcrumb-nav .sep { margin: 0 0.2rem; }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .user-menu {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.4rem 0.8rem 0.4rem 0.5rem;
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 50px;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            color: var(--text-primary);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
        }
        .user-menu:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.15);
        }
        .user-avatar {
            width: 32px; height: 32px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            color: #fff;
        }
        .user-name { font-size: 0.82rem; font-weight: 500; }

        /* ===== CONTENT ===== */
        .content {
            flex: 1;
            padding: 2rem;
        }

        /* ===== CARDS ===== */
        .card {
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(40px) saturate(180%);
            -webkit-backdrop-filter: blur(40px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            box-shadow:
                0 8px 32px rgba(0, 0, 0, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.06);
            overflow: hidden;
            position: relative;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.12), transparent);
            z-index: 1;
        }
        .card:hover {
            border-color: rgba(255, 255, 255, 0.12);
            box-shadow:
                0 16px 48px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }
        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(0, 0, 0, 0.15);
        }
        .card-header h5 { font-size: 1rem; font-weight: 600; color: #f1f5f9 !important; }
        .card-header .badge, .card-header span { color: inherit; }
        .card-body { padding: 1.5rem; }

        /* ===== STAT CARDS ===== */
        .stat-card {
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(40px) saturate(180%);
            -webkit-backdrop-filter: blur(40px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            padding: 1.5rem;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow:
                0 8px 32px rgba(0, 0, 0, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.06);
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.12), transparent);
        }
        .stat-card:hover {
            transform: translateY(-3px);
            border-color: rgba(255, 255, 255, 0.15);
            box-shadow:
                0 16px 48px rgba(0, 0, 0, 0.25),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }
        .stat-card::after {
            content: '';
            position: absolute;
            top: -50%; right: -30%;
            width: 120px; height: 120px;
            background: radial-gradient(circle, rgba(59,130,246,0.06), transparent);
            border-radius: 50%;
        }
        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin-bottom: 1rem;
        }
        .stat-icon.blue { background: rgba(59,130,246,0.1); color: #3b82f6; }
        .stat-icon.green { background: rgba(16,185,129,0.1); color: #10b981; }
        .stat-icon.purple { background: rgba(139,92,246,0.1); color: #8b5cf6; }
        .stat-icon.orange { background: rgba(245,158,11,0.1); color: #f59e0b; }
        .stat-value { font-size: 1.8rem; font-weight: 700; line-height: 1; margin-bottom: 0.3rem; color: #fff !important; }
        .stat-label { font-size: 0.8rem; color: #94a3b8 !important; font-weight: 500; }

        /* ===== BUTTONS ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.55rem 1rem;
            border-radius: var(--radius-sm);
            font-size: 0.82rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            font-family: 'Inter', sans-serif;
        }
        .btn-primary {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.8), rgba(37, 99, 235, 0.9));
            backdrop-filter: blur(10px);
            color: #fff;
            border: 1px solid rgba(59, 130, 246, 0.3);
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.25);
        }
        .btn-primary:hover {
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4);
            transform: translateY(-1px);
            border-color: rgba(59, 130, 246, 0.5);
        }
        .btn-sm { padding: 0.4rem 0.7rem; font-size: 0.78rem; border-radius: 10px; }
        .btn-info { background: rgba(59,130,246,0.08); color: #60a5fa; border: 1px solid rgba(59,130,246,0.2); backdrop-filter: blur(10px); }
        .btn-info:hover { background: rgba(59,130,246,0.15); border-color: rgba(59,130,246,0.4); }
        .btn-danger { background: rgba(239,68,68,0.08); color: #f87171; border: 1px solid rgba(239,68,68,0.2); backdrop-filter: blur(10px); }
        .btn-danger:hover { background: rgba(239,68,68,0.15); border-color: rgba(239,68,68,0.4); }
        .btn-success { background: rgba(16,185,129,0.08); color: #34d399; border: 1px solid rgba(16,185,129,0.2); backdrop-filter: blur(10px); }
        .btn-success:hover { background: rgba(16,185,129,0.15); border-color: rgba(16,185,129,0.4); }
        .btn-outline-primary { background: rgba(59,130,246,0.05); color: var(--primary); border: 1px solid rgba(59,130,246,0.2); backdrop-filter: blur(10px); }
        .btn-outline-primary:hover { background: rgba(59,130,246,0.12); border-color: rgba(59,130,246,0.4); }

        /* ===== TABLES ===== */
        .table { color: var(--text-primary) !important; border-collapse: separate !important; border-spacing: 0; background: transparent !important; }
        .table > :not(caption) > * > * { background: transparent !important; color: var(--text-primary) !important; border-bottom-color: rgba(255, 255, 255, 0.04) !important; padding: 0.85rem 1rem !important; box-shadow: none !important; }
        .table thead th {
            background: rgba(0, 0, 0, 0.15) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: rgba(255, 255, 255, 0.5) !important;
            padding: 0.9rem 1rem !important;
        }
        .table tbody td {
            padding: 0.85rem 1rem !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
            font-size: 0.84rem;
            vertical-align: middle;
            background: transparent !important;
        }
        .table tbody tr { transition: var(--transition); }
        .table tbody tr:hover > * { background: rgba(59, 130, 246, 0.04) !important; }
        .table-striped > tbody > tr:nth-of-type(odd) > * { background: transparent !important; }
        .table-hover > tbody > tr:hover > * { background: rgba(59, 130, 246, 0.04) !important; }

        /* ===== BADGES ===== */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.3rem 0.7rem;
            border-radius: 50px;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.3px;
        }
        .bg-info { background: rgba(59,130,246,0.12) !important; color: #60a5fa !important; }
        .bg-warning { background: rgba(245,158,11,0.12) !important; color: #fbbf24 !important; }
        .bg-success { background: rgba(16,185,129,0.12) !important; color: #34d399 !important; }
        .bg-secondary { background: rgba(100,116,139,0.15) !important; color: #94a3b8 !important; }
        .bg-primary { background: rgba(59,130,246,0.12) !important; color: #60a5fa !important; }
        .bg-danger { background: rgba(239,68,68,0.12) !important; color: #f87171 !important; }
        .bg-light { background: rgba(255,255,255,0.05) !important; color: var(--text-secondary) !important; }

        /* ===== FORMS (Modal) ===== */
        .modal-content {
            background: rgba(30, 41, 59, 0.85);
            backdrop-filter: blur(40px) saturate(180%);
            -webkit-backdrop-filter: blur(40px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            color: var(--text-primary);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5);
        }
        .modal-header { border-bottom: 1px solid rgba(255, 255, 255, 0.06); padding: 1.25rem 1.5rem; }
        .modal-header .modal-title { font-weight: 600; }
        .modal-header .btn-close { filter: invert(1); }
        .modal-body { padding: 1.5rem; }
        .form-label { font-size: 0.8rem; font-weight: 500; color: #cbd5e1 !important; margin-bottom: 0.4rem; }
        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            padding: 0.7rem 1rem;
            font-size: 0.85rem;
            transition: var(--transition);
        }
        .form-control:focus, .form-select:focus {
            background: rgba(59, 130, 246, 0.05);
            border-color: rgba(59, 130, 246, 0.5);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            color: var(--text-primary);
        }
        .form-control::placeholder { color: var(--text-muted); }
        .form-control:disabled, .form-control[disabled], .form-select:disabled {
            background: rgba(255, 255, 255, 0.06) !important;
            color: #94a3b8 !important;
            -webkit-text-fill-color: #94a3b8 !important;
            border-color: rgba(255, 255, 255, 0.06) !important;
            opacity: 1 !important;
        }
        .text-danger { color: #ef4444 !important; }

        /* ===== DATATABLE OVERRIDES ===== */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: var(--text-primary);
            padding: 0.4rem 0.8rem;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: rgba(59, 130, 246, 0.4);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }
        .dataTables_wrapper .dataTables_info { color: var(--text-muted); font-size: 0.8rem; }

        /* Pagination fix - force horizontal layout */
        .dataTables_wrapper .dataTables_paginate {
            display: flex !important;
            align-items: center !important;
        }
        .dataTables_wrapper .dataTables_paginate span {
            display: flex !important;
            align-items: center !important;
            gap: 4px;
        }
        .dataTables_wrapper .dataTables_paginate ul,
        .dataTables_wrapper .dataTables_paginate > span,
        .dataTables_wrapper .dataTables_paginate .pagination {
            list-style: none !important;
            display: flex !important;
            flex-wrap: wrap;
            align-items: center !important;
            gap: 4px !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .dataTables_wrapper .dataTables_paginate ul li,
        .dataTables_wrapper .dataTables_paginate .pagination li {
            list-style: none !important;
            display: inline-flex !important;
            margin: 0 !important;
        }
        .dataTables_wrapper .dataTables_paginate ul li::before,
        .dataTables_wrapper .dataTables_paginate .pagination li::before {
            content: none !important;
            display: none !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button,
        .dataTables_wrapper .dataTables_paginate ul li a,
        .dataTables_wrapper .dataTables_paginate .page-link {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-width: 32px !important;
            height: 32px !important;
            padding: 0 0.5rem !important;
            font-size: 0.8rem !important;
            color: var(--text-secondary) !important;
            border-radius: 8px !important;
            border: 1px solid rgba(255, 255, 255, 0.06) !important;
            background: rgba(255, 255, 255, 0.03) !important;
            text-decoration: none !important;
            cursor: pointer !important;
            transition: all 0.2s !important;
            line-height: 1 !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate ul li.active a,
        .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
            background: rgba(59, 130, 246, 0.15) !important;
            border-color: rgba(59, 130, 246, 0.3) !important;
            color: #60a5fa !important;
            font-weight: 600 !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover,
        .dataTables_wrapper .dataTables_paginate ul li a:hover,
        .dataTables_wrapper .dataTables_paginate .page-link:hover {
            background: rgba(59, 130, 246, 0.1) !important;
            border-color: rgba(59, 130, 246, 0.25) !important;
            color: var(--primary) !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            opacity: 0.4 !important;
            cursor: not-allowed !important;
        }
        .dataTables_wrapper .dataTables_paginate .ellipsis {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-width: 32px !important;
            height: 32px !important;
            color: var(--text-muted) !important;
            border: none !important;
            background: none !important;
        }

        /* ===== RESPONSIVE ===== */
        .sidebar-toggle { display: none; }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); z-index: 1100; }
            .main { margin-left: 0; }
            .content { padding: 1rem; }
            .topbar { padding: 0 1rem; }
            .sidebar-toggle { display: inline-flex !important; }
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0,0,0,0.5);
                backdrop-filter: blur(4px);
                z-index: 1050;
            }
            .sidebar-overlay.show { display: block; }
        }

        /* ===== SELECT2 DARK THEME ===== */
        .select2-container--default .select2-selection--single {
            background: rgba(255, 255, 255, 0.04) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 10px !important;
            height: 40px !important;
            padding: 5px 12px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #f1f5f9 !important;
            line-height: 28px !important;
            padding-left: 0 !important;
            font-size: 0.85rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__placeholder { color: #64748b !important; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px !important; }
        .select2-container--default .select2-selection--single .select2-selection__arrow b { border-color: #64748b transparent transparent transparent !important; }
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: rgba(59, 130, 246, 0.5) !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
        }
        .select2-dropdown {
            background: rgba(30, 41, 59, 0.97) !important;
            backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 12px !important;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5) !important;
            overflow: hidden;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 8px !important;
            color: #f1f5f9 !important;
            padding: 8px 12px !important;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: rgba(59, 130, 246, 0.5) !important;
            outline: none;
        }
        .select2-container--default .select2-results__option {
            color: #cbd5e1 !important;
            padding: 8px 12px !important;
            font-size: 0.85rem;
            border-radius: 6px;
            margin: 2px 4px;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: rgba(59, 130, 246, 0.2) !important;
            color: #fff !important;
        }
        .select2-container--default .select2-results__option[aria-selected=true] {
            background: rgba(59, 130, 246, 0.1) !important;
            color: #60a5fa !important;
        }
        .select2-search--dropdown { padding: 8px !important; }
        .select2-results { max-height: 250px; }
        .select2-container { width: 100% !important; }
        .select2-container--default .select2-selection--single .select2-selection__clear { color: #94a3b8 !important; font-size: 1.2rem; }

        /* ===== SCROLLBAR ===== */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }

        /* ===== GRID HELPERS ===== */
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; }
        @media (max-width: 1024px) { .grid-4 { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 600px) { .grid-4 { grid-template-columns: 1fr; } }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar Overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="logo"><i class="bi bi-lightning-charge-fill"></i></div>
            <div>
                <h2>Aira Link</h2>
                <span>Financial Management</span>
            </div>
        </div>
        <nav class="sidebar-nav">
            @can('dashboard-view')
            <a href="{{ url('/home') }}" class="nav-item {{ request()->is('home') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard
            </a>
            @endcan

            @can('transfer-view')
            <a href="{{ url('/transfer') }}" class="nav-item {{ request()->is('transfer') ? 'active' : '' }}">
                <i class="bi bi-arrow-up-circle"></i> Transfer
            </a>
            @endcan

            @can('tunai-view')
            <a href="{{ url('/tunai') }}" class="nav-item {{ request()->is('tunai') ? 'active' : '' }}">
                <i class="bi bi-arrow-down-circle"></i> Tunai
            </a>
            @endcan

            @can('kalkulator-view')
            <a href="{{ url('/kalkulator') }}" class="nav-item {{ request()->is('kalkulator') ? 'active' : '' }}">
                <i class="bi bi-calculator"></i> Kalkulator
            </a>
            @endcan

            @can('pengeluaran-view')
            <a href="{{ url('/pengeluaran') }}" class="nav-item {{ request()->is('pengeluaran') ? 'active' : '' }}">
                <i class="bi bi-cash-coin"></i> Pengeluaran
            </a>
            @endcan

            @if(auth()->user()?->username === 'ramdan')
            <a href="{{ url('/rekap-pengeluaran') }}" class="nav-item {{ request()->is('rekap-pengeluaran*') ? 'active' : '' }}">
                <i class="bi bi-journal-text"></i> Rekap Pengeluaran
            </a>
            @endif

            @can('investor-view')
            <a href="{{ url('/investor') }}" class="nav-item {{ request()->is('investor') ? 'active' : '' }}">
                <i class="bi bi-graph-up-arrow"></i> Investor
            </a>
            @endcan

            @can('investor-view')
            <a href="{{ url('/laporan') }}" class="nav-item {{ request()->is('laporan') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-pdf"></i> Laporan
            </a>
            @endcan

            @can('uang-cash-view')
            <a href="{{ url('/uang-cash') }}" class="nav-item {{ request()->is('uang-cash') ? 'active' : '' }}">
                <i class="bi bi-wallet2"></i> Uang Cash
            </a>
            @endcan

            @can('uang-cash-monitoring-view')
            <a href="{{ url('/uang-cash-monitoring') }}" class="nav-item {{ request()->is('uang-cash-monitoring') ? 'active' : '' }}">
                <i class="bi bi-eye"></i> Uang Cash Monitoring
            </a>
            @endcan

            <a href="{{ url('/closing') }}" class="nav-item {{ request()->is('closing*') ? 'active' : '' }}">
                <i class="bi bi-cash-stack"></i> Closing
            </a>

            @canany(['transaksi-transfer-view', 'transaksi-tarik-tunai-view'])
            <div class="nav-section">Transaksi Bank</div>
            @can('transaksi-transfer-view')
            <a href="{{ url('/transaksi/transfer') }}" class="nav-item {{ request()->is('transaksi/transfer*') ? 'active' : '' }}">
                <i class="bi bi-arrow-left-right"></i> Transfer
            </a>
            @endcan
            @can('transaksi-tarik-tunai-view')
            <a href="{{ url('/transaksi/tarik-tunai') }}" class="nav-item {{ request()->is('transaksi/tarik-tunai*') ? 'active' : '' }}">
                <i class="bi bi-cash-coin"></i> Tarik Tunai
            </a>
            @endcan
            @endcanany

            @can('master-pegawai-view')
            <div class="nav-section">Data Master</div>
            <a href="{{ url('/master/pegawai') }}" class="nav-item {{ request()->is('master/pegawai*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> Pegawai
            </a>
            <a href="{{ url('/rekening-valid') }}" class="nav-item {{ request()->is('rekening-valid*') ? 'active' : '' }}">
                <i class="bi bi-credit-card"></i> Rekening Valid
            </a>
            @endcan

            @canany(['konfigurasi-users-view', 'konfigurasi-roles-view'])
            <div class="nav-section">Konfigurasi</div>
            @can('konfigurasi-users-view')
            <a href="{{ url('/konfigurasi/users') }}" class="nav-item {{ request()->is('konfigurasi/users*') ? 'active' : '' }}">
                <i class="bi bi-person-fill-gear"></i> Manajemen User
            </a>
            @endcan
            @can('konfigurasi-roles-view')
            <a href="{{ url('/konfigurasi/roles') }}" class="nav-item {{ request()->is('konfigurasi/roles*') ? 'active' : '' }}">
                <i class="bi bi-shield-fill-check"></i> Hak Akses
            </a>
            @endcan
            @endcanany
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="main">
        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="btn btn-sm btn-outline-primary sidebar-toggle" onclick="toggleSidebar()">
                    <i class="bi bi-list" style="font-size: 1.2rem;"></i>
                </button>
                <div>
                    <div class="page-title">{{ $title ?? 'Dashboard' }}</div>
                    <div class="breadcrumb-nav">
                        @foreach($breadcrumb ?? [] as $label => $url)
                            @if(!$loop->first)<span class="sep">/</span>@endif
                            <a href="{{ $url }}">{{ $label }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <a href="{{ url('/logout') }}" class="user-menu">
                    <div class="user-avatar">{{ strtoupper(substr(auth()->user()->username ?? 'U', 0, 2)) }}</div>
                    <span class="user-name">{{ auth()->user()->username ?? 'User' }}</span>
                    <i class="bi bi-box-arrow-right" style="font-size:0.85rem; color:var(--text-muted)"></i>
                </a>
            </div>
        </header>

        <!-- Content -->
        <main class="content">
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        // Global Select2 init untuk semua select.form-select
        $(function() {
            $('.form-select').each(function() {
                if (!$(this).hasClass('no-select2') && !$(this).data('select2')) {
                    $(this).select2({
                        placeholder: $(this).find('option:first').text() || 'Pilih...',
                        allowClear: true,
                        dropdownParent: $(this).closest('.modal').length ? $(this).closest('.modal') : $('body')
                    });
                }
            });
        });

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        }
        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('show');
            document.getElementById('sidebarOverlay').classList.remove('show');
        }

        function deleteData(url) {
            Swal.fire({
                title: 'Hapus Data?',
                text: 'Data yang dihapus tidak dapat dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="bi bi-trash"></i> Ya, Hapus!',
                cancelButtonText: 'Batal',
                background: '#1e293b',
                color: '#f1f5f9',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url, type: 'DELETE',
                        success: function(res) {
                            Swal.fire({ title: 'Terhapus!', text: res.message || 'Data berhasil dihapus.', icon: 'success', background: '#1e293b', color: '#f1f5f9' });
                            if (typeof dataTable !== 'undefined') dataTable.ajax.reload();
                        },
                        error: function() { Swal.fire({ title: 'Error!', text: 'Gagal menghapus data.', icon: 'error', background: '#1e293b', color: '#f1f5f9' }); }
                    });
                }
            });
        }

        function confirmData(url) {
            Swal.fire({
                title: 'Konfirmasi Data?',
                text: 'Ubah status menjadi Success',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="bi bi-check-lg"></i> Konfirmasi',
                cancelButtonText: 'Batal',
                background: '#1e293b',
                color: '#f1f5f9',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url, type: 'PUT', data: { status: 3 },
                        success: function(res) {
                            Swal.fire({ title: 'Berhasil!', text: res.message || 'Data dikonfirmasi.', icon: 'success', background: '#1e293b', color: '#f1f5f9' });
                            if (typeof dataTable !== 'undefined') dataTable.ajax.reload();
                        },
                        error: function() { Swal.fire({ title: 'Error!', text: 'Gagal konfirmasi.', icon: 'error', background: '#1e293b', color: '#f1f5f9' }); }
                    });
                }
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
