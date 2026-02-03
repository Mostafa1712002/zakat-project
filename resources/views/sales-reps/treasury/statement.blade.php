@extends('layouts.app')

@section('title', 'كشف حساب الخزينة')

@section('content')
<div class="page-header">
    <div>
        <h1>كشف حساب الخزينة</h1>
        <p>المندوب: {{ $salesRep->name }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('sales-reps.deposit.form', $salesRep) }}" class="btn btn-primary">+ إيداع</a>
        <a href="{{ route('sales-reps.withdraw.form', $salesRep) }}" class="btn btn-warning">- سحب</a>
        <a href="{{ route('sales-reps.show', $salesRep) }}" class="btn">← رجوع</a>
    </div>
</div>

<!-- الرصيد الحالي -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value {{ $salesRep->treasury_balance >= 0 ? 'text-success' : 'text-danger' }}">
            {{ number_format($salesRep->treasury_balance, 2) }}
        </div>
        <div class="stat-label">الرصيد الحالي (ج.م)</div>
    </div>
</div>

<!-- جدول المعاملات -->
<div class="card">
    <div class="card-body overflow-auto">
        @if($transactions->count() > 0)
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>النوع</th>
                    <th>الوصف</th>
                    <th>المبلغ</th>
                    <th>الرصيد بعد</th>
                    <th>بواسطة</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        @if($transaction->isCredit())
                            <span class="badge badge-success">{{ $transaction->type_name }}</span>
                        @else
                            <span class="badge badge-danger">{{ $transaction->type_name }}</span>
                        @endif
                    </td>
                    <td>{{ $transaction->description ?? '-' }}</td>
                    <td class="{{ $transaction->isCredit() ? 'text-success' : 'text-danger' }}">
                        {{ $transaction->isCredit() ? '+' : '-' }}{{ number_format($transaction->amount, 2) }}
                    </td>
                    <td>{{ number_format($transaction->balance_after, 2) }}</td>
                    <td>{{ $transaction->creator->name ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{ $transactions->links() }}
        @else
        <p class="text-center text-muted">لا توجد معاملات</p>
        @endif
    </div>
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.stat-value { font-size: 1.5rem; font-weight: 700; }
.stat-label { color: #6b7280; font-size: 0.875rem; margin-top: 0.25rem; }
.text-success { color: #22c55e; }
.text-danger { color: #ef4444; }
.text-muted { color: #6b7280; }
.text-center { text-align: center; }
</style>
@endsection
