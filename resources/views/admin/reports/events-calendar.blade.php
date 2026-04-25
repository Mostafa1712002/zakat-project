@extends('layouts.app')

@section('title', 'تقويم الفعاليات')

@section('content')
<div class="page-header">
    <div>
        <h1>📅 تقويم الفعاليات (90 يوم قادمة)</h1>
        <p>من {{ $start->format('Y-m-d') }} إلى {{ $end->format('Y-m-d') }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('admin.reports.export', ['type' => 'events-calendar']) }}" class="btn btn-primary">⬇️ تصدير CSV</a>
    </div>
</div>

@if ($byMonth->isEmpty())
    <div class="card"><div class="card-body"><p class="muted">لا توجد فعاليات قادمة.</p></div></div>
@else
    @foreach ($byMonth as $ym => $events)
        <div class="card" style="margin-bottom: 16px;">
            <div class="card-header">
                <h3 class="card-title">📆 {{ \Carbon\Carbon::createFromFormat('Y-m', $ym)->translatedFormat('F Y') }}
                    <small style="color: #64748b;">({{ $events->count() }} فعالية)</small>
                </h3>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr><th>التاريخ</th><th>الفاتورة</th><th>العميل</th><th>الفعالية</th><th>الموقع</th><th>الإجمالي</th></tr>
                    </thead>
                    <tbody>
                    @foreach ($events as $i)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($i->event_start_date)->format('Y-m-d') }}</td>
                            <td><a href="{{ route('admin.invoices.show', $i) }}">{{ $i->invoice_number }}</a></td>
                            <td>{{ optional($i->customer)->name }}</td>
                            <td>{{ $i->event_name }}</td>
                            <td>{{ $i->event_location }}</td>
                            <td>{{ number_format($i->grand_total, 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
@endif
@endsection
