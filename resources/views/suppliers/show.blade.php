@extends('layouts.app')

@section('title', 'عرض المورد')

@section('content')
<div class="page-header">
    <div>
        <h1>🏭 {{ $supplier->name }}</h1>
        <p>تفاصيل المورد</p>
    </div>
    <div class="header-actions">
        @if($supplier->current_balance > 0)
            <a href="{{ route('suppliers.pay.form', $supplier) }}" class="btn btn-primary">💰 دفع</a>
        @endif
        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn">تعديل</a>
        <a href="{{ route('suppliers.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-body">
            <h3>بيانات المورد</h3>
            <p><strong>الكود:</strong> {{ $supplier->code ?? '-' }}</p>
            <p><strong>البريد:</strong> {{ $supplier->email ?? '-' }}</p>
            <p><strong>الهاتف:</strong> {{ $supplier->phone ?? '-' }}</p>
            <p><strong>الموبايل:</strong> {{ $supplier->mobile ?? '-' }}</p>
            <p><strong>جهة الاتصال:</strong> {{ $supplier->contact_person ?? '-' }}</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <h3>معلومات مالية</h3>
            <p><strong>الرصيد الحالي:</strong> {{ number_format($supplier->current_balance ?? 0, 2) }} ج.م</p>
            <p><strong>مدة السداد:</strong> {{ $supplier->payment_terms_days ?? '-' }} يوم</p>
            <p><strong>البنك:</strong> {{ $supplier->bank_name ?? '-' }}</p>
            <p><strong>رقم الحساب:</strong> {{ $supplier->bank_account ?? '-' }}</p>
            <p><strong>الحالة:</strong>
                @if($supplier->is_active)
                    <span class="badge badge-success">نشط</span>
                @else
                    <span class="badge badge-danger">غير نشط</span>
                @endif
            </p>
        </div>
    </div>
</div>

@if($supplier->address)
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-body">
        <h3>العنوان</h3>
        <p>{{ $supplier->address }}{{ $supplier->city ? ' - ' . $supplier->city : '' }}</p>
    </div>
</div>
@endif

@if($supplier->notes)
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-body">
        <h3>ملاحظات</h3>
        <p>{{ $supplier->notes }}</p>
    </div>
</div>
@endif

<!-- Purchases History -->
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h2>🧾 آخر المشتريات</h2>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>رقم الفاتورة</th>
                    <th>التاريخ</th>
                    <th>الإجمالي</th>
                    <th>المدفوع</th>
                    <th>المتبقي</th>
                </tr>
            </thead>
            <tbody>
                @forelse($supplier->purchases()->latest()->take(10)->get() as $index => $purchase)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><code>{{ $purchase->invoice_number }}</code></td>
                    <td>{{ $purchase->invoice_date?->format('Y-m-d') ?? '-' }}</td>
                    <td>{{ number_format($purchase->total_amount, 2) }} ج.م</td>
                    <td>{{ number_format($purchase->paid_amount, 2) }} ج.م</td>
                    <td>
                        @if($purchase->remaining_amount > 0)
                            <span class="text-danger">{{ number_format($purchase->remaining_amount, 2) }} ج.م</span>
                        @else
                            <span class="text-success">0.00 ج.م</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div class="empty-state-icon">🧾</div>
                            <h3>لا توجد مشتريات</h3>
                            <p>لم يتم تسجيل أي فواتير شراء من هذا المورد بعد</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Payment History -->
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h2>💳 سجل المدفوعات</h2>
        @if($supplier->current_balance > 0)
            <a href="{{ route('suppliers.pay.form', $supplier) }}" class="btn btn-sm btn-primary">+ دفعة جديدة</a>
        @endif
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>رقم الإيصال</th>
                    <th>التاريخ</th>
                    <th>المبلغ</th>
                    <th>طريقة الدفع</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($supplier->payments()->latest()->take(10)->get() as $payment)
                <tr>
                    <td><code>{{ $payment->payment_number }}</code></td>
                    <td>{{ $payment->payment_date?->format('Y-m-d') ?? '-' }}</td>
                    <td>{{ number_format($payment->amount, 2) }} ج.م</td>
                    <td>
                        @php
                            $methodLabels = [
                                'cash' => 'نقدي',
                                'bank_transfer' => 'تحويل بنكي',
                                'instapay' => 'انستا باي',
                                'vodafone_cash' => 'فودافون كاش',
                                'check' => 'شيك',
                                'card' => 'بطاقة',
                                'other' => 'أخرى',
                            ];
                        @endphp
                        {{ $methodLabels[$payment->method] ?? $payment->method }}
                    </td>
                    <td>
                        @if($payment->status === 'completed')
                            <span class="badge badge-success">مكتمل</span>
                        @elseif($payment->status === 'pending')
                            <span class="badge badge-warning">معلق</span>
                        @elseif($payment->status === 'bounced')
                            <span class="badge badge-danger">مرتجع</span>
                        @else
                            <span class="badge">{{ $payment->status }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <div class="empty-state-icon">💳</div>
                            <h3>لا توجد مدفوعات</h3>
                            <p>لم يتم تسجيل أي مدفوعات لهذا المورد بعد</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
.grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
.card-body h3 { margin-bottom: 1rem; font-size: 1rem; color: var(--primary); }
</style>
@endsection
