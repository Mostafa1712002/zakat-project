@extends('layouts.app')

@section('title', 'تفاصيل الدفعة - ' . $payment->payment_number)

@section('content')
<div class="page-header">
    <div>
        <h1>{{ $payment->payment_number }}</h1>
        <p>{{ $payment->type === 'received' ? 'إيصال تحصيل' : 'إيصال دفع' }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('payments.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">تفاصيل الدفعة</h3>
        <span class="badge badge-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">
            @switch($payment->status)
                @case('completed') مكتمل @break
                @case('pending') معلق @break
                @case('cancelled') ملغي @break
                @case('bounced') مرتد @break
                @default -
            @endswitch
        </span>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <div>
                <span class="text-muted">رقم الإيصال:</span>
                <strong>{{ $payment->payment_number }}</strong>
            </div>
            <div>
                <span class="text-muted">النوع:</span>
                <strong>{{ $payment->type === 'received' ? 'تحصيل من عميل' : 'دفع لمورد' }}</strong>
            </div>
            <div>
                <span class="text-muted">{{ $payment->type === 'received' ? 'العميل' : 'المورد' }}:</span>
                <strong>{{ $payment->payable?->name ?? '-' }}</strong>
            </div>
            <div>
                <span class="text-muted">الفاتورة:</span>
                <strong>
                    <span class="text-muted">على الحساب</span>
                </strong>
            </div>
            <div>
                <span class="text-muted">المبلغ:</span>
                <strong class="{{ $payment->type === 'received' ? 'text-success' : 'text-danger' }}" style="font-size: 18px;">
                    {{ number_format($payment->amount) }} ج.م
                </strong>
            </div>
            <div>
                <span class="text-muted">تاريخ الدفع:</span>
                <strong>{{ $payment->payment_date->format('Y-m-d') }}</strong>
            </div>
            <div>
                <span class="text-muted">طريقة الدفع:</span>
                <strong>
                    @switch($payment->method)
                        @case('cash') نقدي @break
                        @case('bank_transfer') تحويل بنكي @break
                        @case('instapay') انستا باي @break
                        @case('vodafone_cash') فودافون كاش @break
                        @case('check') شيك @break
                        @case('card') بطاقة @break
                        @default أخرى
                    @endswitch
                </strong>
            </div>

            @if($payment->reference_number)
            <div>
                <span class="text-muted">رقم المرجع:</span>
                <strong>{{ $payment->reference_number }}</strong>
            </div>
            @endif

            @if($payment->method === 'check')
            <div>
                <span class="text-muted">رقم الشيك:</span>
                <strong>{{ $payment->check_number ?? '-' }}</strong>
            </div>
            <div>
                <span class="text-muted">تاريخ استحقاق الشيك:</span>
                <strong>{{ $payment->check_date?->format('Y-m-d') ?? '-' }}</strong>
            </div>
            @endif

            @if($payment->bank_name || $payment->bank_account)
            <div>
                <span class="text-muted">البنك:</span>
                <strong>{{ $payment->bank_name ?? '-' }}</strong>
            </div>
            <div>
                <span class="text-muted">رقم الحساب:</span>
                <strong>{{ $payment->bank_account ?? '-' }}</strong>
            </div>
            @endif

            <div>
                <span class="text-muted">الفرع:</span>
                <strong>{{ $payment->branch?->name ?? '-' }}</strong>
            </div>
            <div>
                <span class="text-muted">المستخدم:</span>
                <strong>{{ $payment->user?->name ?? '-' }}</strong>
            </div>

            <div>
                <span class="text-muted">تاريخ الإنشاء:</span>
                <strong>{{ $payment->created_at->format('Y-m-d H:i') }}</strong>
            </div>
        </div>

        @if($payment->notes)
        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border);">
            <span class="text-muted">ملاحظات:</span>
            <p style="margin-top: 8px;">{{ $payment->notes }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
