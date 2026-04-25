@extends('layouts.app')

@section('title', 'تقرير المتأخرات')

@section('content')
<div class="page-header">
    <div>
        <h1>⚠️ تقرير أعمار المتأخرات</h1>
        <p>المبالغ المستحقة على العملاء مقسّمة حسب العمر</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('admin.reports.export', ['type' => 'customer-debt']) }}" class="btn btn-primary">⬇️ تصدير CSV</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if (empty($buckets))
            <p class="muted">لا توجد فواتير غير مدفوعة.</p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>العميل</th>
                        <th>0-30 يوم</th>
                        <th>31-60 يوم</th>
                        <th>61-90 يوم</th>
                        <th>+90 يوم</th>
                        <th>الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($buckets as $row)
                    <tr>
                        <td>{{ optional($row['customer'])->name ?? '—' }}</td>
                        <td>{{ number_format($row['b0_30'], 2) }}</td>
                        <td>{{ number_format($row['b31_60'], 2) }}</td>
                        <td>{{ number_format($row['b61_90'], 2) }}</td>
                        <td style="background: {{ $row['b90_plus'] > 0 ? '#fee2e2' : 'transparent' }}; color: {{ $row['b90_plus'] > 0 ? '#b91c1c' : 'inherit' }};">
                            {{ number_format($row['b90_plus'], 2) }}
                        </td>
                        <td style="font-weight: bold;">{{ number_format($row['total'], 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr style="font-weight: bold; background: #f8fafc;">
                        <td>الإجمالي</td>
                        <td>{{ number_format($grand['b0_30'], 2) }}</td>
                        <td>{{ number_format($grand['b31_60'], 2) }}</td>
                        <td>{{ number_format($grand['b61_90'], 2) }}</td>
                        <td>{{ number_format($grand['b90_plus'], 2) }}</td>
                        <td>{{ number_format($grand['total'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>
</div>
@endsection
