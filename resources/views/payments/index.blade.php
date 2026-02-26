@extends('layouts.app')

@section('title', 'التحصيلات والمدفوعات')

@section('content')
<div class="page-header">
    <div>
        <h1>التحصيلات والمدفوعات</h1>
        <p>قائمة جميع التحصيلات من العملاء والمدفوعات للموردين</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('customers.index') }}" class="btn">تحصيل من عميل</a>
        @can('pay_suppliers')
        <a href="{{ route('suppliers.index') }}" class="btn">دفع لمورد</a>
        @endcan
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form action="{{ route('payments.index') }}" method="GET" class="filter-form">
            <div class="filter-row">
                <select name="type" class="form-control">
                    <option value="">كل الأنواع</option>
                    <option value="received" {{ request('type') === 'received' ? 'selected' : '' }}>تحصيلات</option>
                    <option value="paid" {{ request('type') === 'paid' ? 'selected' : '' }}>مدفوعات</option>
                </select>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                <button type="submit" class="btn btn-primary">بحث</button>
                <a href="{{ route('payments.index') }}" class="btn">إعادة تعيين</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>رقم الإيصال</th>
                    <th>النوع</th>
                    <th>العميل/المورد</th>
                    <th>الفاتورة</th>
                    <th>التاريخ</th>
                    <th>طريقة الدفع</th>
                    <th>المبلغ</th>
                    <th>الحالة</th>
                    <th>المندوب</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                <tr>
                    <td><strong>{{ $payment->payment_number }}</strong></td>
                    <td>
                        <span class="badge {{ $payment->type === 'received' ? 'badge-success' : 'badge-warning' }}">
                            {{ $payment->type === 'received' ? 'تحصيل' : 'دفع' }}
                        </span>
                    </td>
                    <td>{{ $payment->payable?->name ?? '-' }}</td>
                    <td>
                        @if($payment->sale)
                            <a href="{{ route('sales.show', $payment->sale) }}" class="text-primary">
                                <code>{{ $payment->sale->invoice_number }}</code>
                            </a>
                        @elseif($payment->purchase)
                            <a href="{{ route('purchases.show', $payment->purchase) }}" class="text-primary">
                                <code>{{ $payment->purchase->invoice_number }}</code>
                            </a>
                        @else
                            <span class="text-muted">على الحساب</span>
                        @endif
                    </td>
                    <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                    <td>
                        @switch($payment->method)
                            @case('cash') نقدي @break
                            @case('bank_transfer') تحويل بنكي @break
                            @case('instapay') انستا باي @break
                            @case('vodafone_cash') فودافون كاش @break
                            @case('check') شيك @break
                            @case('card') بطاقة @break
                            @default أخرى
                        @endswitch
                    </td>
                    <td>
                        <strong class="{{ $payment->type === 'received' ? 'text-success' : 'text-danger' }}">
                            {{ number_format($payment->amount) }} ج.م
                        </strong>
                    </td>
                    <td>
                        <span class="badge badge-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">
                            @switch($payment->status)
                                @case('completed') مكتمل @break
                                @case('pending') معلق @break
                                @case('cancelled') ملغي @break
                                @case('bounced') مرتد @break
                                @default -
                            @endswitch
                        </span>
                    </td>
                    <td>{{ $payment->salesRep?->name ?? '-' }}</td>
                    <td>
                        <a href="{{ route('payments.show', $payment) }}" class="btn btn-sm">عرض</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10">
                        <div class="empty-state">
                            <div class="empty-state-icon">💳</div>
                            <h3>لا توجد تحصيلات أو مدفوعات</h3>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($payments->hasPages())
    <div class="card-footer">
        {{ $payments->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
