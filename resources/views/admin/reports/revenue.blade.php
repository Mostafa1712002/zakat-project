@extends('layouts.app')

@section('title', 'تقرير الإيرادات')

@section('content')
<div class="page-header">
    <div>
        <h1>💰 تقرير الإيرادات</h1>
        <p>إجمالي الفواتير المُصدرة خلال الفترة المحددة</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('admin.reports.export', ['type' => 'revenue', 'from' => request('from'), 'to' => request('to'), 'customer_id' => request('customer_id'), 'event_type' => request('event_type')]) }}"
           class="btn btn-primary">⬇️ تصدير CSV</a>
    </div>
</div>

<form method="GET" class="filters card" style="padding: 12px; margin-bottom: 16px; display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px;">
    <div>
        <label>من تاريخ</label>
        <input type="date" name="from" value="{{ optional($from)->format('Y-m-d') }}" class="form-control">
    </div>
    <div>
        <label>إلى تاريخ</label>
        <input type="date" name="to" value="{{ optional($to)->format('Y-m-d') }}" class="form-control">
    </div>
    <div>
        <label>العميل</label>
        <select name="customer_id" class="form-control">
            <option value="">— الكل —</option>
            @foreach ($customers as $c)
                <option value="{{ $c->id }}" @selected((int) request('customer_id') === $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>نوع الفعالية</label>
        <input type="text" name="event_type" value="{{ request('event_type') }}" class="form-control" placeholder="مثل: مؤتمر">
    </div>
    <div style="align-self: end;">
        <button class="btn btn-primary" type="submit">تطبيق</button>
        <a href="{{ route('admin.reports.revenue') }}" class="btn">إعادة تعيين</a>
    </div>
</form>

<div class="card">
    <div class="card-body">
        @if ($invoices->isEmpty())
            <p class="muted">لا توجد فواتير في هذه الفترة.</p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>الرقم</th><th>التاريخ</th><th>العميل</th><th>الفعالية</th>
                        <th>الإجمالي قبل الضريبة</th><th>الضريبة</th><th>الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($invoices as $i)
                    <tr>
                        <td><a href="{{ route('admin.invoices.show', $i) }}">{{ $i->invoice_number }}</a></td>
                        <td>{{ optional($i->issued_at)->format('Y-m-d') }}</td>
                        <td>{{ optional($i->customer)->name }}</td>
                        <td>{{ $i->event_name }}</td>
                        <td>{{ number_format($i->subtotal, 2) }}</td>
                        <td>{{ number_format($i->tax_total, 2) }}</td>
                        <td>{{ number_format($i->grand_total, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr style="font-weight: bold; background: #f8fafc;">
                        <td colspan="6">الإجمالي</td>
                        <td>{{ number_format($total, 2) }} ر.س</td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>
</div>
@endsection
