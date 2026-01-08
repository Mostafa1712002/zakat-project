@extends('layouts.app')

@section('title', 'عرض المصروف')

@section('content')
<div class="page-header">
    <div>
        <h1>💸 {{ $expense->expense_number ?? 'مصروف' }}</h1>
        <p>تفاصيل المصروف</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('expenses.edit', $expense) }}" class="btn">تعديل</a>
        <a href="{{ route('expenses.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-body">
            <h3>بيانات المصروف</h3>
            <p><strong>العنوان:</strong> {{ $expense->title }}</p>
            <p><strong>التاريخ:</strong> {{ $expense->expense_date?->format('Y-m-d') ?? '-' }}</p>
            <p><strong>البند:</strong> {{ $expense->category->name ?? '-' }}</p>
            <p><strong>الفرع:</strong> {{ $expense->branch->name ?? '-' }}</p>
            <p><strong>المسؤول:</strong> {{ $expense->user->name ?? '-' }}</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <h3>التفاصيل المالية</h3>
            <p><strong>المبلغ:</strong> {{ number_format($expense->amount, 2) }} ج.م</p>
            <p><strong>الضريبة:</strong> {{ number_format($expense->tax_amount ?? 0, 2) }} ج.م</p>
            <p><strong>الإجمالي:</strong> {{ number_format($expense->total_amount ?? $expense->amount, 2) }} ج.م</p>
            @php
                $methodLabel = $expense->paymentMethod?->name;
                if (!$methodLabel && $expense->payment_method) {
                    $methodLabel = match ($expense->payment_method) {
                        'cash' => 'نقدي',
                        'bank_transfer' => 'تحويل بنكي',
                        'check' => 'شيك',
                        'card' => 'بطاقة',
                        'other' => 'أخرى',
                        default => $expense->payment_method,
                    };
                }
            @endphp
            <p><strong>طريقة الدفع:</strong> {{ $methodLabel ?? '-' }}</p>
            <p><strong>الحالة:</strong> {{ $expense->status }}</p>
        </div>
    </div>
</div>

@if($expense->vendor_name || $expense->reference_number)
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-body">
        <h3>بيانات المورد</h3>
        <p><strong>اسم المورد:</strong> {{ $expense->vendor_name ?? '-' }}</p>
        <p><strong>رقم المرجع:</strong> {{ $expense->reference_number ?? '-' }}</p>
    </div>
</div>
@endif

@if($expense->description)
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-body">
        <h3>الوصف</h3>
        <p>{{ $expense->description }}</p>
    </div>
</div>
@endif

@if($expense->notes)
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-body">
        <h3>ملاحظات</h3>
        <p>{{ $expense->notes }}</p>
    </div>
</div>
@endif

<style>
.grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
.card-body h3 { margin-bottom: 1rem; font-size: 1rem; color: var(--primary); }
</style>
@endsection
