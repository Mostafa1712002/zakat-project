@extends('layouts.app')

@section('title', 'تسعيرات المشتريات')

@section('content')
<div class="page-header">
    <div>
        <h1>📝 تسعيرات المشتريات</h1>
        <p>إدارة عروض أسعار المشتريات</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('purchase-quotations.create') }}" class="btn btn-primary">+ تسعيرة جديدة</a>
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
        <form action="{{ route('purchase-quotations.index') }}" method="GET" class="filter-form">
            <div class="filter-row">
                <input type="text" name="search" class="form-control" placeholder="بحث برقم التسعيرة أو اسم المورد..." value="{{ request('search') }}">
                <select name="status" class="form-control">
                    <option value="">كل الحالات</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                    <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>مؤكدة</option>
                </select>
                <button type="submit" class="btn btn-primary">بحث</button>
                @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('purchase-quotations.index') }}" class="btn">مسح</a>
                @endif
            </div>
        </form>
    </div>

    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>#</th>
                    <th>رقم التسعيرة</th>
                    <th>المورد</th>
                    <th>الإجمالي</th>
                    <th>الحالة</th>
                    <th>التاريخ</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($quotations as $quotation)
                <tr>
                    <td>{{ $quotation->id }}</td>
                    <td><code>{{ $quotation->invoice_number }}</code></td>
                    <td><strong>{{ $quotation->supplier->name ?? '-' }}</strong></td>
                    <td>{{ number_format($quotation->total_amount, 2) }} ج.م</td>
                    <td>
                        @if($quotation->status === 'draft')
                            <span class="badge text-muted">مسودة</span>
                        @else
                            <span class="badge badge-primary">مؤكدة</span>
                        @endif
                    </td>
                    <td>{{ $quotation->invoice_date?->format('Y/m/d') ?? $quotation->created_at->format('Y/m/d') }}</td>
                    <td>
                        <div class="table-actions">
                            <a href="{{ route('purchase-quotations.show', $quotation) }}" class="btn btn-sm">عرض</a>
                            @if($quotation->status === 'draft')
                            <a href="{{ route('purchase-quotations.edit', $quotation) }}" class="btn btn-sm">تعديل</a>
                            <form action="{{ route('purchase-quotations.confirm', $quotation) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من تأكيد التسعيرة؟ سيتم تحويلها لفاتورة مشتريات وإضافة الكميات للمخزون.')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">تأكيد</button>
                            </form>
                            <form action="{{ route('purchase-quotations.destroy', $quotation) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <div class="empty-state-icon">📝</div>
                            <h3>لا توجد تسعيرات</h3>
                            <p>ابدأ بإنشاء تسعيرة مشتريات جديدة</p>
                            <a href="{{ route('purchase-quotations.create') }}" class="btn btn-primary">+ تسعيرة جديدة</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($quotations->hasPages())
    <div class="pagination">
        {{ $quotations->links() }}
    </div>
    @endif
</div>

<style>
.filter-form { margin-bottom: 0; }
.filter-row { display: flex; gap: 12px; flex-wrap: wrap; align-items: center; }
.filter-row .form-control { max-width: 280px; }
.btn-success { background: linear-gradient(135deg, #22c55e, #16a34a); color: white; border: none; }
.btn-success:hover { background: linear-gradient(135deg, #16a34a, #15803d); }
</style>
@endsection
