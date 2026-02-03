<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'لوحة التحكم') - Rogence System</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --primary: #0891b2;
            --primary-dark: #0e7490;
            --primary-light: #06b6d4;
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

        body {
            font-family: 'Cairo', sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
        }

        .app { display: flex; min-height: 100vh; }

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
        }

        .main {
            flex: 1;
            margin-right: 260px;
            padding: 20px;
            min-height: 100vh;
            width: calc(100% - 260px);
            max-width: calc(100% - 260px);
            overflow-x: hidden;
        }

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

        .nav-menu { list-style: none; display: flex; flex-direction: column; gap: 4px; flex: 1; }

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

        .sidebar-footer {
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.2);
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
        }

        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title { font-size: 16px; font-weight: 600; }
        .card-body { padding: 20px; }

        /* Tables */
        .table-container {
            overflow-x: auto !important;
            overflow-y: visible;
            -webkit-overflow-scrolling: touch;
            width: 100%;
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
            .menu-toggle { display: flex; align-items: center; justify-content: center; }
            .sidebar { transform: translateX(100%); transition: transform 0.3s ease; }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay.open { display: block; }
            .main { margin-right: 0; padding: 70px 16px 16px; width: 100%; max-width: 100%; }
            .page-header { flex-direction: column; align-items: flex-start; }
            .stats-grid { grid-template-columns: 1fr; }

            /* Responsive Tables - Enhanced */
            .table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                margin: 0 -16px;
                padding: 0 16px;
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
            .table { min-width: 700px; font-size: 12px; }
            .table th, .table td {
                padding: 10px 8px;
                white-space: nowrap;
            }
            .table th:first-child, .table td:first-child {
                position: sticky;
                right: 0;
                background: var(--card);
                z-index: 1;
                box-shadow: -2px 0 5px rgba(0,0,0,0.05);
            }
            .table tr:hover td:first-child {
                background: rgba(8,145,178,0.03);
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
    </style>
    @stack('styles')
</head>
<body>
    <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <div class="app">
        <aside class="sidebar" id="sidebar">
            @php
                if (auth()->user()->isSalesRep() && !auth()->user()->isSuperAdmin()) {
                    $homeRoute = route('sales-rep.dashboard');
                } else {
                    $homeRoute = route('dashboard');
                }
            @endphp
            <a href="{{ $homeRoute }}" class="logo">
                <img src="{{ asset('logo.png') }}" alt="Rogence System" class="logo-icon">
                <div class="logo-text">Rogence System</div>
            </a>

            <div class="user-info">
                <div class="user-avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</div>
                <div class="user-details">
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="user-role">{{ auth()->user()->email }}</div>
                </div>
            </div>

            <ul class="nav-menu">
                @php
                    $isSalesRepOnly = auth()->user()->isSalesRep() && !auth()->user()->isSuperAdmin();
                    $isEmployeeOnly = auth()->user()->isEmployee() && !auth()->user()->isSuperAdmin() && !auth()->user()->isSalesRep();
                @endphp

                @if($isSalesRepOnly)
                    {{-- Sales Rep Menu --}}
                    <li><a href="{{ route('sales-rep.dashboard') }}" class="{{ request()->routeIs('sales-rep.dashboard') ? 'active' : '' }}"><span class="nav-icon">📊</span> لوحة التحكم</a></li>
                    <li><a href="{{ route('sales-rep.customers') }}" class="{{ request()->routeIs('sales-rep.customers') ? 'active' : '' }}"><span class="nav-icon">👥</span> عملائي</a></li>
                    <li><a href="{{ route('sales-rep.sales') }}" class="{{ request()->routeIs('sales-rep.sales') ? 'active' : '' }}"><span class="nav-icon">💰</span> مبيعاتي</a></li>
                    <li><a href="{{ route('sales-rep.collections') }}" class="{{ request()->routeIs('sales-rep.collections') ? 'active' : '' }}"><span class="nav-icon">💵</span> تحصيلاتي</a></li>
                    <li><a href="{{ route('sales-rep.treasury') }}" class="{{ request()->routeIs('sales-rep.treasury') ? 'active' : '' }}"><span class="nav-icon">🏦</span> خزينتي</a></li>
                    <li><a href="{{ route('sales-rep.expenses') }}" class="{{ request()->routeIs('sales-rep.expenses*') ? 'active' : '' }}"><span class="nav-icon">🧾</span> مصروفاتي</a></li>
                    <li><a href="{{ route('sales-rep.inventory') }}" class="{{ request()->routeIs('sales-rep.inventory') ? 'active' : '' }}"><span class="nav-icon">📦</span> مخزني</a></li>
                    <li><a href="{{ route('sales-rep.reports') }}" class="{{ request()->routeIs('sales-rep.reports') ? 'active' : '' }}"><span class="nav-icon">📈</span> تقاريري</a></li>
                @elseif($isEmployeeOnly)
                    {{-- Employee Menu - Operational Access --}}
                    <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><span class="nav-icon">📊</span> لوحة التحكم</a></li>
                    @if(feature_enabled('sales'))
                    <li><a href="{{ route('sales.index') }}" class="{{ request()->routeIs('sales.*') ? 'active' : '' }}"><span class="nav-icon">{{ feature_icon('sales', '💰') }}</span> {{ feature_name('sales', 'المبيعات') }}</a></li>
                    @endif
                    @if(feature_enabled('purchases'))
                    <li><a href="{{ route('purchases.index') }}" class="{{ request()->routeIs('purchases.*') ? 'active' : '' }}"><span class="nav-icon">{{ feature_icon('purchases', '🛒') }}</span> {{ feature_name('purchases', 'المشتريات') }}</a></li>
                    @endif
                    @if(feature_enabled('products'))
                    <li><a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}"><span class="nav-icon">{{ feature_icon('products', '📦') }}</span> {{ feature_name('products', 'الأصناف') }}</a></li>
                    @endif
                    @if(feature_enabled('categories'))
                    <li><a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}"><span class="nav-icon">{{ feature_icon('categories', '🏷️') }}</span> {{ feature_name('categories', 'الأقسام') }}</a></li>
                    @endif
                    @if(feature_enabled('warehouses'))
                    <li><a href="{{ route('warehouses.index') }}" class="{{ request()->routeIs('warehouses.*') ? 'active' : '' }}"><span class="nav-icon">{{ feature_icon('warehouses', '🏭') }}</span> {{ feature_name('warehouses', 'المخازن') }}</a></li>
                    @endif
                    @if(feature_enabled('customers'))
                    <li><a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}"><span class="nav-icon">{{ feature_icon('customers', '👥') }}</span> {{ feature_name('customers', 'العملاء') }}</a></li>
                    @endif
                    @if(feature_enabled('suppliers'))
                    <li><a href="{{ route('suppliers.index') }}" class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}"><span class="nav-icon">{{ feature_icon('suppliers', '🏢') }}</span> {{ feature_name('suppliers', 'الموردين') }}</a></li>
                    @endif
                    @if(feature_enabled('reports'))
                    <li><a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}"><span class="nav-icon">{{ feature_icon('reports', '📈') }}</span> {{ feature_name('reports', 'التقارير') }}</a></li>
                    @endif
                @else
                    {{-- Admin/Full Menu --}}
                    <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><span class="nav-icon">📊</span> لوحة التحكم</a></li>
                    @if(feature_enabled('sales'))
                    <li><a href="{{ route('sales.index') }}" class="{{ request()->routeIs('sales.*') ? 'active' : '' }}"><span class="nav-icon">{{ feature_icon('sales', '💰') }}</span> {{ feature_name('sales', 'المبيعات') }}</a></li>
                    @endif
                    @if(feature_enabled('purchases'))
                    <li><a href="{{ route('purchases.index') }}" class="{{ request()->routeIs('purchases.*') ? 'active' : '' }}"><span class="nav-icon">{{ feature_icon('purchases', '🛒') }}</span> {{ feature_name('purchases', 'المشتريات') }}</a></li>
                    @endif
                    @if(feature_enabled('suppliers'))
                    <li><a href="{{ route('suppliers.index') }}" class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}"><span class="nav-icon">{{ feature_icon('suppliers', '🏢') }}</span> {{ feature_name('suppliers', 'الموردين') }}</a></li>
                    @endif
                    @if(feature_enabled('invoices'))
                    <li><a href="{{ route('invoices.index') }}" class="{{ request()->routeIs('invoices.*') ? 'active' : '' }}"><span class="nav-icon">{{ feature_icon('invoices', '📄') }}</span> {{ feature_name('invoices', 'الفواتير') }}</a></li>
                    @endif
                    @if(feature_enabled('warehouses'))
                    <li><a href="{{ route('warehouses.index') }}" class="{{ request()->routeIs('warehouses.*') ? 'active' : '' }}"><span class="nav-icon">{{ feature_icon('warehouses', '🏭') }}</span> {{ feature_name('warehouses', 'المخازن') }}</a></li>
                    @endif
                    @if(feature_enabled('products'))
                    <li><a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}"><span class="nav-icon">{{ feature_icon('products', '📦') }}</span> {{ feature_name('products', 'الأصناف') }}</a></li>
                    @endif
                    @if(feature_enabled('categories'))
                    <li><a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}"><span class="nav-icon">{{ feature_icon('categories', '🏷️') }}</span> {{ feature_name('categories', 'الأقسام') }}</a></li>
                    @endif
                    @if(feature_enabled('customers'))
                    <li><a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}"><span class="nav-icon">{{ feature_icon('customers', '👥') }}</span> {{ feature_name('customers', 'العملاء') }}</a></li>
                    @endif
                    @if(feature_enabled('sales_reps'))
                    <li><a href="{{ route('sales-reps.index') }}" class="{{ request()->routeIs('sales-reps.*') ? 'active' : '' }}"><span class="nav-icon">{{ feature_icon('sales_reps', '👔') }}</span> {{ feature_name('sales_reps', 'المندوبين') }}</a></li>
                    <li><a href="{{ route('admin.sales-rep-treasury.index') }}" class="{{ request()->routeIs('admin.sales-rep-treasury.*') ? 'active' : '' }}"><span class="nav-icon">💰</span> خزينات المندوبين</a></li>
                    <li><a href="{{ route('admin.sales-rep-inventory.index') }}" class="{{ request()->routeIs('admin.sales-rep-inventory.*') ? 'active' : '' }}"><span class="nav-icon">📦</span> مخازن المندوبين</a></li>
                    @endif
                    @if(feature_enabled('employees'))
                    <li><a href="{{ route('employees.index') }}" class="{{ request()->routeIs('employees.*') ? 'active' : '' }}"><span class="nav-icon">{{ feature_icon('employees', '👨‍💻') }}</span> {{ feature_name('employees', 'الموظفين') }}</a></li>
                    <li><a href="{{ route('employee-transactions.index') }}" class="{{ request()->routeIs('employee-transactions.*') ? 'active' : '' }}"><span class="nav-icon">💰</span> المرتبات والسلف</a></li>
                    @endif
                    <li><a href="{{ route('partners.index') }}" class="{{ request()->routeIs('partners.*') ? 'active' : '' }}"><span class="nav-icon">🤝</span> الشركاء</a></li>
                    <li><a href="{{ route('partner-transactions.index') }}" class="{{ request()->routeIs('partner-transactions.*') ? 'active' : '' }}"><span class="nav-icon">📊</span> معاملات الشركاء</a></li>
                    <li><a href="{{ route('profit-distribution.index') }}" class="{{ request()->routeIs('profit-distribution.*') ? 'active' : '' }}"><span class="nav-icon">💹</span> توزيع الأرباح</a></li>
                    @if(feature_enabled('expenses'))
                    <li><a href="{{ route('expenses.index') }}" class="{{ request()->routeIs('expenses.*') ? 'active' : '' }}"><span class="nav-icon">{{ feature_icon('expenses', '💸') }}</span> {{ feature_name('expenses', 'المصروفات') }}</a></li>
                    <li><a href="{{ route('treasury.index') }}" class="{{ request()->routeIs('treasury.*') ? 'active' : '' }}"><span class="nav-icon">🏦</span> الخزنة</a></li>
                    @endif
                    @if(feature_enabled('reports'))
                    <li><a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}"><span class="nav-icon">{{ feature_icon('reports', '📈') }}</span> {{ feature_name('reports', 'التقارير') }}</a></li>
                    @endif
                    @if(feature_enabled('settings'))
                    <li><a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.index') ? 'active' : '' }}"><span class="nav-icon">{{ feature_icon('settings', '⚙️') }}</span> {{ feature_name('settings', 'الإعدادات') }}</a></li>
                    <li><a href="{{ route('settings.users') }}" class="{{ request()->routeIs('settings.users*') ? 'active' : '' }}"><span class="nav-icon">👤</span> المستخدمين</a></li>
                    @if(auth()->user() && auth()->user()->isSuperAdmin())
                    <li><a href="{{ route('settings.roles') }}" class="{{ request()->routeIs('settings.roles*') ? 'active' : '' }}"><span class="nav-icon">🔐</span> الأدوار والصلاحيات</a></li>
                    @endif
                    @endif
                    <li><a href="{{ route('portfolio.index') }}" class="{{ request()->routeIs('portfolio.*') ? 'active' : '' }}"><span class="nav-icon">📸</span> معرض الأعمال</a></li>
                    <li><a href="{{ route('ux-analysis.index') }}" class="{{ request()->routeIs('ux-analysis.*') ? 'active' : '' }}"><span class="nav-icon">🎨</span> تحليل تجربة المستخدم</a></li>
                    @if(auth()->user() && auth()->user()->isSuperAdmin())
                    <li><a href="{{ route('features.index') }}" class="{{ request()->routeIs('features.*') ? 'active' : '' }}" style="background: rgba(245,158,11,0.2);"><span class="nav-icon">🔧</span> إدارة المميزات</a></li>
                    @endif
                @endif
            </ul>

            <div class="sidebar-footer">
                <a href="{{ route('profile.index') }}" class="profile-btn" style="display: block; text-align: center; padding: 10px; margin-bottom: 10px; background: rgba(59,130,246,0.2); border: 1px solid rgba(59,130,246,0.3); border-radius: 8px; color: #93c5fd; text-decoration: none;">
                    👤 {{ auth()->user()->name ?? 'الملف الشخصي' }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn">🚪 تسجيل الخروج</button>
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
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.querySelector('.sidebar-overlay').classList.toggle('open');
        }
    </script>
    @stack('scripts')
</body>
</html>
