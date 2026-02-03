@extends('layouts.app')

@section('title', 'المشتريات')

@section('content')
<div class="page-header">
    <div>
        <h1>🛒 المشتريات</h1>
        <p>إدارة أوامر الشراء والموردين</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('purchases.create') }}" class="btn btn-primary">+ أمر شراء جديد</a>
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
        <form action="{{ route('purchases.index') }}" method="GET" class="filter-form">
            <div class="filter-row">
                <input type="text" name="search" class="form-control" placeholder="بحث برقم الفاتورة أو اسم المورد..." value="{{ request('search') }}">
                <select name="status" class="form-control">
                    <option value="">كل الحالات</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                    <option value="ordered" {{ request('status') == 'ordered' ? 'selected' : '' }}>تم الطلب</option>
                    <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>مستلم</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                </select>
                <select name="payment_status" class="form-control">
                    <option value="">كل حالات الدفع</option>
                    <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>غير مدفوعة</option>
                    <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>مدفوعة جزئياً</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>مدفوعة</option>
                </select>
                <button type="submit" class="btn btn-primary">🔍 بحث</button>
                <a href="{{ route('purchases.index') }}" class="btn">إعادة تعيين</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>رقم الفاتورة</th>
                    <th>المورد</th>
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
                @forelse($purchases as $purchase)
                <tr>
                    <td><code>{{ $purchase->invoice_number }}</code></td>
                    <td><strong>{{ $purchase->supplier->name ?? '-' }}</strong></td>
                    <td>{{ $purchase->invoice_date?->format('Y-m-d') }}</td>
                    <td>{{ number_format($purchase->total_amount, 2) }} ج.م</td>
                    <td>{{ number_format($purchase->paid_amount, 2) }} ج.م</td>
                    <td>{{ number_format($purchase->remaining_amount, 2) }} ج.م</td>
                    <td>
                        @switch($purchase->status)
                            @case('draft')<span class="badge badge-secondary">مسودة</span>@break
                            @case('ordered')<span class="badge badge-primary">تم الطلب</span>@break
                            @case('received')<span class="badge badge-success">مستلم</span>@break
                            @case('cancelled')<span class="badge badge-danger">ملغي</span>@break
                            @default<span class="badge">{{ $purchase->status }}</span>
                        @endswitch
                    </td>
                    <td>
                        @switch($purchase->payment_status)
                            @case('unpaid')<span class="badge badge-danger">غير مدفوعة</span>@break
                            @case('partial')<span class="badge badge-warning">جزئي</span>@break
                            @case('paid')<span class="badge badge-success">مدفوعة</span>@break
                            @default<span class="badge">{{ $purchase->payment_status }}</span>
                        @endswitch
                    </td>
                    <td>
                        <div class="table-actions">
                            <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-sm" title="عرض">👁️</a>
                            <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-sm" title="تعديل">✏️</a>
                            <form action="{{ route('purchases.destroy', $purchase) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا الشراء؟')">
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
                            <div class="empty-state-icon">🛒</div>
                            <h3>لا توجد مشتريات</h3>
                            <p>ابدأ بإنشاء أمر شراء جديد</p>
                            <a href="{{ route('purchases.create') }}" class="btn btn-primary">+ أمر شراء جديد</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($purchases->hasPages())
    <div class="card-footer">
        {{ $purchases->withQueryString()->links() }}
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
