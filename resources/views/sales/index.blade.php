@extends('layouts.app')

@section('title', 'المبيعات')

@section('content')
<div class="page-header">
    <div>
        <h1>💰 المبيعات</h1>
        <p>إدارة فواتير وعمليات البيع</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('sales.create') }}" class="btn btn-primary">+ فاتورة جديدة</a>
    </div>
</div>

<div class="card">
    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>#</th>
                    <th>رقم الفاتورة</th>
                    <th>العميل</th>
                    <th>الإجمالي</th>
                    <th>المدفوع</th>
                    <th>المتبقي</th>
                    <th>حالة الفاتورة</th>
                    <th>حالة الدفع</th>
                    <th>التاريخ</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                <tr>
                    <td>{{ $sale->id }}</td>
                    <td><code>{{ $sale->invoice_number }}</code></td>
                    <td><strong>{{ $sale->customer->name ?? '-' }}</strong></td>
                    <td>{{ number_format($sale->total_amount, 2) }} ج.م</td>
                    <td>{{ number_format($sale->paid_amount, 2) }} ج.م</td>
                    <td>{{ number_format($sale->remaining_amount, 2) }} ج.م</td>
                    <td>
                        @switch($sale->status)
                            @case('draft')
                                <span class="badge text-muted">مسودة</span>
                                @break
                            @case('confirmed')
                                <span class="badge badge-primary">مؤكدة</span>
                                @break
                            @case('delivered')
                                <span class="badge badge-success">تم التسليم</span>
                                @break
                            @case('cancelled')
                                <span class="badge badge-danger">ملغاة</span>
                                @break
                            @default
                                <span class="badge text-muted">{{ $sale->status }}</span>
                        @endswitch
                    </td>
                    <td>
                        @switch($sale->payment_status)
                            @case('unpaid')
                                <span class="badge badge-danger">غير مدفوعة</span>
                                @break
                            @case('partial')
                                <span class="badge badge-warning">جزئي</span>
                                @break
                            @case('paid')
                                <span class="badge badge-success">مدفوعة</span>
                                @break
                            @case('overdue')
                                <span class="badge badge-warning">متأخرة</span>
                                @break
                            @default
                                <span class="badge text-muted">{{ $sale->payment_status }}</span>
                        @endswitch
                    </td>
                    <td>{{ $sale->invoice_date?->format('Y/m/d') ?? $sale->created_at->format('Y/m/d') }}</td>
                    <td>
                        <div class="table-actions">
                            <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm">عرض</a>
                            <a href="{{ route('sales.edit', $sale) }}" class="btn btn-sm">تعديل</a>
                            <form action="{{ route('sales.destroy', $sale) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <div class="empty-state-icon">💰</div>
                            <h3>لا توجد مبيعات</h3>
                            <p>ابدأ بإنشاء فاتورة بيع جديدة</p>
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
