@extends('layouts.app')

@section('title', 'خزينتي')

@section('content')
<div class="page-header">
    <div>
        <h1>💰 خزينتي</h1>
        <p>إدارة رصيد الخزينة الخاص بك</p>
    </div>
</div>

<!-- إحصائيات -->
<div class="stats-grid" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon" style="background: #dcfce7; color: #16a34a;">💰</div>
        <div class="stat-details">
            <div class="stat-value">{{ number_format($salesRep->treasury_balance, 2) }} ج.م</div>
            <div class="stat-label">الرصيد الحالي</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #dbeafe; color: #2563eb;">📥</div>
        <div class="stat-details">
            <div class="stat-value">{{ number_format($todayStats['collections'], 2) }} ج.م</div>
            <div class="stat-label">تحصيلات اليوم</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #fee2e2; color: #dc2626;">📤</div>
        <div class="stat-details">
            <div class="stat-value">{{ number_format($todayStats['expenses'], 2) }} ج.م</div>
            <div class="stat-label">مصروفات اليوم</div>
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
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">لا توجد معاملات بعد</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{ $transactions->links() }}
@endsection
