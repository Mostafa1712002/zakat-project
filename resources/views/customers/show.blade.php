@extends('layouts.app')

@section('title', 'عرض العميل')

@section('content')
<div class="page-header">
    <div>
        <h1>👥 {{ $customer->name }}</h1>
        <p>بيانات العميل وآخر تعاملات</p>
    </div>
    <div class="header-actions">
        @if($customer->current_balance > 0)
            <a href="{{ route('customers.collect.form', $customer) }}" class="btn btn-primary">💰 تحصيل</a>
        @endif
        <a href="{{ route('customers.edit', $customer) }}" class="btn">تعديل</a>
        <a href="{{ route('customers.index') }}" class="btn">← رجوع</a>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-body">
            <h3>بيانات العميل</h3>
            <p><strong>الكود:</strong> {{ $customer->code ?? '-' }}</p>
            <p><strong>البريد:</strong> {{ $customer->email ?? '-' }}</p>
            <p><strong>الهاتف:</strong> {{ $customer->phone ?? '-' }}</p>
            <p><strong>الموبايل:</strong> {{ $customer->mobile ?? '-' }}</p>
            <p><strong>الفرع:</strong> {{ $customer->branch->name ?? '-' }}</p>
            <p><strong>المندوب:</strong> {{ $customer->salesRep->name ?? '-' }}</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <h3>إعدادات مالية</h3>
            @php
                $typeLabel = match ($customer->type) {
                    'wholesale' => 'جملة',
                    'corporate' => 'شركة',
                    default => 'قطاعي',
                };
            @endphp
            <p><strong>نوع العميل:</strong> {{ $typeLabel }}</p>
            <p><strong>سقف الائتمان:</strong> {{ number_format($customer->credit_limit ?? 0, 2) }} ج.م</p>
            <p><strong>الرصيد الحالي:</strong> {{ number_format($customer->current_balance ?? 0, 2) }} ج.م</p>
            <p><strong>مدة السداد:</strong> {{ $customer->payment_terms_days ?? '-' }} يوم</p>
            <p><strong>الحالة:</strong>
                @if($customer->is_active)
                    <span class="badge badge-success">نشط</span>
                @else
                    <span class="badge badge-danger">غير نشط</span>
                @endif
            </p>
        </div>
    </div>
</div>

@if($customer->address)
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-body">
        <h3>العنوان</h3>
        <p>{{ $customer->address }}{{ $customer->city ? ' - ' . $customer->city : '' }}</p>
    </div>
</div>
@endif

@if($customer->notes)
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-body">
        <h3>ملاحظات</h3>
        <p>{{ $customer->notes }}</p>
    </div>
</div>
@endif

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h2>🧾 آخر الفواتير</h2>
    </div>
    <div class="table-container  overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>#</th>
                    <th>رقم الفاتورة</th>
                    <th>التاريخ</th>
                    <th>الإجمالي</th>
                    <th>الحالة</th>
                    <th>حالة الدفع</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customer->sales as $index => $sale)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><code>{{ $sale->invoice_number }}</code></td>
                    <td>{{ $sale->invoice_date?->format('Y-m-d') ?? '-' }}</td>
                    <td>{{ number_format($sale->total_amount, 2) }} ج.م</td>
                    <td>{{ $sale->status }}</td>
                    <td>{{ $sale->payment_status }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div class="empty-state-icon">🧾</div>
                            <h3>لا توجد فواتير</h3>
                            <p>لم يتم تسجيل أي فواتير لهذا العميل بعد</p>
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
        @if($customer->current_balance > 0)
            <a href="{{ route('customers.collect.form', $customer) }}" class="btn btn-sm btn-primary">+ تحصيل جديد</a>
        @endif
    </div>
    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
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
                @forelse($customer->payments()->latest()->take(10)->get() as $payment)
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
                            <p>لم يتم تسجيل أي تحصيلات لهذا العميل بعد</p>
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
