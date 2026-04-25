@extends('layouts.app')

@section('title', 'المدفوعات')

@section('content')
@php
    $methodLabels = [
        'cash' => 'نقدي', 'bank' => 'بنكي', 'transfer' => 'تحويل', 'check' => 'شيك',
    ];
@endphp
<div class="page-header">
    <div>
        <h1>💰 المدفوعات</h1>
        <p>سجل جميع الدفعات المستلمة من العملاء</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('admin.payments.create') }}" class="btn btn-primary">➕ تسجيل دفعة</a>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<form method="GET" class="filter-bar">
    <input type="date" name="from" value="{{ request('from') }}" placeholder="من تاريخ">
    <input type="date" name="to" value="{{ request('to') }}" placeholder="إلى تاريخ">
    <select name="method">
        <option value="">— كل الطرق —</option>
        @foreach ($methodLabels as $k => $v)
            <option value="{{ $k }}" @selected(request('method') === $k)>{{ $v }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-secondary">بحث</button>
</form>

<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>رقم الدفعة</th><th>الفاتورة</th><th>العميل</th>
                <th>المبلغ</th><th>الطريقة</th><th>الخزينة</th><th>التاريخ</th><th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($payments as $p)
                <tr>
                    <td><strong>{{ $p->payment_number }}</strong></td>
                    <td>{{ $p->invoice?->invoice_number }}</td>
                    <td>{{ $p->customer?->name }}</td>
                    <td>{{ number_format($p->amount, 2) }} ر.س</td>
                    <td>{{ $methodLabels[$p->method] ?? $p->method }}</td>
                    <td>{{ $p->treasury?->name }}</td>
                    <td>{{ $p->payment_date?->format('Y-m-d') }}</td>
                    <td><a href="{{ route('admin.payments.show', $p) }}" class="btn btn-sm">عرض</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center">لا توجد مدفوعات</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $payments->links() }}
</div>
@endsection
