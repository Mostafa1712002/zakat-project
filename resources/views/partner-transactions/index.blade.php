@extends('layouts.app')

@section('title', 'سجل معاملات الشركاء')

@section('content')
<div class="page-header">
    <div>
        <h1>💰 سجل معاملات الشركاء</h1>
        <p>إدارة سحوبات وأرباح الشركاء</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('partner-transactions.create', ['type' => 'withdrawal']) }}" class="btn btn-warning">+ سحب أرباح</a>
        <a href="{{ route('partner-transactions.create', ['type' => 'profit_share']) }}" class="btn btn-success">+ توزيع أرباح</a>
        <a href="{{ route('partner-transactions.create') }}" class="btn btn-primary">+ معاملة جديدة</a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<!-- Summary Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: #f59e0b;">💸</div>
        <div class="stat-content">
            <div class="stat-value">{{ number_format($totals['withdrawals'], 2) }}</div>
            <div class="stat-label">إجمالي السحوبات</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #10b981;">📊</div>
        <div class="stat-content">
            <div class="stat-value">{{ number_format($totals['profit_shares'], 2) }}</div>
            <div class="stat-label">إجمالي الأرباح الموزعة</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #3b82f6;">💵</div>
        <div class="stat-content">
            <div class="stat-value">{{ number_format($totals['investments'], 2) }}</div>
            <div class="stat-label">إجمالي الاستثمارات المضافة</div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form action="{{ route('partner-transactions.index') }}" method="GET" class="filter-form">
            <div class="filter-row">
                <select name="partner_id" class="form-control">
                    <option value="">-- كل الشركاء --</option>
                    @foreach($partners as $partner)
                        <option value="{{ $partner->id }}" {{ request('partner_id') == $partner->id ? 'selected' : '' }}>
                            {{ $partner->name }}
                        </option>
                    @endforeach
                </select>
                <select name="type" class="form-control">
                    <option value="">-- كل الأنواع --</option>
                    <option value="withdrawal" {{ request('type') == 'withdrawal' ? 'selected' : '' }}>سحب أرباح</option>
                    <option value="profit_share" {{ request('type') == 'profit_share' ? 'selected' : '' }}>توزيع أرباح</option>
                    <option value="investment" {{ request('type') == 'investment' ? 'selected' : '' }}>إضافة رأس مال</option>
                    <option value="return" {{ request('type') == 'return' ? 'selected' : '' }}>إرجاع رأس مال</option>
                </select>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="من تاريخ">
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="إلى تاريخ">
                <button type="submit" class="btn btn-primary">🔍 بحث</button>
                <a href="{{ route('partner-transactions.index') }}" class="btn">إعادة تعيين</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-container  overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>رقم المعاملة</th>
                    <th>الشريك</th>
                    <th>النوع</th>
                    <th>المبلغ</th>
                    <th>التاريخ</th>
                    <th>الفترة</th>
                    <th>طريقة الدفع</th>
                    <th>الوصف</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                <tr>
                    <td><code>{{ $transaction->transaction_number }}</code></td>
                    <td>
                        <a href="{{ route('partner-transactions.partner-history', $transaction->partner) }}">
                            <strong>{{ $transaction->partner->name }}</strong>
                        </a>
                    </td>
                    <td>
                        @php
                            $typeColors = [
                                'withdrawal' => 'badge-warning',
                                'profit_share' => 'badge-success',
                                'investment' => 'badge-info',
                                'return' => 'badge-danger',
                            ];
                        @endphp
                        <span class="badge {{ $typeColors[$transaction->type] ?? '' }}">
                            {{ $transaction->type_name }}
                        </span>
                    </td>
                    <td class="{{ $transaction->isCredit() ? 'text-success' : 'text-danger' }}">
                        {{ $transaction->isCredit() ? '+' : '-' }}{{ number_format($transaction->amount, 2) }} ج.م
                    </td>
                    <td>{{ $transaction->transaction_date->format('Y/m/d') }}</td>
                    <td>{{ $transaction->period ?? '-' }}</td>
                    <td>{{ $transaction->payment_method_name }}</td>
                    <td class="text-muted">{{ Str::limit($transaction->description, 30) ?? '-' }}</td>
                    <td>
                        <div class="table-actions">
                            <a href="{{ route('partner-transactions.show', $transaction) }}" class="btn btn-sm" title="عرض">👁️</a>
                            <a href="{{ route('partner-transactions.edit', $transaction) }}" class="btn btn-sm" title="تعديل">✏️</a>
                            <form action="{{ route('partner-transactions.destroy', $transaction) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذه المعاملة؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="حذف">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <div class="empty-state-icon">💰</div>
                            <h3>لا توجد معاملات</h3>
                            <p>ابدأ بتسجيل معاملة جديدة</p>
                            <a href="{{ route('partner-transactions.create') }}" class="btn btn-primary">+ معاملة جديدة</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($transactions->hasPages())
    <div class="card-footer">
        {{ $transactions->withQueryString()->links() }}
    </div>
    @endif
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
.stat-value { font-size: 1.5rem; font-weight: 700; }
.stat-label { color: var(--text-muted); font-size: 0.875rem; }
.filter-form { margin-bottom: 0; }
.filter-row { display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; }
.filter-row .form-control { flex: 1; min-width: 150px; }
.filter-row .btn { white-space: nowrap; }
.table-actions { display: flex; gap: 0.25rem; }
.badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; }
.badge-success { background: #10b981; color: white; }
.badge-warning { background: #f59e0b; color: white; }
.badge-info { background: #3b82f6; color: white; }
.badge-danger { background: #ef4444; color: white; }
.text-success { color: #10b981; }
.text-danger { color: #ef4444; }
.alert { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
.alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
</style>
@endsection
