@extends('layouts.app')

@section('title', 'عروض الأسعار')

@section('content')
@php
    $statusLabels = [
        'draft'      => ['مسودة', 'secondary'],
        'submitted'  => ['قيد الاعتماد', 'warning'],
        'approved'   => ['معتمد', 'success'],
        'rejected'   => ['مرفوض', 'danger'],
        'converted'  => ['محول لفاتورة', 'info'],
    ];
@endphp
<div class="page-header">
    <div>
        <h1>📑 عروض الأسعار</h1>
        <p>إدارة عروض الأسعار من المسودة إلى الاعتماد</p>
    </div>
    @can('create', \App\Domain\Sales\Models\Quote::class)
        <div class="header-actions">
            <a href="{{ route('admin.quotes.create') }}" class="btn btn-primary">
                ➕ عرض سعر جديد
            </a>
        </div>
    @endcan
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<form method="GET" class="filters">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="بحث برقم العرض أو اسم الفعالية" class="form-control">

    <select name="status" class="form-control">
        <option value="">— كل الحالات —</option>
        @foreach ($statusLabels as $key => [$label, $_])
            <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
        @endforeach
    </select>

    <select name="customer_id" class="form-control">
        <option value="">— كل العملاء —</option>
        @foreach ($customers as $c)
            <option value="{{ $c->id }}" @selected((int) request('customer_id') === $c->id)>{{ $c->name }}</option>
        @endforeach
    </select>

    <button class="btn btn-secondary">تصفية</button>
</form>

<table class="table">
    <thead>
        <tr>
            <th>الرقم</th>
            <th>العميل</th>
            <th>الفعالية</th>
            <th>الإجمالي</th>
            <th>الحالة</th>
            <th>تاريخ الإنشاء</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($quotes as $quote)
            <tr>
                <td>{{ $quote->quote_number }}</td>
                <td>{{ $quote->customer?->name ?? '—' }}</td>
                <td>{{ $quote->event_name }}</td>
                <td>{{ number_format((float) $quote->grand_total, 2) }}</td>
                <td>
                    @php [$label, $color] = $statusLabels[$quote->status] ?? [$quote->status, 'secondary']; @endphp
                    <span class="badge badge-{{ $color }}">{{ $label }}</span>
                </td>
                <td>{{ $quote->created_at?->format('Y-m-d') }}</td>
                <td>
                    <a href="{{ route('admin.quotes.show', $quote) }}" class="btn btn-sm">عرض</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center">لا توجد عروض أسعار</td></tr>
        @endforelse
    </tbody>
</table>

{{ $quotes->links() }}
@endsection
