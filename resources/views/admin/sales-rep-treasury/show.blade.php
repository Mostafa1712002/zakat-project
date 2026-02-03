@extends('layouts.app')

@section('title', 'خزينة ' . $salesRep->name)

@section('content')
<div class="page-header">
    <div>
        <h1>💰 خزينة {{ $salesRep->name }}</h1>
        <p>تفاصيل خزينة المندوب</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('admin.sales-rep-treasury.index') }}" class="btn">← رجوع</a>
        @if($salesRep->treasury_balance > 0)
        <form action="{{ route('admin.sales-rep-treasury.withdraw', $salesRep) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من سحب كامل الرصيد؟')">
            @csrf
            <button type="submit" class="btn btn-primary">💸 سحب كامل الرصيد ({{ number_format($salesRep->treasury_balance, 2) }} ج.م)</button>
        </form>
        @endif
    </div>
</div>

<!-- معلومات المندوب -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <div>
                <span class="text-muted">المندوب:</span>
                <strong>{{ $salesRep->name }}</strong>
            </div>
            <div>
                <span class="text-muted">الكود:</span>
                <strong>{{ $salesRep->code }}</strong>
            </div>
            <div>
                <span class="text-muted">الفرع:</span>
                <strong>{{ $salesRep->branch?->name ?? '-' }}</strong>
            </div>
            <div>
                <span class="text-muted">الرصيد الحالي:</span>
                <strong class="text-success" style="font-size: 24px;">{{ number_format($salesRep->treasury_balance, 2) }} ج.م</strong>
            </div>
        </div>
    </div>
</div>

<!-- سجل المعاملات -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">📋 سجل المعاملات</h3>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>النوع</th>
                    <th>الوصف</th>
                    <th>المبلغ</th>
                    <th>الرصيد بعدها</th>
                    <th>بواسطة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        @if($transaction->isCredit())
                            <span class="badge badge-success">{{ $transaction->type_name }}</span>
                        @else
                            <span class="badge badge-danger">{{ $transaction->type_name }}</span>
                        @endif
                    </td>
                    <td>{{ $transaction->description }}</td>
                    <td>
                        @if($transaction->isCredit())
                            <span class="text-success">+{{ number_format($transaction->amount, 2) }}</span>
                        @else
                            <span class="text-danger">-{{ number_format($transaction->amount, 2) }}</span>
                        @endif
                    </td>
                    <td>{{ number_format($transaction->balance_after, 2) }} ج.م</td>
                    <td>{{ $transaction->createdByUser?->name ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">لا توجد معاملات</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{ $transactions->links() }}
@endsection
