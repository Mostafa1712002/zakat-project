@extends('layouts.app')

@section('title', 'فواتيري')

@section('content')
<div class="page-header">
    <div>
        <h1>فواتيري</h1>
        <p>قائمة فواتير المبيعات الخاصة بك</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('sales-rep.dashboard') }}" class="btn">← رجوع</a>
        <a href="{{ route('sales.create') }}" class="btn btn-primary">+ فاتورة جديدة</a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('sales-rep.sales') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;">
            <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 150px;">
                <label class="form-label">الحالة</label>
                <select name="status" class="form-control">
                    <option value="">الكل</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>مسودة</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>مؤكد</option>
                    <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>تم التسليم</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>ملغي</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 150px;">
                <label class="form-label">حالة الدفع</label>
                <select name="payment_status" class="form-control">
                    <option value="">الكل</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>غير مدفوع</option>
                    <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>جزئي</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>مدفوع</option>
                    <option value="overdue" {{ request('payment_status') === 'overdue' ? 'selected' : '' }}>متأخر</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 150px;">
                <label class="form-label">من تاريخ</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 150px;">
                <label class="form-label">إلى تاريخ</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <button type="submit" class="btn btn-primary">بحث</button>
            <a href="{{ route('sales-rep.sales') }}" class="btn">إعادة تعيين</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>رقم الفاتورة</th>
                    <th>العميل</th>
                    <th>التاريخ</th>
                    <th>المخزن</th>
                    <th>الإجمالي</th>
                    <th>المدفوع</th>
                    <th>المتبقي</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                <tr>
                    <td><strong class="text-primary">{{ $sale->invoice_number }}</strong></td>
                    <td>{{ $sale->customer?->name ?? 'عميل نقدي' }}</td>
                    <td>{{ $sale->invoice_date->format('Y-m-d') }}</td>
                    <td>{{ $sale->warehouse?->name ?? '-' }}</td>
                    <td>{{ number_format($sale->total_amount) }} ج.م</td>
                    <td>{{ number_format($sale->paid_amount) }} ج.م</td>
                    <td>
                        @if($sale->remaining_amount > 0)
                        <span class="text-danger">{{ number_format($sale->remaining_amount) }} ج.م</span>
                        @else
                        <span class="text-success">0 ج.م</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-{{ $sale->payment_status === 'paid' ? 'success' : ($sale->payment_status === 'partial' ? 'warning' : 'danger') }}">
                            @switch($sale->payment_status)
                                @case('paid') مدفوع @break
                                @case('partial') جزئي @break
                                @case('overdue') متأخر @break
                                @default غير مدفوع
                            @endswitch
                        </span>
                    </td>
                    <td>
                        <div class="table-actions">
                            <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm">عرض</a>
                            @if($sale->status === 'draft')
                            <a href="{{ route('sales.edit', $sale) }}" class="btn btn-sm">تعديل</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <div class="empty-state-icon">📄</div>
                            <h3>لا توجد فواتير</h3>
                            <p>ابدأ بإنشاء فاتورة جديدة</p>
                            <a href="{{ route('sales.create') }}" class="btn btn-primary">+ فاتورة جديدة</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($sales->hasPages())
    <div class="pagination">
        {{ $sales->links() }}
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .mb-4 { margin-bottom: 20px; }
</style>
@endpush
