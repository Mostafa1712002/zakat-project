@extends('layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
<div class="page-header">
    <div>
        <h1>📊 ملخص الأداء اليومي</h1>
        <p>نظرة سريعة على الحركة عبر الفروع والمخازن</p>
    </div>
    <div class="header-actions">
        <button class="btn">الأسبوع الحالي</button>
        <button class="btn">كل الفروع</button>
        <a href="{{ route('sales.create') }}" class="btn btn-primary">+ إضافة فاتورة</a>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">💰</div>
        <div class="stat-value">{{ number_format($stats['total_sales'] ?? 128450) }} ج.م</div>
        <div class="stat-label">مبيعات اليوم</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning">🛒</div>
        <div class="stat-value">{{ number_format($stats['total_purchases'] ?? 52800) }} ج.م</div>
        <div class="stat-label">مشتريات اليوم</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon danger">⚠️</div>
        <div class="stat-value">{{ $stats['low_stock_count'] ?? 17 }} صنف</div>
        <div class="stat-label">تنبيه مخزون</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success">📈</div>
        <div class="stat-value">{{ number_format($stats['monthly_profit'] ?? 0) }} ج.م</div>
        <div class="stat-label">صافي الربح (30 يوم)</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon primary">💵</div>
        <div class="stat-value">{{ number_format($stats['monthly_cash_balance'] ?? 0) }} ج.م</div>
        <div class="stat-label">الرصيد النقدي (30 يوم)</div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">⚡ إجراءات سريعة</h3>
    </div>
    <div class="card-body">
        <div style="display: flex; flex-wrap: wrap; gap: 12px;">
            <a href="{{ route('sales.create') }}" class="btn btn-primary">💰 فاتورة بيع</a>
            <a href="{{ route('purchases.create') }}" class="btn">🛒 فاتورة شراء</a>
            <a href="{{ route('customers.create') }}" class="btn">👤 إضافة عميل</a>
            <a href="{{ route('products.create') }}" class="btn">📦 إضافة صنف</a>
            <a href="{{ route('warehouses.index') }}" class="btn">🏭 تحويل مخزن</a>
            <a href="{{ route('expenses.create') }}" class="btn">💸 تسجيل مصروف</a>
        </div>
    </div>
</div>

<!-- Two Column Layout -->
<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 20px;">
    <!-- Low Stock Alert -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">⚠️ تنبيهات المخزون</h3>
            <a href="{{ route('products.index') }}?low_stock=1" class="btn btn-sm">عرض الكل</a>
        </div>
        <div class="card-body">
            @if(isset($low_stock_products) && $low_stock_products->count() > 0)
                @foreach($low_stock_products as $product)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--bg); border-radius: 8px; margin-bottom: 8px;">
                    <span>{{ $product->name }}</span>
                    <span class="badge {{ $product->total_stock < 10 ? 'badge-danger' : 'badge-warning' }}">متبقي {{ $product->total_stock ?? 0 }}</span>
                </div>
                @endforeach
            @else
                <div class="empty-state" style="padding: 30px 20px;">
                    <div class="empty-state-icon">✅</div>
                    <h3>المخزون جيد</h3>
                    <p>لا توجد أصناف منخفضة المخزون</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Collection Status -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">💳 حالة التحصيل</h3>
        </div>
        <div class="card-body">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--bg); border-radius: 8px; margin-bottom: 8px;">
                <span>فواتير غير مدفوعة</span>
                <strong>{{ $stats['unpaid_invoices'] ?? 0 }} فاتورة</strong>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--bg); border-radius: 8px; margin-bottom: 8px;">
                <span>مدفوعات متوقعة خلال 7 أيام</span>
                <strong>{{ number_format($stats['expected_payments'] ?? 0) }} ج.م</strong>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--bg); border-radius: 8px;">
                <span>فواتير متأخرة</span>
                <strong class="text-danger">{{ $stats['overdue_invoices'] ?? 0 }} فاتورة</strong>
            </div>
        </div>
    </div>
</div>

<!-- Three Column Layout -->
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
    <!-- Top Customers -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">🏆 أفضل العملاء</h3>
            <a href="{{ route('customers.index') }}" class="btn btn-sm">عرض الكل</a>
        </div>
        <div class="card-body overflow-auto">
            @if(isset($top_customers) && $top_customers->count() > 0)
            <table class="table text-nowrap">
                <thead>
                    <tr>
                        <th>العميل</th>
                        <th>الفواتير</th>
                        <th>الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($top_customers as $customer)
                    <tr>
                        <td>{{ $customer->name }}</td>
                        <td>{{ $customer->sales_count ?? 0 }}</td>
                        <td><strong>{{ number_format($customer->sales_sum_total_amount ?? 0) }} ج.م</strong></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
                <div class="empty-state" style="padding: 30px 20px;">
                    <div class="empty-state-icon">👥</div>
                    <h3>لا يوجد عملاء بعد</h3>
                    <p>ابدأ بإضافة عملائك</p>
                    <a href="{{ route('customers.create') }}" class="btn btn-primary" style="margin-top: 12px;">+ إضافة عميل</a>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Sales -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">📄 آخر الفواتير</h3>
            <a href="{{ route('sales.index') }}" class="btn btn-sm">عرض الكل</a>
        </div>
        <div class="card-body">
            @if(isset($recent_sales) && $recent_sales->count() > 0)
                @foreach($recent_sales as $sale)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--bg); border-radius: 8px; margin-bottom: 8px;">
                    <div>
                        <span class="text-primary font-bold">{{ $sale->invoice_number }}</span>
                        @if($sale->customer)
                            <small class="text-muted" style="display: block; font-size: 11px;">{{ $sale->customer->name }}</small>
                        @endif
                    </div>
                    <strong>{{ number_format($sale->total_amount) }} ج.م</strong>
                </div>
                @endforeach
            @else
                <div class="empty-state" style="padding: 30px 20px;">
                    <div class="empty-state-icon">📄</div>
                    <h3>لا توجد فواتير بعد</h3>
                    <p>ابدأ بإنشاء أول فاتورة مبيعات</p>
                    <a href="{{ route('sales.create') }}" class="btn btn-primary" style="margin-top: 12px;">+ إنشاء فاتورة</a>
                </div>
            @endif
        </div>
    </div>

    <!-- System Stats -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">📊 إحصائيات النظام</h3>
        </div>
        <div class="card-body">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--bg); border-radius: 8px; margin-bottom: 8px;">
                <span>👥 العملاء</span>
                <strong>{{ $stats['customers_count'] ?? 156 }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--bg); border-radius: 8px; margin-bottom: 8px;">
                <span>📦 الأصناف</span>
                <strong>{{ $stats['products_count'] ?? 324 }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--bg); border-radius: 8px;">
                <span>🏷️ الأقسام</span>
                <strong>{{ $stats['categories_count'] ?? 12 }}</strong>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .mb-4 { margin-bottom: 20px; }

    @media (max-width: 1200px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr) !important; }
    }

    @media (max-width: 900px) {
        div[style*="grid-template-columns: repeat(2, 1fr)"],
        div[style*="grid-template-columns: repeat(3, 1fr)"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endpush
