@extends('layouts.app')

@section('title', 'عرض المندوب')

@section('content')
<div class="page-header">
    <div>
        <h1>{{ $salesRep->name }}</h1>
        <p>تفاصيل مندوب المبيعات</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('sales-reps.edit', $salesRep) }}" class="btn">تعديل</a>
        <a href="{{ route('sales-reps.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<!-- الإحصائيات -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value">{{ number_format($stats['total_sales']) }}</div>
        <div class="stat-label">إجمالي المبيعات (ج.م)</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['total_invoices'] }}</div>
        <div class="stat-label">عدد الفواتير</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['total_customers'] }}</div>
        <div class="stat-label">عدد العملاء</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ number_format($stats['total_collections']) }}</div>
        <div class="stat-label">إجمالي التحصيلات (ج.م)</div>
    </div>
    <div class="stat-card {{ $salesRep->treasury_balance >= 0 ? 'stat-success' : 'stat-danger' }}">
        <div class="stat-value">{{ number_format($salesRep->treasury_balance) }}</div>
        <div class="stat-label">رصيد الخزينة (ج.م)</div>
    </div>
</div>

<div class="grid-2">
    <!-- بيانات المندوب -->
    <div class="card">
        <div class="card-body">
            <h3>بيانات المندوب</h3>
            <p><strong>الكود:</strong> {{ $salesRep->code ?? '-' }}</p>
            <p><strong>النوع:</strong>
                @if($salesRep->type === 'fridge')
                    <span class="badge badge-info">تلاجة</span>
                @else
                    <span class="badge badge-warning">خاص</span>
                @endif
            </p>
            <p><strong>البريد:</strong> {{ $salesRep->email ?? '-' }}</p>
            <p><strong>الهاتف:</strong> {{ $salesRep->phone ?? '-' }}</p>
            <p><strong>الفرع:</strong> {{ $salesRep->branch->name ?? '-' }}</p>
            <p><strong>المناطق:</strong> {{ is_array($salesRep->regions) ? implode('، ', $salesRep->regions) : '-' }}</p>
            <p><strong>الحالة:</strong>
                @if($salesRep->is_active)
                    <span class="badge badge-success">نشط</span>
                @else
                    <span class="badge badge-danger">غير نشط</span>
                @endif
            </p>
        </div>
    </div>

    <!-- الأهداف والعمولات -->
    <div class="card">
        <div class="card-body">
            <h3>الأهداف والعمولات</h3>
            <p><strong>نوع العمولة:</strong> {{ $salesRep->commission_type === 'fixed' ? 'ثابتة' : 'نسبة' }}</p>
            <p><strong>قيمة العمولة:</strong> {{ number_format($salesRep->commission_rate ?? 0, 2) }}{{ $salesRep->commission_type === 'percentage' ? '%' : ' ج.م' }}</p>
            <p><strong>هدف المبيعات:</strong> {{ number_format($salesRep->sales_target ?? 0, 2) }} ج.م</p>
            <p><strong>نسبة التحقيق:</strong>
                <span class="badge {{ $stats['target_achievement'] >= 100 ? 'badge-success' : ($stats['target_achievement'] >= 50 ? 'badge-warning' : 'badge-danger') }}">
                    {{ number_format($stats['target_achievement'], 1) }}%
                </span>
            </p>
        </div>
    </div>
</div>

<!-- الخزينة -->
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">الخزينة</h3>
        <div class="card-actions">
            <a href="{{ route('sales-reps.deposit.form', $salesRep) }}" class="btn btn-sm btn-primary">+ إيداع</a>
            <a href="{{ route('sales-reps.withdraw.form', $salesRep) }}" class="btn btn-sm btn-warning">- سحب</a>
            <a href="{{ route('sales-reps.treasury', $salesRep) }}" class="btn btn-sm">كشف الحساب</a>
        </div>
    </div>
    <div class="card-body overflow-auto">
        @if($treasuryTransactions->count() > 0)
        <table class="table table-sm text-nowrap">
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>النوع</th>
                    <th>الوصف</th>
                    <th>المبلغ</th>
                    <th>الرصيد بعد</th>
                </tr>
            </thead>
            <tbody>
                @foreach($treasuryTransactions as $transaction)
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
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="text-muted text-center">لا توجد معاملات في الخزينة</p>
        @endif
    </div>
</div>

<!-- المخازن المرتبطة -->
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">المخازن المرتبطة</h3>
    </div>
    <div class="card-body">
        @if($salesRep->warehouses->count() > 0)
        <div class="warehouses-list">
            @foreach($salesRep->warehouses as $warehouse)
            <div class="warehouse-item">
                <span class="warehouse-name">{{ $warehouse->name }}</span>
                @if($warehouse->pivot->is_default)
                    <span class="badge badge-primary">افتراضي</span>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <p class="text-muted text-center">لا توجد مخازن مرتبطة</p>
        @endif
    </div>
</div>

<!-- المصروفات -->
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">المصروفات الأخيرة</h3>
    </div>
    <div class="card-body overflow-auto">
        @if($expenses->count() > 0)
        <table class="table table-sm text-nowrap">
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>الرقم</th>
                    <th>التصنيف</th>
                    <th>الوصف</th>
                    <th>المبلغ</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expenses as $expense)
                <tr>
                    <td>{{ $expense->expense_date->format('Y-m-d') }}</td>
                    <td>{{ $expense->expense_number }}</td>
                    <td>{{ $expense->category->name ?? '-' }}</td>
                    <td>{{ $expense->title }}</td>
                    <td>{{ number_format($expense->total_amount, 2) }}</td>
                    <td>
                        @if($expense->status === 'paid')
                            <span class="badge badge-success">مدفوع</span>
                        @elseif($expense->status === 'approved')
                            <span class="badge badge-info">معتمد</span>
                        @elseif($expense->status === 'pending')
                            <span class="badge badge-warning">معلق</span>
                        @else
                            <span class="badge badge-danger">مرفوض</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="text-muted text-center">لا توجد مصروفات</p>
        @endif
    </div>
</div>

@if($salesRep->notes)
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-body">
        <h3>ملاحظات</h3>
        <p>{{ $salesRep->notes }}</p>
    </div>
</div>
@endif

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.stat-card {
    background: white;
    padding: 1.25rem;
    border-radius: 8px;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.stat-card.stat-success { border-right: 4px solid #22c55e; }
.stat-card.stat-danger { border-right: 4px solid #ef4444; }
.stat-value { font-size: 1.5rem; font-weight: 700; color: var(--primary); }
.stat-label { color: #6b7280; font-size: 0.875rem; margin-top: 0.25rem; }
.grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
.card-body h3 { margin-bottom: 1rem; font-size: 1rem; color: var(--primary); }
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border);
}
.card-header .card-title { margin: 0; font-size: 1rem; color: var(--primary); }
.card-actions { display: flex; gap: 0.5rem; }
.btn-sm { padding: 6px 12px; font-size: 0.875rem; }
.table-sm td, .table-sm th { padding: 8px 12px; font-size: 0.875rem; }
.text-success { color: #22c55e; }
.text-danger { color: #ef4444; }
.text-muted { color: #6b7280; }
.text-center { text-align: center; }
.warehouses-list { display: flex; flex-wrap: wrap; gap: 0.75rem; }
.warehouse-item {
    background: var(--bg-light);
    padding: 0.5rem 1rem;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.warehouse-name { font-weight: 500; }
</style>
@endsection
