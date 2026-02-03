@extends('layouts.app')

@section('title', 'سحب من خزينة المندوب')

@section('content')
<div class="page-header">
    <div>
        <h1>سحب من الخزينة</h1>
        <p>المندوب: {{ $salesRep->name }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('sales-reps.show', $salesRep) }}" class="btn">← رجوع</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="treasury-info">
            <span class="label">الرصيد الحالي:</span>
            <span class="value {{ $salesRep->treasury_balance >= 0 ? 'text-success' : 'text-danger' }}">
                {{ number_format($salesRep->treasury_balance, 2) }} ج.م
            </span>
        </div>

        @if($salesRep->treasury_balance <= 0)
            <div class="alert alert-warning">
                لا يوجد رصيد كافي للسحب
            </div>
        @else
        <form action="{{ route('sales-reps.withdraw', $salesRep) }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="amount" class="form-label">المبلغ *</label>
                <input type="number" step="0.01" name="amount" id="amount" class="form-control"
                       value="{{ old('amount') }}" min="0.01" max="{{ $salesRep->treasury_balance }}" required autofocus>
                <small class="text-muted">الحد الأقصى: {{ number_format($salesRep->treasury_balance, 2) }} ج.م</small>
                @error('amount')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="description" class="form-label">الوصف</label>
                <input type="text" name="description" id="description" class="form-control"
                       value="{{ old('description') }}" placeholder="مثال: تسوية نهاية اليوم">
                @error('description')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-warning">تأكيد السحب</button>
                <a href="{{ route('sales-reps.show', $salesRep) }}" class="btn">إلغاء</a>
            </div>
        </form>
        @endif
    </div>
</div>

<style>
.treasury-info {
    background: var(--bg-light);
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.treasury-info .label { font-weight: 500; }
.treasury-info .value { font-size: 1.25rem; font-weight: 700; }
.text-success { color: #22c55e; }
.text-danger { color: #ef4444; }
.text-muted { color: #6b7280; font-size: 12px; display: block; margin-top: 4px; }
.alert-warning { background: #fef3c7; color: #92400e; padding: 1rem; border-radius: 8px; }
</style>
@endsection
