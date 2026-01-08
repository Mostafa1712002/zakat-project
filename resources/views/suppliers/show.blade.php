@extends('layouts.app')

@section('title', 'عرض المورد')

@section('content')
<div class="page-header">
    <div>
        <h1>🏭 {{ $supplier->name }}</h1>
        <p>تفاصيل المورد</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn">تعديل</a>
        <a href="{{ route('suppliers.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-body">
            <h3>بيانات المورد</h3>
            <p><strong>الكود:</strong> {{ $supplier->code ?? '-' }}</p>
            <p><strong>البريد:</strong> {{ $supplier->email ?? '-' }}</p>
            <p><strong>الهاتف:</strong> {{ $supplier->phone ?? '-' }}</p>
            <p><strong>الموبايل:</strong> {{ $supplier->mobile ?? '-' }}</p>
            <p><strong>جهة الاتصال:</strong> {{ $supplier->contact_person ?? '-' }}</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <h3>معلومات مالية</h3>
            <p><strong>الرصيد الحالي:</strong> {{ number_format($supplier->current_balance ?? 0, 2) }} ج.م</p>
            <p><strong>مدة السداد:</strong> {{ $supplier->payment_terms_days ?? '-' }} يوم</p>
            <p><strong>البنك:</strong> {{ $supplier->bank_name ?? '-' }}</p>
            <p><strong>رقم الحساب:</strong> {{ $supplier->bank_account ?? '-' }}</p>
            <p><strong>الحالة:</strong>
                @if($supplier->is_active)
                    <span class="badge badge-success">نشط</span>
                @else
                    <span class="badge badge-danger">غير نشط</span>
                @endif
            </p>
        </div>
    </div>
</div>

@if($supplier->address)
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-body">
        <h3>العنوان</h3>
        <p>{{ $supplier->address }}{{ $supplier->city ? ' - ' . $supplier->city : '' }}</p>
    </div>
</div>
@endif

@if($supplier->notes)
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-body">
        <h3>ملاحظات</h3>
        <p>{{ $supplier->notes }}</p>
    </div>
</div>
@endif

<style>
.grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
.card-body h3 { margin-bottom: 1rem; font-size: 1rem; color: var(--primary); }
</style>
@endsection
