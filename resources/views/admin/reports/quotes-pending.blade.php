@extends('layouts.app')

@section('title', 'عروض الأسعار قيد الاعتماد')

@section('content')
<div class="page-header">
    <div>
        <h1>📑 عروض الأسعار قيد الاعتماد</h1>
        <p>{{ $quotes->count() }} عرض ينتظر الاعتماد</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('admin.reports.export', ['type' => 'quotes-pending']) }}" class="btn btn-primary">⬇️ تصدير CSV</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if ($quotes->isEmpty())
            <p class="muted">لا توجد عروض قيد الاعتماد.</p>
        @else
            <table class="table">
                <thead>
                    <tr><th>الرقم</th><th>العميل</th><th>الفعالية</th><th>الإجمالي</th><th>تاريخ الإنشاء</th><th>المُنشئ</th><th></th></tr>
                </thead>
                <tbody>
                @foreach ($quotes as $q)
                    <tr>
                        <td>{{ $q->quote_number }}</td>
                        <td>{{ optional($q->customer)->name }}</td>
                        <td>{{ $q->event_name }}</td>
                        <td>{{ number_format($q->grand_total, 2) }}</td>
                        <td>{{ optional($q->created_at)->format('Y-m-d') }}</td>
                        <td>{{ optional($q->creator)->name ?? '—' }}</td>
                        <td><a href="{{ route('admin.quotes.show', $q) }}" class="btn btn-sm">عرض</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
