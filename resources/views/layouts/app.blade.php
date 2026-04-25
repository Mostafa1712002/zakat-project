@php
    $__siteSettings = \Illuminate\Support\Facades\Cache::remember('sidebar_settings', 3600, function() {
        try {
            return \DB::table('settings')
                ->whereIn('key', ['company_name', 'company_logo', 'color_palette'])
                ->pluck('value', 'key')
                ->toArray();
        } catch (\Exception $e) { return []; }
    });
    $__sidebarLogo = $__siteSettings['company_logo'] ?? '';
    $__sidebarName = $__siteSettings['company_name'] ?? config('app.name', 'CRM');
    $__palettes = [
        'cyan'    => ['primary' => '#0891b2', 'dark' => '#0e7490', 'light' => '#06b6d4'],
        'blue'    => ['primary' => '#2563eb', 'dark' => '#1d4ed8', 'light' => '#3b82f6'],
        'indigo'  => ['primary' => '#6366f1', 'dark' => '#4f46e5', 'light' => '#818cf8'],
        'purple'  => ['primary' => '#7c3aed', 'dark' => '#6d28d9', 'light' => '#8b5cf6'],
        'rose'    => ['primary' => '#e11d48', 'dark' => '#be123c', 'light' => '#f43f5e'],
        'emerald' => ['primary' => '#059669', 'dark' => '#047857', 'light' => '#10b981'],
        'amber'   => ['primary' => '#d97706', 'dark' => '#b45309', 'light' => '#f59e0b'],
        'slate'   => ['primary' => '#475569', 'dark' => '#334155', 'light' => '#64748b'],
    ];
    $__activePalette = $__siteSettings['color_palette'] ?? 'cyan';
    $__colors = $__palettes[$__activePalette] ?? $__palettes['cyan'];
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'لوحة التحكم') - {{ $__sidebarName }}</title>
    <link rel="icon" type="image/png" href="{{ !empty($__sidebarLogo) ? asset('storage/' . $__sidebarLogo) : asset('logo.png') }}">
    <link rel="apple-touch-icon" href="{{ !empty($__sidebarLogo) ? asset('storage/' . $__sidebarLogo) : asset('logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400;1,700&family=Cairo:wght@400;500;600;700;800&family=Reem+Kufi:wght@500;600;700&family=Tajawal:wght@500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Select2 RTL fixes */
        .select2-container--default .select2-selection--single {
            height: 46px;
            padding: 8px 12px;
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            font-family: 'Cairo', sans-serif;
            font-size: 14px;
            direction: rtl;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px;
            padding-right: 0;
            padding-left: 20px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 44px;
            left: 4px;
            right: auto;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            font-family: 'Cairo', sans-serif;
            font-size: 14px;
            padding: 8px 12px;
            direction: rtl;
        }
        .select2-results__option {
            font-family: 'Cairo', sans-serif;
            font-size: 14px;
            direction: rtl;
        }
        .select2-container { width: 100% !important; }
        .select2-dropdown { direction: rtl; }
    </style>
    <style>
        :root {
            --primary: {{ $__colors['primary'] }};
            --primary-dark: {{ $__colors['dark'] }};
            --primary-light: {{ $__colors['light'] }};
            --success: #059669;
            --warning: #f59e0b;
            --danger: #dc2626;
            --bg: #f1f5f9;
            --card: #ffffff;
            --text: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --shadow: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1);
            --radius: 12px;
            --radius-sm: 8px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        img, svg, video, canvas {
            max-width: 100%;
            height: auto;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
        }

        .app {
            display: flex;
            min-height: 100vh;
            width: 100%;
            overflow: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            right: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
            transition: width 0.3s ease;
        }

        .main {
            flex: 1;
            margin-right: 260px;
            padding: 20px;
            min-height: 100vh;
            min-width: 0;
            width: calc(100% - 260px);
            max-width: calc(100% - 260px);
            overflow-x: hidden;
            transition: margin-right 0.3s ease, width 0.3s ease, max-width 0.3s ease;
        }

        /* Sidebar collapse button */
        .sidebar-collapse-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            width: 100%;
            padding: 11px 12px;
            margin: 0 0 14px;
            background: rgba(255,255,255,0.14);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            border-radius: 10px;
            cursor: pointer;
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            order: 2;
            position: sticky;
            top: 8px;
            z-index: 15;
            -webkit-backdrop-filter: blur(6px);
            backdrop-filter: blur(6px);
            transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
        }
        .sidebar-collapse-btn:hover {
            background: rgba(255,255,255,0.24);
            border-color: rgba(255,255,255,0.42);
        }
        .sidebar-collapse-btn:active { transform: translateY(1px); }
        .sidebar-collapse-btn svg {
            flex-shrink: 0;
            transition: transform 0.3s ease;
        }

        /* Custom scrollbar for sidebar */
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-track { background: transparent; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }
        .sidebar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.35); }
        .sidebar { scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.2) transparent; }

        /* JS Tooltip (appended to body, not clipped by sidebar overflow) */
        .sidebar-tooltip {
            position: fixed;
            background: #1e293b;
            color: white;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-family: 'Cairo', sans-serif;
            white-space: nowrap;
            z-index: 9999;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.15s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        .sidebar-tooltip.visible { opacity: 1; }
        .sidebar-tooltip::after {
            content: '';
            position: absolute;
            top: 50%;
            right: -8px;
            transform: translateY(-50%);
            border: 5px solid transparent;
            border-left-color: #1e293b;
        }

        /* Collapsed sidebar */
        .app.sidebar-collapsed .sidebar {
            width: 72px;
            padding: 12px;
            overflow-x: hidden;
        }
        .app.sidebar-collapsed .sidebar::-webkit-scrollbar { display: none; }
        .app.sidebar-collapsed .sidebar { scrollbar-width: none; }
        .app.sidebar-collapsed .main {
            margin-right: 72px;
            width: calc(100% - 72px);
            max-width: calc(100% - 72px);
        }
        .app.sidebar-collapsed .logo-text,
        .app.sidebar-collapsed .user-details,
        .app.sidebar-collapsed .nav-label { display: none; }
        .app.sidebar-collapsed .logo { padding-bottom: 12px; margin-bottom: 12px; }
        .app.sidebar-collapsed .logo-icon { width: 44px; height: 44px; }
        .app.sidebar-collapsed .user-info { padding: 8px; justify-content: center; }
        .app.sidebar-collapsed .user-avatar { width: 36px; height: 36px; font-size: 14px; }
        .app.sidebar-collapsed .nav-menu a { padding: 12px; justify-content: center; gap: 0; }
        .app.sidebar-collapsed .nav-icon { width: auto; margin: 0; font-size: 20px; }
        .app.sidebar-collapsed .sidebar-collapse-btn { justify-content: center; padding: 10px; }
        .app.sidebar-collapsed .sidebar-collapse-btn svg { transform: rotate(180deg); }
        .app.sidebar-collapsed .sidebar-footer { padding-top: 12px; }
        .app.sidebar-collapsed .sidebar-footer .profile-btn { padding: 10px !important; justify-content: center; border: none; background: rgba(59,130,246,0.15); }
        .app.sidebar-collapsed .sidebar-footer .logout-btn { padding: 10px; justify-content: center; }
        .app.sidebar-collapsed .sidebar-footer .nav-icon { font-size: 18px; width: auto; }

        .logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 20px;
            text-decoration: none;
            color: white;
            text-align: center;
        }

        .logo-icon {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            object-fit: contain;
        }

        .logo-text { font-size: 18px; font-weight: 700; }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .user-details { flex: 1; }
        .user-name { font-weight: 600; font-size: 14px; }
        .user-role { font-size: 12px; opacity: 0.8; }

        .nav-menu { list-style: none; display: flex; flex-direction: column; gap: 4px; flex: 1; order: 3; }

        .nav-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .nav-menu a:hover, .nav-menu a.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        .nav-icon { font-size: 18px; width: 24px; text-align: center; }

        .nav-menu .nav-sub-item a {
            padding-right: 38px;
            font-size: 13px;
            color: rgba(255,255,255,0.7);
        }
        .nav-menu .nav-sub-item a:hover, .nav-menu .nav-sub-item a.active {
            color: white;
        }
        .app.sidebar-collapsed .nav-sub-item { display: none; }

        .sidebar-footer {
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.2);
            order: 4;
        }

        .logout-btn {
            width: 100%;
            padding: 12px;
            background: rgba(220,38,38,0.2);
            border: 1px solid rgba(220,38,38,0.3);
            color: #fecaca;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.2s;
        }

        .logout-btn:hover { background: rgba(220,38,38,0.3); }

        /* Page Header */
        .page-header {
            background: var(--card);
            border-radius: var(--radius);
            padding: 20px 24px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        .page-header h1 { font-size: 22px; font-weight: 700; }
        .page-header p { color: var(--text-muted); font-size: 14px; margin-top: 4px; }

        .header-actions { display: flex; flex-wrap: wrap; gap: 10px; }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 500;
            border: 1px solid var(--border);
            background: white;
            color: var(--text);
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn:hover { background: var(--bg); border-color: var(--primary); }

        .btn-primary { background: var(--primary); color: white; border-color: var(--primary); }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-success { background: var(--success); color: white; border-color: var(--success); }
        .btn-danger { background: var(--danger); color: white; border-color: var(--danger); }
        .btn-warning { background: var(--warning); color: white; border-color: var(--warning); }
        .btn-sm { padding: 6px 12px; font-size: 13px; }

        /* Cards */
        .card {
            background: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            min-width: 0;
        }

        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title { font-size: 16px; font-weight: 600; }
        .card-body {
            padding: 20px;
            min-width: 0;
        }

        /* Tables */
        .table-container,
        .table-container-auto {
            overflow-x: auto !important;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            width: 100%;
            scrollbar-width: thin;
        }

        .table-container > table,
        .table-container-auto > table {
            margin-bottom: 0;
        }

        .table {
            width: 100%;
            min-width: max-content;
            border-collapse: collapse;
            font-size: 14px;
            white-space: nowrap !important;
        }

        .table th,
        .table td {
            white-space: nowrap !important;
            word-break: keep-all;
        }

        .table th {
            text-align: right;
            padding: 14px 16px;
            background: var(--bg);
            border-bottom: 2px solid var(--border);
            color: var(--text-muted);
            font-weight: 600;
            font-size: 13px;
        }

        .table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .table tr:hover { background: rgba(8,145,178,0.03); }

        .table-actions { display: flex; gap: 8px; }

        /* Forms */
        .form-group { margin-bottom: 20px; }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            font-family: 'Cairo', sans-serif;
            font-size: 14px;
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            background: white;
            color: var(--text);
            transition: all 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(8,145,178,0.1);
        }

        select.form-control { cursor: pointer; }
        textarea.form-control { min-height: 120px; resize: vertical; }

        .form-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .form-row-3 { grid-template-columns: repeat(3, 1fr); }

        .form-error { color: var(--danger); font-size: 13px; margin-top: 6px; }

        .form-text { color: var(--text-muted); font-size: 13px; margin-top: 6px; }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 99px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success { background: rgba(5,150,105,0.1); color: var(--success); }
        .badge-warning { background: rgba(245,158,11,0.1); color: var(--warning); }
        .badge-danger { background: rgba(220,38,38,0.1); color: var(--danger); }
        .badge-primary { background: rgba(8,145,178,0.1); color: var(--primary); }

        /* Alerts */
        .alert {
            padding: 16px 20px;
            border-radius: var(--radius-sm);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }

        .empty-state-icon { font-size: 64px; margin-bottom: 16px; opacity: 0.5; }
        .empty-state h3 { font-size: 18px; color: var(--text); margin-bottom: 8px; }

        /* Stats */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 20px; }

        .stat-card {
            background: var(--card);
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 12px;
        }

        .stat-icon.primary { background: rgba(8,145,178,0.1); }
        .stat-icon.success { background: rgba(5,150,105,0.1); }
        .stat-icon.warning { background: rgba(245,158,11,0.1); }
        .stat-icon.danger { background: rgba(220,38,38,0.1); }

        .stat-value { font-size: 28px; font-weight: 700; margin-bottom: 4px; }
        .stat-label { font-size: 14px; color: var(--text-muted); }

        /* Mobile */
        .menu-toggle {
            display: none;
            position: fixed;
            top: 16px;
            right: 16px;
            z-index: 200;
            width: 44px;
            height: 44px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 20px;
            cursor: pointer;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 90;
        }

        @media (max-width: 1200px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .form-row, .form-row-3 { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            .sidebar-collapse-btn {
                display: flex;
                position: sticky;
                top: 12px;
                margin-bottom: 12px;
                z-index: 120;
                background: rgba(14,116,144,0.9);
                border-color: rgba(255,255,255,0.28);
            }
            .app.sidebar-collapsed .sidebar-collapse-btn {
                justify-content: space-between;
                padding: 11px 12px;
            }
            .app.sidebar-collapsed .sidebar { width: 260px; padding: 20px; }
            .app.sidebar-collapsed .main { margin-right: 0; width: 100%; max-width: 100%; }
            .app.sidebar-collapsed .nav-label { display: inline; }
            .app.sidebar-collapsed .nav-menu a { justify-content: flex-start; gap: 10px; }
            .app.sidebar-collapsed .sidebar-footer .profile-btn { justify-content: flex-start !important; gap: 10px !important; }
            .app.sidebar-collapsed .sidebar-footer .logout-btn { justify-content: flex-start; gap: 10px; }
            .menu-toggle { display: flex; align-items: center; justify-content: center; }
            .menu-toggle[aria-expanded="true"] { display: none; }
            .sidebar { transform: translateX(100%); transition: transform 0.3s ease; }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay.open { display: block; }
            .main { margin-right: 0; padding: 70px 16px 16px; width: 100%; max-width: 100%; }
            .page-header { flex-direction: column; align-items: flex-start; gap: 8px; }
            .page-header h1 { font-size: 1.25rem !important; line-height: 1.3; }
            .stats-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
            .stats-grid > .stat-card { padding: 14px 12px; }
            .stat-card .stat-value { font-size: 1.05rem; word-break: break-word; line-height: 1.2; }
            .stat-card .stat-label { font-size: 0.7rem; }
            .header-actions { width: 100%; flex-wrap: wrap; }
            .header-actions .btn { flex: 1; min-width: 0; padding: 8px 12px; font-size: 0.85rem; }
            .filter-bar { flex-wrap: wrap; gap: 6px; }
            .filter-bar input, .filter-bar select { flex: 1 1 auto; min-width: 120px; }
            .form-actions { flex-direction: column; }
            .form-actions .btn { width: 100%; }
            .data-table { font-size: 0.78rem; }
            .data-table th, .data-table td { padding: 6px 4px; }
            .info-table th { width: 38% !important; font-size: 0.78rem; }
            .info-table td { font-size: 0.82rem; }
            .card { padding: 14px !important; }
            .form-card { padding: 14px !important; }
            /* Auto-wrap any standalone .table inside a .card so it scrolls horizontally */
            .card > table.table:not(.info-table) {
                display: block;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                white-space: nowrap;
            }
            .card > table.info-table { display: table; white-space: normal; }
            .card > table.info-table th,
            .card > table.info-table td { white-space: normal; word-break: break-word; }

            /* Responsive Tables - Enhanced */
            .table-container,
            .table-container-auto {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                margin: 0 -16px;
                padding: 0 16px 8px;
                position: relative;
            }
            .table-container::after {
                content: '← اسحب للمزيد →';
                display: block;
                text-align: center;
                font-size: 11px;
                color: var(--text-muted);
                padding: 8px;
                background: linear-gradient(to right, rgba(241,245,249,0.9), transparent 20%, transparent 80%, rgba(241,245,249,0.9));
            }
            .table { min-width: 620px; font-size: 12px; }
            .table-container > table.items-table,
            .table-container-auto > table.items-table {
                min-width: 620px;
            }
            .table-container > table.info-table,
            .table-container-auto > table.info-table {
                min-width: 100%;
            }
            .table-container::after,
            .table-container-auto::after {
                content: none;
            }
            .table th, .table td {
                padding: 10px 8px;
                white-space: nowrap;
            }
            .table th:first-child,
            .table td:first-child {
                position: static;
                box-shadow: none;
            }
            .table-actions {
                flex-wrap: nowrap;
                gap: 4px;
                justify-content: flex-start;
            }
            .table-actions .btn {
                padding: 4px 8px;
                font-size: 11px;
                white-space: nowrap;
            }
            .btn { padding: 8px 14px; font-size: 13px; }
            .btn-sm { padding: 4px 8px; font-size: 12px; }
            .card-body { padding: 16px; }
            .header-actions { width: 100%; flex-direction: column; }
            .header-actions .btn { width: 100%; justify-content: center; }
        }

        .text-center { text-align: center; }
        .text-muted { color: var(--text-muted); }
        .text-success { color: var(--success); }
        .text-danger { color: var(--danger); }
        .font-bold { font-weight: 700; }
        .mb-4 { margin-bottom: 16px; }
        .mt-4 { margin-top: 16px; }

        /* Card Footer (Pagination Container) */
        .card-footer {
            padding: 12px 20px;
            border-top: 1px solid var(--border);
            background: var(--bg);
            border-radius: 0 0 var(--radius) var(--radius);
        }

        .pagination-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .pagination-info {
            font-size: 13px;
            color: var(--text-muted);
            white-space: nowrap;
        }

        .pagination {
            display: flex;
            align-items: center;
            gap: 4px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .page-item .page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 10px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 500;
            color: var(--text);
            background: white;
            border: 1px solid var(--border);
            text-decoration: none;
            transition: all 0.2s;
            font-family: inherit;
        }

        .page-item .page-link:hover {
            background: var(--bg);
            border-color: var(--primary);
            color: var(--primary);
        }

        .page-item.active .page-link {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        .page-item.disabled .page-link {
            opacity: 0.4;
            pointer-events: none;
            background: var(--bg);
        }

        /* Filter Card Toggle */
        .filter-card { margin-bottom: 20px; }
        .filter-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 20px;
            cursor: pointer;
            user-select: none;
            border-bottom: 1px solid transparent;
            transition: border-color 0.2s;
        }
        .filter-toggle:hover { background: var(--bg); }
        .filter-card.open .filter-toggle { border-bottom-color: var(--border); }
        .filter-toggle-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .filter-toggle-icon {
            transition: transform 0.2s;
            font-size: 12px;
            color: var(--text-muted);
        }
        .filter-card.open .filter-toggle-icon { transform: rotate(180deg); }
        .filter-body {
            display: none;
            padding: 16px 20px;
        }
        .filter-card.open .filter-body { display: block; }
        .filter-card.has-filters .filter-toggle-title::after {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--primary);
            display: inline-block;
        }

        /* Filter Form Styles */
        .filter-form { margin-bottom: 0; }
        .filter-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            align-items: center;
        }
        .filter-actions {
            display: flex;
            gap: 8px;
            align-items: center;
            grid-column: 1 / -1;
        }
        .filter-row .btn { white-space: nowrap; }

        @media (max-width: 768px) {
            .pagination-wrapper { justify-content: center; }
            .filter-row { grid-template-columns: 1fr; }
        }
    </style>

    {{-- ============================================================ --}}
    {{-- EDITORIAL LUXURY THEME — AMMRK signature aesthetic            --}}
    {{-- ============================================================ --}}
    <style>
        :root {
            /* Refined palette: deep teal + antique gold on cream */
            --ink: #0f1721;
            --ink-soft: #2a3641;
            --ink-muted: #6b7785;
            --teal-deep: #0a4d5c;
            --teal-mid: #0e7490;
            --teal-light: #ecf6f8;
            --gold: #b89968;
            --gold-light: #d4b87f;
            --gold-pale: #f5ecd9;
            --cream: #faf7f0;
            --paper: #ffffff;
            --line: #e8e2d4;
            --shadow-soft: 0 2px 12px rgba(15, 23, 33, 0.04), 0 1px 2px rgba(15, 23, 33, 0.03);
            --shadow-lift: 0 12px 32px -12px rgba(15, 23, 33, 0.18), 0 4px 12px rgba(15, 23, 33, 0.06);
        }

        body {
            background: var(--cream);
            background-image:
                radial-gradient(ellipse 1200px 600px at 80% -10%, rgba(184,153,104,0.06), transparent 60%),
                radial-gradient(ellipse 800px 400px at 0% 100%, rgba(10,77,92,0.05), transparent 60%);
            color: var(--ink);
            font-feature-settings: "kern", "liga", "ss01";
            font-family: 'Amiri', 'Cairo', serif;
            font-size: 16px;
            line-height: 1.7;
        }

        /* Subtle paper grain */
        body::before {
            content: "";
            position: fixed; inset: 0;
            background-image:
                radial-gradient(circle at 20% 30%, rgba(184,153,104,0.025) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(10,77,92,0.02) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        /* Amiri for body, Reem Kufi for display headings (better for big titles) */
        h1, .page-header h1 {
            font-family: 'Amiri', 'Cairo', serif;
            font-weight: 700;
            letter-spacing: 0;
            color: var(--ink);
        }
        h2, h3 {
            font-family: 'Amiri', 'Cairo', serif;
            font-weight: 700;
            color: var(--ink);
            letter-spacing: 0;
        }
        /* Numerical / metric values stay in geometric font for legibility */
        .stat-value, .ic-num, .ic-amount-row strong, .kpi strong,
        code, [data-numeric], .badge {
            font-family: 'Reem Kufi', 'Cairo', sans-serif;
        }
        /* Tabular numbers everywhere */
        .table td, .data-table td, .info-table td {
            font-variant-numeric: tabular-nums;
        }

        /* Sidebar — refined editorial */
        .sidebar {
            background: linear-gradient(170deg, #0a4d5c 0%, #07343f 100%) !important;
            box-shadow: -1px 0 0 rgba(184,153,104,0.15), inset 1px 0 0 rgba(255,255,255,0.04);
        }
        .sidebar::before {
            content: "";
            position: absolute;
            top: 0; right: 0; width: 3px; height: 80px;
            background: linear-gradient(180deg, var(--gold) 0%, transparent 100%);
            opacity: 0.4;
        }
        .sidebar .logo {
            border-bottom: 1px solid rgba(184,153,104,0.18) !important;
            padding-bottom: 18px !important;
        }
        .sidebar .logo-text {
            font-family: 'Reem Kufi', 'Cairo', sans-serif;
            font-weight: 700;
            letter-spacing: 0.02em;
        }
        .sidebar .nav-menu a {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
            border-radius: 8px !important;
        }
        .sidebar .nav-menu a:hover {
            background: rgba(184,153,104,0.12) !important;
            color: var(--gold-light) !important;
            transform: translateX(-2px);
        }
        .sidebar .nav-menu a.active,
        .sidebar .nav-menu li.active a {
            background: linear-gradient(90deg, rgba(184,153,104,0.18), rgba(184,153,104,0.05)) !important;
            border-right: 2px solid var(--gold);
            color: white !important;
        }

        /* Main content paper */
        .main {
            position: relative;
            z-index: 1;
        }

        /* Page headers with editorial flair */
        .page-header {
            position: relative;
            padding-bottom: 20px !important;
            margin-bottom: 24px !important;
            border-bottom: 1px solid var(--line) !important;
        }
        .page-header::after {
            content: "";
            position: absolute;
            bottom: -1px;
            right: 0;
            width: 60px;
            height: 2px;
            background: var(--gold);
        }
        .page-header h1 {
            font-size: 1.75rem !important;
            margin-bottom: 4px !important;
        }
        .page-header p {
            color: var(--ink-muted);
            font-size: 0.9rem;
        }

        /* Cards — generous padding + paper feel + depth */
        .card,
        .stat-card,
        .form-card,
        .invoice-card,
        .kpi,
        .filter-card,
        .filters-card,
        .item-card {
            padding: 28px 32px !important;
            background: var(--paper) !important;
            border: 1px solid var(--line) !important;
            border-radius: 14px !important;
            box-shadow: var(--shadow-soft) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card:hover,
        .stat-card:hover {
            box-shadow: var(--shadow-lift) !important;
        }
        /* Card heading spacing */
        .card > h3:first-child,
        .form-card > h3:first-child {
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--line);
            position: relative;
        }
        .card > h3:first-child::after,
        .form-card > h3:first-child::after {
            content: "";
            position: absolute;
            bottom: -1px; right: 0;
            width: 40px; height: 2px;
            background: var(--gold);
        }
        /* Vertical breathing room between stacked cards */
        .card + .card,
        .form-card + .card,
        .card + .form-card { margin-top: 20px; }
        /* Mobile padding adjusted */
        @media (max-width: 768px) {
            .card, .stat-card, .form-card, .invoice-card, .kpi, .item-card,
            .filter-card, .filters-card {
                padding: 18px 20px !important;
            }
        }

        /* Stat card — editorial number layout */
        .stat-card {
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: "";
            position: absolute;
            top: 0; right: 0;
            width: 100%; height: 3px;
            background: linear-gradient(90deg, var(--gold) 0%, transparent 50%);
            opacity: 0.6;
        }
        .stat-card .stat-value {
            font-family: 'Reem Kufi', 'Cairo', sans-serif;
            font-weight: 700;
            color: var(--teal-deep);
            letter-spacing: -0.02em;
        }
        .stat-card .stat-label {
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 0.72rem !important;
            color: var(--ink-muted) !important;
            font-weight: 600;
        }

        /* Buttons — refined */
        .btn-primary {
            background: linear-gradient(135deg, var(--teal-deep) 0%, var(--teal-mid) 100%) !important;
            border: none !important;
            color: white !important;
            font-weight: 600 !important;
            letter-spacing: 0.01em;
            box-shadow: 0 2px 8px rgba(10,77,92,0.18) !important;
            transition: all 0.25s ease !important;
            position: relative;
            overflow: hidden;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #093d4a 0%, #0c6280 100%) !important;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(10,77,92,0.28) !important;
        }
        .btn-primary::after {
            content: "";
            position: absolute;
            bottom: 0; right: 0;
            width: 30px; height: 30px;
            background: radial-gradient(circle, rgba(184,153,104,0.3), transparent 70%);
            opacity: 0;
            transition: opacity 0.3s;
        }
        .btn-primary:hover::after { opacity: 1; }
        .btn-secondary {
            background: var(--paper) !important;
            border: 1px solid var(--line) !important;
            color: var(--ink) !important;
            font-weight: 500 !important;
        }
        .btn-secondary:hover {
            background: var(--cream) !important;
            border-color: var(--gold-light) !important;
            color: var(--teal-deep) !important;
        }

        /* Tables — editorial typography */
        .table thead th,
        .data-table thead th {
            background: var(--cream) !important;
            color: var(--ink-soft) !important;
            font-family: 'Reem Kufi', 'Cairo', sans-serif !important;
            font-weight: 600 !important;
            font-size: 0.78rem !important;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 14px 12px !important;
            border-bottom: 1px solid var(--line) !important;
        }
        .table tbody tr,
        .data-table tbody tr {
            transition: background 0.15s ease;
        }
        .table tbody tr:hover,
        .data-table tbody tr:hover {
            background: var(--gold-pale) !important;
        }
        .table tbody td,
        .data-table tbody td {
            border-bottom: 1px solid var(--line) !important;
            padding: 14px 12px !important;
            color: var(--ink-soft);
        }

        /* Forms — refined inputs */
        input.form-control,
        select.form-control,
        textarea.form-control,
        .form-row input[type="text"],
        .form-row input[type="email"],
        .form-row input[type="number"],
        .form-row input[type="date"],
        .form-row select,
        .form-row textarea {
            border: 1px solid var(--line) !important;
            background: var(--paper) !important;
            border-radius: 10px !important;
            font-family: 'Cairo', sans-serif !important;
            transition: all 0.2s ease !important;
        }
        input.form-control:focus,
        select.form-control:focus,
        textarea.form-control:focus {
            border-color: var(--gold) !important;
            background: var(--paper) !important;
            box-shadow: 0 0 0 3px rgba(184,153,104,0.12) !important;
            outline: none;
        }
        label, .form-row label {
            color: var(--ink-soft) !important;
            font-weight: 600 !important;
            font-size: 0.82rem !important;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        /* Badges — pill style */
        .badge {
            font-family: 'Cairo', sans-serif !important;
            font-weight: 600 !important;
            font-size: 0.72rem !important;
            padding: 4px 10px !important;
            border-radius: 999px !important;
            letter-spacing: 0.02em;
        }
        .badge-success { background: #ecfdf5 !important; color: #047857 !important; border: 1px solid #a7f3d0; }
        .badge-warning { background: #fffbeb !important; color: #b45309 !important; border: 1px solid #fde68a; }
        .badge-danger { background: #fef2f2 !important; color: #b91c1c !important; border: 1px solid #fecaca; }
        .badge-primary { background: var(--teal-light) !important; color: var(--teal-deep) !important; border: 1px solid #b3e5ed; }
        .badge-info { background: #eff6ff !important; color: #1d4ed8 !important; border: 1px solid #bfdbfe; }
        .badge-secondary { background: #f8fafc !important; color: #64748b !important; border: 1px solid var(--line); }

        /* Alerts — subtle elevated */
        .alert {
            border-radius: 12px !important;
            border-width: 1px !important;
            border-style: solid !important;
            padding: 12px 16px !important;
            font-weight: 500;
        }
        .alert-success {
            background: #ecfdf5 !important;
            border-color: #a7f3d0 !important;
            color: #065f46 !important;
        }
        .alert-danger {
            background: #fef2f2 !important;
            border-color: #fecaca !important;
            color: #991b1b !important;
        }

        /* Scrollbar refined */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb {
            background: var(--line);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover { background: var(--gold-light); }

        /* Selection */
        ::selection {
            background: var(--gold-pale);
            color: var(--ink);
        }

        /* Mobile menu toggle — refined */
        .menu-toggle {
            background: linear-gradient(135deg, var(--teal-deep), var(--teal-mid)) !important;
            box-shadow: 0 4px 12px rgba(10,77,92,0.3) !important;
            border-radius: 12px !important;
        }

        /* Page-load fade */
        @keyframes fade-up {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .main > * { animation: fade-up 0.4s cubic-bezier(0.4, 0, 0.2, 1) backwards; }
        .main > *:nth-child(1) { animation-delay: 0ms; }
        .main > *:nth-child(2) { animation-delay: 60ms; }
        .main > *:nth-child(3) { animation-delay: 120ms; }
        .main > *:nth-child(4) { animation-delay: 180ms; }
        .main > *:nth-child(5) { animation-delay: 240ms; }
    </style>

    @stack('styles')
</head>
<body>
    <button class="menu-toggle" aria-label="فتح القائمة" aria-expanded="false" onclick="toggleSidebar()">☰</button>
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <div class="app">
        <aside class="sidebar" id="sidebar">
            @php
                $homeRoute = route('dashboard');
            @endphp
            <a href="{{ $homeRoute }}" class="logo">
                @if($__sidebarLogo)
                    <img src="{{ asset('storage/' . $__sidebarLogo) }}" alt="{{ $__sidebarName }}" class="logo-icon">
                @else
                    <img src="{{ asset('logo.png') }}" alt="{{ $__sidebarName }}" class="logo-icon">
                @endif
                <div class="logo-text">{{ $__sidebarName }}</div>
            </a>

            <div class="user-info">
                <div class="user-avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</div>
                <div class="user-details">
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="user-role">{{ auth()->user()->email }}</div>
                </div>
            </div>

            <ul class="nav-menu">
                {{-- NOTE: Phase 1 cleanup — old product/sale/purchase/supplier/sales-rep/warehouse menu items removed.
                     New service-CRM nav (services, quotes, invoices) will be added in Phases 3–5. --}}
                @php
                    $isEmployeeOnly = auth()->user()->isEmployee() && !auth()->user()->isSuperAdmin();
                @endphp

                <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" data-tooltip="لوحة التحكم"><span class="nav-icon">📊</span><span class="nav-label">لوحة التحكم</span></a></li>
                @if(feature_enabled('customers'))
                <li><a href="{{ route('admin.customers.index') }}" class="{{ request()->routeIs('admin.customers.*') ? 'active' : '' }}" data-tooltip="{{ feature_name('customers', 'العملاء') }}"><span class="nav-icon">{{ feature_icon('customers', '👥') }}</span><span class="nav-label">{{ feature_name('customers', 'العملاء') }}</span></a></li>
                @endif
                @if(!$isEmployeeOnly)
                    <li><a href="{{ route('branches.index') }}" class="{{ request()->routeIs('branches.*') ? 'active' : '' }}" data-tooltip="الفروع"><span class="nav-icon">🏢</span><span class="nav-label">الفروع</span></a></li>
                    @if(feature_enabled('employees'))
                    <li><a href="{{ route('employees.index') }}" class="{{ request()->routeIs('employees.*') ? 'active' : '' }}" data-tooltip="{{ feature_name('employees', 'الموظفين') }}"><span class="nav-icon">{{ feature_icon('employees', '👨‍💻') }}</span><span class="nav-label">{{ feature_name('employees', 'الموظفين') }}</span></a></li>
                    <li><a href="{{ route('employee-transactions.index') }}" class="{{ request()->routeIs('employee-transactions.*') ? 'active' : '' }}" data-tooltip="المرتبات والسلف"><span class="nav-icon">💰</span><span class="nav-label">المرتبات والسلف</span></a></li>
                    @endif
                    <li><a href="{{ route('partners.index') }}" class="{{ request()->routeIs('partners.*') ? 'active' : '' }}" data-tooltip="الشركاء"><span class="nav-icon">🤝</span><span class="nav-label">الشركاء</span></a></li>
                    <li><a href="{{ route('partner-transactions.index') }}" class="{{ request()->routeIs('partner-transactions.*') ? 'active' : '' }}" data-tooltip="معاملات الشركاء"><span class="nav-icon">📊</span><span class="nav-label">معاملات الشركاء</span></a></li>
                    <li><a href="{{ route('profit-distribution.index') }}" class="{{ request()->routeIs('profit-distribution.*') ? 'active' : '' }}" data-tooltip="توزيع الأرباح"><span class="nav-icon">💹</span><span class="nav-label">توزيع الأرباح</span></a></li>
                @endif
                @if(feature_enabled('expenses'))
                <li><a href="{{ route('expenses.index') }}" class="{{ request()->routeIs('expenses.*') ? 'active' : '' }}" data-tooltip="{{ feature_name('expenses', 'المصروفات') }}"><span class="nav-icon">{{ feature_icon('expenses', '💸') }}</span><span class="nav-label">{{ feature_name('expenses', 'المصروفات') }}</span></a></li>
                <li><a href="{{ route('treasury.index') }}" class="{{ request()->routeIs('treasury.*') ? 'active' : '' }}" data-tooltip="الخزنة"><span class="nav-icon">🏦</span><span class="nav-label">الخزنة</span></a></li>
                @endif
                @if(feature_enabled('reports'))
                <li><a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}" data-tooltip="{{ feature_name('reports', 'التقارير') }}"><span class="nav-icon">{{ feature_icon('reports', '📈') }}</span><span class="nav-label">{{ feature_name('reports', 'التقارير') }}</span></a></li>
                @endif
                <li><a href="{{ route('zatca.dashboard') }}" class="{{ request()->routeIs('zatca.*') ? 'active' : '' }}" data-tooltip="ZATCA"><span class="nav-icon">📋</span><span class="nav-label">الفوترة الإلكترونية</span></a></li>
                @if(feature_enabled('settings'))
                <li><a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.index') ? 'active' : '' }}" data-tooltip="{{ feature_name('settings', 'الإعدادات') }}"><span class="nav-icon">{{ feature_icon('settings', '⚙️') }}</span><span class="nav-label">{{ feature_name('settings', 'الإعدادات') }}</span></a></li>
                <li><a href="{{ route('settings.users') }}" class="{{ request()->routeIs('settings.users*') ? 'active' : '' }}" data-tooltip="المستخدمين"><span class="nav-icon">👤</span><span class="nav-label">المستخدمين</span></a></li>
                @if(auth()->user() && auth()->user()->isSuperAdmin())
                <li><a href="{{ route('settings.roles') }}" class="{{ request()->routeIs('settings.roles*') ? 'active' : '' }}" data-tooltip="الأدوار والصلاحيات"><span class="nav-icon">🔐</span><span class="nav-label">الأدوار والصلاحيات</span></a></li>
                @endif
                @endif
                <li><a href="{{ route('portfolio.index') }}" class="{{ request()->routeIs('portfolio.*') ? 'active' : '' }}" data-tooltip="معرض الأعمال"><span class="nav-icon">📸</span><span class="nav-label">معرض الأعمال</span></a></li>
                <li><a href="{{ route('ux-analysis.index') }}" class="{{ request()->routeIs('ux-analysis.*') ? 'active' : '' }}" data-tooltip="تحليل تجربة المستخدم"><span class="nav-icon">🎨</span><span class="nav-label">تحليل تجربة المستخدم</span></a></li>
                @if(auth()->user() && auth()->user()->isSuperAdmin())
                <li><a href="{{ route('features.index') }}" class="{{ request()->routeIs('features.*') ? 'active' : '' }}" style="background: rgba(245,158,11,0.2);" data-tooltip="إدارة المميزات"><span class="nav-icon">🔧</span><span class="nav-label">إدارة المميزات</span></a></li>
                @endif
            </ul>

            <button class="sidebar-collapse-btn" id="sidebarToggle" title="طي القائمة" data-tooltip="طي القائمة">
                <span class="nav-label">طي القائمة</span>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>

            <div class="sidebar-footer">
                <a href="{{ route('profile.index') }}" class="profile-btn" data-tooltip="الملف الشخصي" style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; margin-bottom: 10px; background: rgba(59,130,246,0.2); border: 1px solid rgba(59,130,246,0.3); border-radius: 8px; color: #93c5fd; text-decoration: none;">
                    <span class="nav-icon">👤</span><span class="nav-label">{{ auth()->user()->name ?? 'الملف الشخصي' }}</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn" data-tooltip="تسجيل الخروج" style="display: flex; align-items: center; gap: 10px;">
                        <span class="nav-icon">🚪</span><span class="nav-label">تسجيل الخروج</span>
                    </button>
                </form>
            </div>
        </aside>

        <main class="main">
            @if(session('success'))
                <div class="alert alert-success">✅ {{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">❌ {{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        function setMobileSidebarOpen(isOpen) {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            const menuToggle = document.querySelector('.menu-toggle');

            if (!sidebar || !overlay || !menuToggle) return;

            sidebar.classList.toggle('open', isOpen);
            overlay.classList.toggle('open', isOpen);
            menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            if (!sidebar) return;
            setMobileSidebarOpen(!sidebar.classList.contains('open'));
        }

        // Sidebar collapse (desktop)
        (function() {
            const app = document.querySelector('.app');
            const toggle = document.getElementById('sidebarToggle');
            const isMobile = () => window.matchMedia('(max-width: 768px)').matches;

            const applySidebarState = () => {
                if (isMobile()) {
                    app.classList.remove('sidebar-collapsed');
                } else if (localStorage.getItem('sidebar-collapsed') === '1') {
                    app.classList.add('sidebar-collapsed');
                } else {
                    app.classList.remove('sidebar-collapsed');
                }
            };

            const updateToggleLabel = () => {
                const label = toggle.querySelector('.nav-label');
                if (!label) return;

                if (isMobile()) {
                    label.textContent = 'اغلاق القائمة';
                    toggle.setAttribute('title', 'اغلاق القائمة');
                    toggle.setAttribute('data-tooltip', 'اغلاق القائمة');
                } else {
                    label.textContent = 'طي القائمة';
                    toggle.setAttribute('title', 'طي القائمة');
                    toggle.setAttribute('data-tooltip', 'طي القائمة');
                }
            };

            applySidebarState();
            updateToggleLabel();
            window.addEventListener('resize', function() {
                applySidebarState();
                updateToggleLabel();
            });

            toggle.addEventListener('click', function() {
                if (isMobile()) {
                    setMobileSidebarOpen(false);
                    return;
                }
                app.classList.toggle('sidebar-collapsed');
                localStorage.setItem('sidebar-collapsed', app.classList.contains('sidebar-collapsed') ? '1' : '0');
            });

            // Tooltip for collapsed sidebar
            var tip = document.createElement('div');
            tip.className = 'sidebar-tooltip';
            document.body.appendChild(tip);
            var hideTimer;
            document.getElementById('sidebar').addEventListener('mouseover', function(e) {
                if (!app.classList.contains('sidebar-collapsed')) return;
                var el = e.target.closest('[data-tooltip]');
                if (!el) return;
                clearTimeout(hideTimer);
                var rect = el.getBoundingClientRect();
                tip.textContent = el.getAttribute('data-tooltip');
                tip.style.top = (rect.top + rect.height / 2) + 'px';
                tip.style.left = (rect.left - 8) + 'px';
                tip.style.transform = 'translate(-100%, -50%)';
                tip.classList.add('visible');
            });
            document.getElementById('sidebar').addEventListener('mouseout', function(e) {
                var el = e.target.closest('[data-tooltip]');
                if (!el) return;
                hideTimer = setTimeout(function() { tip.classList.remove('visible'); }, 100);
            });
        })();
    </script>
    <!-- jQuery + Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Select2 auto-init for filter selects
            $('.filter-form select').each(function() {
                $(this).select2({
                    dir: 'rtl',
                    allowClear: true,
                    placeholder: $(this).find('option:first').text(),
                    minimumResultsForSearch: 5
                });
            });

            // Filter toggle
            $('.filter-toggle').on('click', function() {
                $(this).closest('.filter-card').toggleClass('open');
            });

            // Auto-open if filters are active
            $('.filter-card').each(function() {
                var hasFilters = false;
                $(this).find('input, select').each(function() {
                    if ($(this).val() && $(this).val() !== '') hasFilters = true;
                });
                if (hasFilters) {
                    $(this).addClass('open has-filters');
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
