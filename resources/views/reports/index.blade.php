@extends('layouts.app')

@section('title', 'التقارير')

@section('content')
<div class="page-header">
    <div>
        <h1>📈 التقارير</h1>
        <p>لوحة التقارير والتحليلات</p>
    </div>
</div>

<div class="reports-grid">
    <div class="card report-card">
        <div class="report-card-icon">💰</div>
        <div class="report-card-content">
            <h3>تقرير الأرباح</h3>
            <p>تحليل الأرباح والإيرادات والمصروفات</p>
        </div>
        <div class="report-card-action">
            <a href="{{ route('reports.profits') }}" class="btn btn-primary">عرض التقرير</a>
        </div>
    </div>

    <div class="card report-card">
        <div class="report-card-icon">🧾</div>
        <div class="report-card-content">
            <h3>تقرير المبيعات</h3>
            <p>تحليل المبيعات حسب العملاء والتواريخ</p>
        </div>
        <div class="report-card-action">
            <a href="{{ route('reports.sales') }}" class="btn btn-primary">عرض التقرير</a>
        </div>
    </div>

    <div class="card report-card">
        <div class="report-card-icon">📦</div>
        <div class="report-card-content">
            <h3>تقرير المخزون</h3>
            <p>مستويات المخزون وحركة الأصناف</p>
        </div>
        <div class="report-card-action">
            <a href="{{ route('reports.inventory') }}" class="btn btn-primary">عرض التقرير</a>
        </div>
    </div>

    <div class="card report-card">
        <div class="report-card-icon">👥</div>
        <div class="report-card-content">
            <h3>تقرير العملاء</h3>
            <p>أفضل العملاء والأرصدة المستحقة</p>
        </div>
        <div class="report-card-action">
            <a href="{{ route('reports.customers') }}" class="btn btn-primary">عرض التقرير</a>
        </div>
    </div>

    <div class="card report-card">
        <div class="report-card-icon">🔄</div>
        <div class="report-card-content">
            <h3>حركة المخزون</h3>
            <p>سجل تحركات المخزون حسب المخزن والصنف</p>
        </div>
        <div class="report-card-action">
            <a href="{{ route('reports.stock-movements') }}" class="btn btn-primary">عرض التقرير</a>
        </div>
    </div>
</div>

<style>
.reports-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
}

.report-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 2rem;
    transition: transform 0.2s, box-shadow 0.2s;
}

.report-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.report-card-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.report-card-content h3 {
    margin: 0 0 0.5rem;
    font-size: 1.25rem;
    color: var(--text-primary);
}

.report-card-content p {
    margin: 0;
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.report-card-action {
    margin-top: 1.5rem;
}
</style>
@endsection
