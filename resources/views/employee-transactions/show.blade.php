@extends('layouts.app')

@section('title', 'تفاصيل المعاملة')

@section('content')
<div class="page-header">
    <div>
        <h1>📄 تفاصيل المعاملة</h1>
        <p>{{ $employeeTransaction->transaction_number }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('employee-transactions.edit', $employeeTransaction) }}" class="btn">✏️ تعديل</a>
        <a href="{{ route('employee-transactions.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-body">
            <h3 style="margin-bottom: 16px;">📋 بيانات المعاملة</h3>

            <div class="info-grid">
                <div class="info-item">
                    <label>رقم المعاملة</label>
                    <span><code>{{ $employeeTransaction->transaction_number }}</code></span>
                </div>

                <div class="info-item">
                    <label>الموظف</label>
                    <span>
                        <a href="{{ route('employee-transactions.employee-history', $employeeTransaction->employee) }}">
                            {{ $employeeTransaction->employee->name }}
                        </a>
                    </span>
                </div>

                <div class="info-item">
                    <label>نوع المعاملة</label>
                    <span>
                        @php
                            $typeColors = [
                                'salary' => 'badge-success',
                                'advance' => 'badge-warning',
                                'bonus' => 'badge-info',
                                'deduction' => 'badge-danger',
                            ];
                        @endphp
                        <span class="badge {{ $typeColors[$employeeTransaction->type] ?? '' }}">
                            {{ $employeeTransaction->type_name }}
                        </span>
                    </span>
                </div>

                <div class="info-item">
                    <label>المبلغ</label>
                    <span class="amount {{ $employeeTransaction->isCredit() ? 'text-success' : 'text-danger' }}">
                        {{ $employeeTransaction->isCredit() ? '+' : '-' }}{{ number_format($employeeTransaction->amount, 2) }} ج.م
                    </span>
                </div>

                <div class="info-item">
                    <label>تاريخ المعاملة</label>
                    <span>{{ $employeeTransaction->transaction_date->format('Y/m/d') }}</span>
                </div>

                @if($employeeTransaction->month_year)
                <div class="info-item">
                    <label>شهر المرتب</label>
                    <span>{{ $employeeTransaction->month_year }}</span>
                </div>
                @endif

                <div class="info-item">
                    <label>طريقة الدفع</label>
                    <span>{{ $employeeTransaction->payment_method_name }}</span>
                </div>

                @if($employeeTransaction->reference_number)
                <div class="info-item">
                    <label>رقم المرجع</label>
                    <span>{{ $employeeTransaction->reference_number }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 style="margin-bottom: 16px;">📝 معلومات إضافية</h3>

            <div class="info-grid">
                @if($employeeTransaction->description)
                <div class="info-item full-width">
                    <label>الوصف</label>
                    <span>{{ $employeeTransaction->description }}</span>
                </div>
                @endif

                @if($employeeTransaction->notes)
                <div class="info-item full-width">
                    <label>ملاحظات</label>
                    <span>{{ $employeeTransaction->notes }}</span>
                </div>
                @endif

                <div class="info-item">
                    <label>بواسطة</label>
                    <span>{{ $employeeTransaction->creator->name ?? '-' }}</span>
                </div>

                <div class="info-item">
                    <label>تاريخ الإنشاء</label>
                    <span>{{ $employeeTransaction->created_at->format('Y/m/d H:i') }}</span>
                </div>

                @if($employeeTransaction->updated_at != $employeeTransaction->created_at)
                <div class="info-item">
                    <label>آخر تحديث</label>
                    <span>{{ $employeeTransaction->updated_at->format('Y/m/d H:i') }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.grid-2 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 1.5rem;
}
.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}
.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.info-item.full-width {
    grid-column: span 2;
}
.info-item label {
    color: var(--text-muted);
    font-size: 0.75rem;
    text-transform: uppercase;
}
.info-item span {
    font-size: 1rem;
}
.amount {
    font-size: 1.25rem;
    font-weight: 700;
}
.badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; }
.badge-success { background: #10b981; color: white; }
.badge-warning { background: #f59e0b; color: white; }
.badge-info { background: #3b82f6; color: white; }
.badge-danger { background: #ef4444; color: white; }
.text-success { color: #10b981; }
.text-danger { color: #ef4444; }
</style>
@endsection
