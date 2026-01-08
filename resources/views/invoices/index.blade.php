@extends('layouts.app')

@section('title', 'الفواتير')

@section('content')
<div class="page-header">
    <div>
        <h1>🧾 الفواتير</h1>
        <p>إدارة فواتير المبيعات</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('invoices.create') }}" class="btn btn-primary">+ فاتورة جديدة</a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <form action="{{ route('invoices.index') }}" method="GET" class="filter-form">
            <div class="filter-row">
                <input type="text" name="search" class="form-control" placeholder="بحث برقم الفاتورة أو اسم العميل..." value="{{ request('search') }}">
                <select name="status" class="form-control">
                    <option value="">كل الحالات</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>مؤكدة</option>
                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>تم التسليم</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغاة</option>
                </select>
                <select name="payment_status" class="form-control">
                    <option value="">كل حالات الدفع</option>
                    <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>غير مدفوعة</option>
                    <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>مدفوعة جزئياً</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>مدفوعة</option>
                </select>
                <button type="submit" class="btn btn-primary">🔍 بحث</button>
                <a href="{{ route('invoices.index') }}" class="btn">إعادة تعيين</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>رقم الفاتورة</th>
                    <th>العميل</th>
                    <th>التاريخ</th>
                    <th>الإجمالي</th>
                    <th>المدفوع</th>
                    <th>المتبقي</th>
                    <th>الحالة</th>
                    <th>حالة الدفع</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                <tr>
                    <td><code>{{ $invoice->invoice_number }}</code></td>
                    <td><strong>{{ $invoice->customer->name ?? '-' }}</strong></td>
                    <td>{{ $invoice->invoice_date?->format('Y-m-d') }}</td>
                    <td>{{ number_format($invoice->total_amount, 2) }} ج.م</td>
                    <td>{{ number_format($invoice->paid_amount, 2) }} ج.م</td>
                    <td>{{ number_format($invoice->remaining_amount, 2) }} ج.م</td>
                    <td>
                        @switch($invoice->status)
                            @case('draft')<span class="badge badge-secondary">مسودة</span>@break
                            @case('confirmed')<span class="badge badge-primary">مؤكدة</span>@break
                            @case('delivered')<span class="badge badge-success">تم التسليم</span>@break
                            @case('cancelled')<span class="badge badge-danger">ملغاة</span>@break
                            @default<span class="badge">{{ $invoice->status }}</span>
                        @endswitch
                    </td>
                    <td>
                        @switch($invoice->payment_status)
                            @case('unpaid')<span class="badge badge-danger">غير مدفوعة</span>@break
                            @case('partial')<span class="badge badge-warning">جزئي</span>@break
                            @case('paid')<span class="badge badge-success">مدفوعة</span>@break
                            @default<span class="badge">{{ $invoice->payment_status }}</span>
                        @endswitch
                    </td>
                    <td>
                        <div class="table-actions">
                            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm" title="عرض">👁️</a>
                            <a href="{{ route('invoices.print', $invoice) }}" class="btn btn-sm" target="_blank" title="طباعة">🖨️</a>
                            <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-sm" title="تعديل">✏️</a>
                            <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذه الفاتورة؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="حذف">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <div class="empty-state-icon">🧾</div>
                            <h3>لا توجد فواتير</h3>
                            <p>ابدأ بإنشاء فاتورة جديدة</p>
                            <a href="{{ route('invoices.create') }}" class="btn btn-primary">+ فاتورة جديدة</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($invoices->hasPages())
    <div class="card-footer">
        {{ $invoices->withQueryString()->links() }}
    </div>
    @endif
</div>

<style>
.filter-form { margin-bottom: 0; }
.filter-row { display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; }
.filter-row .form-control { flex: 1; min-width: 150px; }
.filter-row .btn { white-space: nowrap; }
.table-actions { display: flex; gap: 0.25rem; }
.badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; }
.badge-primary { background: var(--primary); color: white; }
.badge-success { background: #10b981; color: white; }
.badge-warning { background: #f59e0b; color: white; }
.badge-danger { background: #ef4444; color: white; }
.badge-secondary { background: #6b7280; color: white; }
.alert { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
.alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
.alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
</style>
@endsection
