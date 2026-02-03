@extends('layouts.app')

@section('title', 'عملائي')

@section('content')
<div class="page-header">
    <div>
        <h1>عملائي</h1>
        <p>قائمة العملاء التابعين لك</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('sales-rep.dashboard') }}" class="btn">← رجوع</a>
        <a href="{{ route('customers.create') }}" class="btn btn-primary">+ إضافة عميل</a>
    </div>
</div>

<div class="card">
    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>#</th>
                    <th>العميل</th>
                    <th>الهاتف</th>
                    <th>عدد الفواتير</th>
                    <th>إجمالي المبيعات</th>
                    <th>الرصيد المستحق</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                <tr>
                    <td>{{ $customer->id }}</td>
                    <td>
                        <strong>{{ $customer->name }}</strong>
                        <br>
                        <small class="text-muted">{{ $customer->code }}</small>
                    </td>
                    <td>{{ $customer->phone ?? $customer->mobile ?? '-' }}</td>
                    <td>{{ $customer->sales_count ?? 0 }}</td>
                    <td>{{ number_format($customer->sales_sum_total_amount ?? 0) }} ج.م</td>
                    <td>
                        @if($customer->current_balance > 0)
                        <span class="badge badge-danger">{{ number_format($customer->current_balance) }} ج.م</span>
                        @else
                        <span class="badge badge-success">0 ج.م</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $customer->is_active ? 'badge-success' : 'badge-secondary' }}">
                            {{ $customer->is_active ? 'نشط' : 'غير نشط' }}
                        </span>
                    </td>
                    <td>
                        <div class="table-actions">
                            <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm">عرض</a>
                            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm">تعديل</a>
                            @if($customer->current_balance > 0)
                            <a href="{{ route('customers.collect', $customer) }}" class="btn btn-sm btn-primary">تحصيل</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <div class="empty-state-icon">👥</div>
                            <h3>لا يوجد عملاء</h3>
                            <p>ابدأ بإضافة عميل جديد</p>
                            <a href="{{ route('customers.create') }}" class="btn btn-primary">+ إضافة عميل</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($customers->hasPages())
    <div class="pagination">
        {{ $customers->links() }}
    </div>
    @endif
</div>
@endsection
