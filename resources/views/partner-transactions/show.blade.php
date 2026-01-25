@extends('layouts.app')

@section('title', 'تفاصيل المعاملة')

@section('content')
<div class="page-header">
    <div>
        <h1>📄 تفاصيل المعاملة</h1>
        <p>{{ $partnerTransaction->transaction_number }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('partner-transactions.edit', $partnerTransaction) }}" class="btn">✏️ تعديل</a>
        <a href="{{ route('partner-transactions.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-body">
            <h3 style="margin-bottom: 16px;">📋 بيانات المعاملة</h3>

            <div class="info-grid">
                <div class="info-item">
                    <label>رقم المعاملة</label>
                    <span><code>{{ $partnerTransaction->transaction_number }}</code></span>
                </div>

                <div class="info-item">
                    <label>الشريك</label>
                    <span>
                        <a href="{{ route('partner-transactions.partner-history', $partnerTransaction->partner) }}">
                            {{ $partnerTransaction->partner->name }}
                        </a>
                    </span>
                </div>

                <div class="info-item">
                    <label>نوع المعاملة</label>
                    <span>
                        @php
                            $typeColors = [
                                'withdrawal' => 'badge-warning',
                                'profit_share' => 'badge-success',
                                'investment' => 'badge-info',
                                'return' => 'badge-danger',
                            ];
                        @endphp
                        <span class="badge {{ $typeColors[$partnerTransaction->type] ?? '' }}">
                            {{ $partnerTransaction->type_name }}
                        </span>
                    </span>
                </div>

                <div class="info-item">
                    <label>المبلغ</label>
                    <span class="amount {{ $partnerTransaction->isCredit() ? 'text-success' : 'text-danger' }}">
                        {{ $partnerTransaction->isCredit() ? '+' : '-' }}{{ number_format($partnerTransaction->amount, 2) }} ج.م
                    </span>
                </div>

                <div class="info-item">
                    <label>تاريخ المعاملة</label>
                    <span>{{ $partnerTransaction->transaction_date->format('Y/m/d') }}</span>
                </div>

                @if($partnerTransaction->period)
                <div class="info-item">
                    <label>الفترة</label>
                    <span>{{ $partnerTransaction->period }}</span>
                </div>
                @endif

                <div class="info-item">
                    <label>طريقة الدفع</label>
                    <span>{{ $partnerTransaction->payment_method_name }}</span>
                </div>

                @if($partnerTransaction->reference_number)
                <div class="info-item">
                    <label>رقم المرجع</label>
                    <span>{{ $partnerTransaction->reference_number }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 style="margin-bottom: 16px;">📝 معلومات إضافية</h3>

            <div class="info-grid">
                @if($partnerTransaction->description)
                <div class="info-item full-width">
                    <label>الوصف</label>
                    <span>{{ $partnerTransaction->description }}</span>
                </div>
                @endif

                @if($partnerTransaction->notes)
                <div class="info-item full-width">
                    <label>ملاحظات</label>
                    <span>{{ $partnerTransaction->notes }}</span>
                </div>
                @endif

                <div class="info-item">
                    <label>بواسطة</label>
                    <span>{{ $partnerTransaction->creator->name ?? '-' }}</span>
                </div>

                <div class="info-item">
                    <label>تاريخ الإنشاء</label>
                    <span>{{ $partnerTransaction->created_at->format('Y/m/d H:i') }}</span>
                </div>

                @if($partnerTransaction->updated_at != $partnerTransaction->created_at)
                <div class="info-item">
                    <label>آخر تحديث</label>
                    <span>{{ $partnerTransaction->updated_at->format('Y/m/d H:i') }}</span>
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
