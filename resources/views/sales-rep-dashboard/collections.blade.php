@extends('layouts.app')

@section('title', 'تحصيلاتي')

@section('content')
<div class="page-header">
    <div>
        <h1>تحصيلاتي</h1>
        <p>قائمة التحصيلات التي قمت بها</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('sales-rep.dashboard') }}" class="btn">← رجوع</a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('sales-rep.collections') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;">
            <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 150px;">
                <label class="form-label">من تاريخ</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 150px;">
                <label class="form-label">إلى تاريخ</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <button type="submit" class="btn btn-primary">بحث</button>
            <a href="{{ route('sales-rep.collections') }}" class="btn">إعادة تعيين</a>
        </form>
    </div>
</div>

<!-- Total Collections -->
<div class="card mb-4" style="background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white;">
    <div class="card-body" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h3 style="margin: 0; font-size: 18px;">إجمالي التحصيلات</h3>
            <p style="margin: 5px 0 0; opacity: 0.8; font-size: 14px;">حسب الفلترة الحالية</p>
        </div>
        <div style="font-size: 28px; font-weight: bold;">
            {{ number_format($totalCollections) }} ج.م
        </div>
    </div>
</div>

<div class="card">
    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>رقم الإيصال</th>
                    <th>العميل/المورد</th>
                    <th>التاريخ</th>
                    <th>طريقة الدفع</th>
                    <th>المبلغ</th>
                    <th>الحالة</th>
                    <th>ملاحظات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                <tr>
                    <td><strong>{{ $payment->payment_number }}</strong></td>
                    <td>{{ $payment->payable?->name ?? '-' }}</td>
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
                    <td><strong class="text-success">{{ number_format($payment->amount) }} ج.م</strong></td>
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
                    <td>{{ Str::limit($payment->notes, 30) ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <div class="empty-state-icon">💳</div>
                            <h3>لا توجد تحصيلات</h3>
                            <p>لم تقم بأي تحصيلات بعد</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($payments->hasPages())
    <div class="pagination">
        {{ $payments->links() }}
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .mb-4 { margin-bottom: 20px; }
</style>
@endpush
