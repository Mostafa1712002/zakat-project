@extends('layouts.app')

@section('title', 'فواتير ZATCA الفاشلة')

@section('content')
<div class="page-header">
    <div>
        <h1>❌ فواتير ZATCA الفاشلة</h1>
        <p>{{ $invoices->count() }} فاتورة فشلت في الإرسال إلى هيئة الزكاة والضريبة</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('admin.reports.export', ['type' => 'zatca-failed']) }}" class="btn btn-primary">⬇️ تصدير CSV</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if ($invoices->isEmpty())
            <p class="muted">لا توجد فواتير فاشلة. ✅</p>
        @else
            <table class="table">
                <thead>
                    <tr><th>الرقم</th><th>العميل</th><th>تاريخ الإصدار</th><th>الإجمالي</th><th>الأخطاء</th><th></th></tr>
                </thead>
                <tbody>
                @foreach ($invoices as $i)
                    <tr>
                        <td>{{ $i->invoice_number }}</td>
                        <td>{{ optional($i->customer)->name }}</td>
                        <td>{{ optional($i->issued_at)->format('Y-m-d') }}</td>
                        <td>{{ number_format($i->grand_total, 2) }}</td>
                        <td style="font-size: 0.8rem; color: #b91c1c;">
                            @if (is_array($i->zatca_warnings))
                                {{ collect($i->zatca_warnings)->take(2)->implode(' • ') }}
                            @else
                                {{ Str::limit((string) $i->zatca_warnings, 80) }}
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.invoices.show', $i) }}" class="btn btn-sm">عرض</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
