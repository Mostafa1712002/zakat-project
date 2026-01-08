@extends('layouts.app')

@section('title', 'عرض المندوب')

@section('content')
<div class="page-header">
    <div>
        <h1>🧑‍💼 {{ $salesRep->name }}</h1>
        <p>تفاصيل مندوب المبيعات</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('sales-reps.edit', $salesRep) }}" class="btn">تعديل</a>
        <a href="{{ route('sales-reps.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-body">
            <h3>بيانات المندوب</h3>
            <p><strong>الكود:</strong> {{ $salesRep->code ?? '-' }}</p>
            <p><strong>البريد:</strong> {{ $salesRep->email ?? '-' }}</p>
            <p><strong>الهاتف:</strong> {{ $salesRep->phone ?? '-' }}</p>
            <p><strong>الفرع:</strong> {{ $salesRep->branch->name ?? '-' }}</p>
            <p><strong>المناطق:</strong> {{ is_array($salesRep->regions) ? implode('، ', $salesRep->regions) : '-' }}</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <h3>الأهداف والعمولات</h3>
            <p><strong>نوع العمولة:</strong> {{ $salesRep->commission_type === 'fixed' ? 'ثابتة' : 'نسبة' }}</p>
            <p><strong>قيمة العمولة:</strong> {{ number_format($salesRep->commission_rate ?? 0, 2) }}{{ $salesRep->commission_type === 'percentage' ? '%' : ' ج.م' }}</p>
            <p><strong>هدف المبيعات:</strong> {{ number_format($salesRep->sales_target ?? 0, 2) }} ج.م</p>
            <p><strong>الحالة:</strong>
                @if($salesRep->is_active)
                    <span class="badge badge-success">نشط</span>
                @else
                    <span class="badge badge-danger">غير نشط</span>
                @endif
            </p>
        </div>
    </div>
</div>

@if($salesRep->notes)
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-body">
        <h3>ملاحظات</h3>
        <p>{{ $salesRep->notes }}</p>
    </div>
</div>
@endif

<style>
.grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
.card-body h3 { margin-bottom: 1rem; font-size: 1rem; color: var(--primary); }
</style>
@endsection
