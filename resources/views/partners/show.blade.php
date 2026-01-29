@extends('layouts.app')

@section('title', 'بيانات الشريك')

@section('content')
<div class="page-header">
    <div>
        <h1>👤 بيانات الشريك</h1>
        <p>{{ $partner->name }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('partner-transactions.create', ['partner_id' => $partner->id]) }}" class="btn btn-success">+ تسجيل معاملة</a>
        <a href="{{ route('partner-transactions.partner-history', $partner) }}" class="btn">💰 سجل المعاملات</a>
        <a href="{{ route('partners.edit', $partner) }}" class="btn">✏️ تعديل</a>
        <a href="{{ route('partners.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<!-- Summary Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: #10b981;">💵</div>
        <div class="stat-content">
            <div class="stat-value">{{ number_format($totals['investments'], 2) }}</div>
            <div class="stat-label">إجمالي الاستثمارات</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #f59e0b;">💰</div>
        <div class="stat-content">
            <div class="stat-value">{{ number_format($totals['withdrawals'], 2) }}</div>
            <div class="stat-label">إجمالي السحوبات</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #3b82f6;">📊</div>
        <div class="stat-content">
            <div class="stat-value">{{ number_format($totals['profit_shares'], 2) }}</div>
            <div class="stat-label">إجمالي الأرباح الموزعة</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #ef4444;">↩️</div>
        <div class="stat-content">
            <div class="stat-value">{{ number_format($totals['returns'], 2) }}</div>
            <div class="stat-label">إرجاع رأس المال</div>
        </div>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-body">
            <h3 style="margin-bottom: 16px;">📋 البيانات الأساسية</h3>

            <div class="info-grid">
                <div class="info-item">
                    <label>كود الشريك</label>
                    <span><code>{{ $partner->partner_code }}</code></span>
                </div>

                <div class="info-item">
                    <label>الاسم</label>
                    <span>{{ $partner->name }}</span>
                </div>

                <div class="info-item">
                    <label>الهاتف</label>
                    <span>{{ $partner->phone ?? '-' }}</span>
                </div>

                <div class="info-item">
                    <label>الحالة</label>
                    <span>
                        @if($partner->is_active)
                            <span class="badge badge-success">نشط</span>
                        @else
                            <span class="badge badge-danger">غير نشط</span>
                        @endif
                    </span>
                </div>

                @if($partner->address)
                <div class="info-item full-width">
                    <label>العنوان</label>
                    <span>{{ $partner->address }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 style="margin-bottom: 16px;">💰 البيانات المالية</h3>

            <div class="info-grid">
                <div class="info-item">
                    <label>نسبة الملكية</label>
                    <span class="highlight">{{ $partner->ownership_percentage }}%</span>
                </div>

                <div class="info-item">
                    <label>رأس المال المبدئي</label>
                    <span>{{ number_format($partner->initial_investment, 2) }} ج.م</span>
                </div>

                <div class="info-item">
                    <label>تاريخ الانضمام</label>
                    <span>{{ $partner->join_date?->format('Y/m/d') ?? '-' }}</span>
                </div>

                <div class="info-item">
                    <label>الرصيد الحالي</label>
                    <span class="highlight">{{ number_format($partner->current_balance, 2) }} ج.م</span>
                </div>

                @if($partner->notes)
                <div class="info-item full-width">
                    <label>ملاحظات</label>
                    <span>{{ $partner->notes }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.stat-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}
.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}
.stat-content { flex: 1; }
.stat-value { font-size: 1.25rem; font-weight: 700; }
.stat-label { color: var(--text-muted); font-size: 0.75rem; }
.grid-2 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 1.5rem;
}
.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}
.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.info-item.full-width {
    grid-column: span 2;
}
.info-item label {
    color: var(--text-muted);
    font-size: 0.75rem;
    text-transform: uppercase;
}
.info-item span {
    font-size: 1rem;
}
.highlight {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary);
}
.badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; }
.badge-success { background: #10b981; color: white; }
.badge-danger { background: #ef4444; color: white; }
</style>
@endsection
