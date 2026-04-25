@extends('layouts.app')

@section('title', 'تقرير ضريبة القيمة المضافة')

@section('content')
<div class="page-header">
    <div>
        <h1>📋 تقرير ضريبة القيمة المضافة (ZATCA)</h1>
        <p>إجمالي الضريبة المُحصّلة شهرياً مقارنةً بالمعتمدة من ZATCA</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('admin.reports.export', ['type' => 'vat', 'from' => request('from'), 'to' => request('to')]) }}"
           class="btn btn-primary">⬇️ تصدير CSV</a>
    </div>
</div>

<form method="GET" class="filters card" style="padding: 12px; margin-bottom: 16px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
    <div>
        <label>من تاريخ</label>
        <input type="date" name="from" value="{{ optional($from)->format('Y-m-d') }}" class="form-control">
    </div>
    <div>
        <label>إلى تاريخ</label>
        <input type="date" name="to" value="{{ optional($to)->format('Y-m-d') }}" class="form-control">
    </div>
    <div style="align-self: end;">
        <button class="btn btn-primary" type="submit">تطبيق</button>
    </div>
</form>

<div class="card">
    <div class="card-body">
        @if ($rows->isEmpty())
            <p class="muted">لا توجد فواتير مُصدرة في هذه الفترة.</p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>الشهر</th>
                        <th>إجمالي الضريبة</th>
                        <th>المعتمد من ZATCA</th>
                        <th>المتبقي</th>
                    </tr>
                </thead>
                <tbody>
                @php
                    $totalVat = 0; $clearedVat = 0; $pendingVat = 0;
                @endphp
                @foreach ($rows as $r)
                    @php
                        $totalVat += (float) $r->total_vat;
                        $clearedVat += (float) $r->cleared_vat;
                        $pendingVat += (float) $r->pending_vat;
                    @endphp
                    <tr>
                        <td>{{ $r->ym }}</td>
                        <td>{{ number_format($r->total_vat, 2) }}</td>
                        <td style="color: #10b981;">{{ number_format($r->cleared_vat, 2) }}</td>
                        <td style="color: #f59e0b;">{{ number_format($r->pending_vat, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr style="font-weight: bold; background: #f8fafc;">
                        <td>الإجمالي</td>
                        <td>{{ number_format($totalVat, 2) }}</td>
                        <td>{{ number_format($clearedVat, 2) }}</td>
                        <td>{{ number_format($pendingVat, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>
</div>
@endsection
