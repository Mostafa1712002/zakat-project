@extends('layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
{{-- NOTE: Phase 1 cleanup — old product/sale dashboard widgets removed.
     New dashboard with quote/invoice KPIs comes in Phase 7. --}}
<div class="page-header">
    <div>
        <h1>📊 لوحة التحكم</h1>
        <p>نظرة عامة على النشاط</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('customers.create') }}" class="btn btn-primary">+ إضافة عميل</a>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon success">🏦</div>
        <div class="stat-value">{{ number_format($stats['treasury_balance'] ?? 0) }} ج.م</div>
        <div class="stat-label">رصيد الخزنة</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon primary">👥</div>
        <div class="stat-value">{{ $stats['customers_count'] ?? 0 }}</div>
        <div class="stat-label">العملاء</div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">⚡ إجراءات سريعة</h3>
    </div>
    <div class="card-body">
        <div style="display: flex; flex-wrap: wrap; gap: 12px;">
            <a href="{{ route('customers.create') }}" class="btn btn-primary">👤 إضافة عميل</a>
            <a href="{{ route('expenses.create') }}" class="btn">💸 تسجيل مصروف</a>
            <a href="{{ route('treasury.index') }}" class="btn">🏦 الخزنة</a>
            <a href="{{ route('zatca.dashboard') }}" class="btn">📋 الفوترة الإلكترونية</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="empty-state" style="padding: 30px 20px;">
            <div class="empty-state-icon">🛠️</div>
            <h3>المنصة قيد إعادة البناء</h3>
            <p>يتم تحويل النظام إلى منصة خدمات AMMRK — الكتالوج والتسعيرات والفواتير قيد التطوير.</p>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .mb-4 {
        margin-bottom: 20px;
    }

    .stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .stats-grid .stat-card {
        min-width: 0;
    }

    .stats-grid .stat-value {
        font-size: clamp(1.4rem, 5.2vw, 2rem);
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr !important;
        }

        .stats-grid .stat-value {
            font-size: clamp(1.6rem, 8.2vw, 2rem);
        }
    }
</style>
@endpush
