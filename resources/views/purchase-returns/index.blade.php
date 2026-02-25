@extends('layouts.app')

@section('title', 'مرتجعات المشتريات')

@section('content')
<div class="page-header">
    <div>
        <h1>↩️ مرتجعات المشتريات</h1>
        <p>إدارة مرتجعات فواتير الشراء</p>
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
        <form action="{{ route('purchase-returns.index') }}" method="GET" class="filter-form">
            <div class="filter-row">
                <input type="text" name="search" class="form-control" placeholder="بحث برقم المرتجع أو رقم الفاتورة أو اسم المورد..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary">🔍 بحث</button>
                <a href="{{ route('purchase-returns.index') }}" class="btn">إعادة تعيين</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>رقم المرتجع</th>
                    <th>رقم الفاتورة</th>
                    <th>المورد</th>
                    <th>التاريخ</th>
                    <th>الإجمالي</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchaseReturns as $return)
                <tr>
                    <td><code>{{ $return->return_number }}</code></td>
                    <td>
                        <a href="{{ route('purchases.show', $return->purchase_id) }}">
                            {{ $return->purchase->invoice_number ?? '-' }}
                        </a>
                    </td>
                    <td><strong>{{ $return->supplier->name ?? '-' }}</strong></td>
                    <td>{{ $return->return_date?->format('Y-m-d') }}</td>
                    <td>{{ number_format($return->total_amount, 2) }} ج.م</td>
                    <td>
                        <div class="table-actions">
                            <a href="{{ route('purchase-returns.show', $return) }}" class="btn btn-sm" title="عرض">👁️</a>
                            <form action="{{ route('purchase-returns.destroy', $return) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف المرتجع؟ سيتم استعادة المخزون.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="حذف">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div class="empty-state-icon">↩️</div>
                            <h3>لا توجد مرتجعات</h3>
                            <p>يمكنك عمل مرتجع من صفحة فاتورة الشراء المستلمة</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($purchaseReturns->hasPages())
    <div class="card-footer">
        {{ $purchaseReturns->withQueryString()->links() }}
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
.badge-success { background: #10b981; color: white; }
.alert { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
.alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
.alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
</style>
@endsection
