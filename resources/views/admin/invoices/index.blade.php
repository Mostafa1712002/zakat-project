@extends('layouts.app')

@section('title', 'الفواتير')

@section('content')
@php
    $statusLabels = [
        'draft'        => ['مسودة', 'secondary'],
        'issued'       => ['مصدرة', 'primary'],
        'paid_partial' => ['مدفوعة جزئياً', 'warning'],
        'paid'         => ['مدفوعة', 'success'],
        'cancelled'    => ['ملغاة', 'danger'],
    ];
    $zatcaLabels = [
        'pending'  => ['قيد الإرسال', 'secondary'],
        'cleared'  => ['مرحّلة', 'success'],
        'reported' => ['مبلَّغة', 'info'],
        'failed'   => ['فشل', 'danger'],
    ];
@endphp

<div class="page-header">
    <div>
        <h1>🧾 الفواتير</h1>
        <p>إدارة الفواتير وإصدارها وإرسالها لزاتكا</p>
    </div>
    @can('create', \App\Domain\Sales\Models\Invoice::class)
        <div class="header-actions">
            <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary">➕ فاتورة جديدة</a>
        </div>
    @endcan
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<form method="GET" class="filters">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="بحث برقم الفاتورة أو اسم الفعالية" class="form-control">

    <select name="status" class="form-control">
        <option value="">— كل الحالات —</option>
        @foreach ($statusLabels as $key => [$label, $_])
            <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
        @endforeach
    </select>

    <select name="zatca_status" class="form-control">
        <option value="">— كل حالات زاتكا —</option>
        @foreach ($zatcaLabels as $key => [$label, $_])
            <option value="{{ $key }}" @selected(request('zatca_status') === $key)>{{ $label }}</option>
        @endforeach
    </select>

    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">

    <button class="btn btn-secondary">تصفية</button>
</form>

<table class="table">
    <thead>
        <tr>
            <th>الرقم</th>
            <th>العميل</th>
            <th>الفعالية</th>
            <th>الإجمالي</th>
            <th>المدفوع</th>
            <th>الحالة</th>
            <th>زاتكا</th>
            <th>التاريخ</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($invoices as $invoice)
            <tr>
                <td>{{ $invoice->invoice_number }}</td>
                <td>{{ $invoice->customer?->name ?? '—' }}</td>
                <td>{{ $invoice->event_name }}</td>
                <td>{{ number_format((float) $invoice->grand_total, 2) }}</td>
                <td>{{ number_format((float) $invoice->paid_amount, 2) }}</td>
                <td>
                    @php [$lab, $col] = $statusLabels[$invoice->status] ?? [$invoice->status, 'secondary']; @endphp
                    <span class="badge badge-{{ $col }}">{{ $lab }}</span>
                </td>
                <td>
                    @php [$zlab, $zcol] = $zatcaLabels[$invoice->zatca_status] ?? [$invoice->zatca_status, 'secondary']; @endphp
                    <span class="badge badge-{{ $zcol }}">{{ $zlab }}</span>
                </td>
                <td>{{ $invoice->created_at?->format('Y-m-d') }}</td>
                <td>
                    <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-sm">عرض</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="9" class="text-center">لا توجد فواتير</td></tr>
        @endforelse
    </tbody>
</table>

{{ $invoices->links() }}
@endsection
