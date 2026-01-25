@extends('layouts.app')

@section('title', 'تفاصيل توزيع الأرباح - ' . $period)

@section('content')
<div class="page-header">
    <div>
        <h1>📊 تفاصيل توزيع الأرباح</h1>
        <p>الفترة: {{ $period }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('profit-distribution.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<!-- Summary -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: #10b981;">💵</div>
        <div class="stat-content">
            <div class="stat-value">{{ number_format($totalAmount, 2) }}</div>
            <div class="stat-label">إجمالي التوزيع</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #3b82f6;">👥</div>
        <div class="stat-content">
            <div class="stat-value">{{ $transactions->count() }}</div>
            <div class="stat-label">عدد الشركاء</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #8b5cf6;">📅</div>
        <div class="stat-content">
            <div class="stat-value">{{ $transactions->first()->transaction_date->format('Y/m/d') }}</div>
            <div class="stat-label">تاريخ التوزيع</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h3 style="margin-bottom: 16px;">💰 تفاصيل التوزيع على الشركاء</h3>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>رقم المعاملة</th>
                        <th>الشريك</th>
                        <th>نسبة الملكية</th>
                        <th>المبلغ</th>
                        <th>طريقة الدفع</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                    <tr>
                        <td><code>{{ $transaction->transaction_number }}</code></td>
                        <td>
                            <a href="{{ route('partner-transactions.partner-history', $transaction->partner) }}">
                                <strong>{{ $transaction->partner->name }}</strong>
                            </a>
                        </td>
                        <td>{{ $transaction->partner->ownership_percentage }}%</td>
                        <td class="text-success">{{ number_format($transaction->amount, 2) }} ج.م</td>
                        <td>{{ $transaction->payment_method_name }}</td>
                        <td>
                            <a href="{{ route('partner-transactions.show', $transaction) }}" class="btn btn-sm">👁️ عرض</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="3"><strong>الإجمالي</strong></td>
                        <td class="text-success"><strong>{{ number_format($totalAmount, 2) }} ج.م</strong></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@if($transactions->first()->description || $transactions->first()->notes)
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-body">
        <h3 style="margin-bottom: 16px;">📝 ملاحظات</h3>

        @if($transactions->first()->description)
        <div class="info-item">
            <label>الوصف</label>
            <p>{{ $transactions->first()->description }}</p>
        </div>
        @endif

        @if($transactions->first()->notes)
        <div class="info-item">
            <label>ملاحظات إضافية</label>
            <p>{{ $transactions->first()->notes }}</p>
        </div>
        @endif
    </div>
</div>
@endif

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.stat-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}
.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}
.stat-content { flex: 1; }
.stat-value { font-size: 1.25rem; font-weight: 700; }
.stat-label { color: var(--text-muted); font-size: 0.75rem; }
.text-success { color: #10b981; }
.total-row {
    background: rgba(16,185,129,0.1);
}
.total-row td {
    border-top: 2px solid #10b981;
}
.info-item {
    margin-bottom: 1rem;
}
.info-item label {
    display: block;
    color: var(--text-muted);
    font-size: 0.75rem;
    text-transform: uppercase;
    margin-bottom: 0.25rem;
}
.info-item p {
    margin: 0;
}
</style>
@endsection
