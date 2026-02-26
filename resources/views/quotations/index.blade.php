@extends('layouts.app')

@section('title', 'تسعيرات المبيعات')

@section('content')
<div class="page-header">
    <div>
        <h1>📋 تسعيرات المبيعات</h1>
        <p>إدارة عروض الأسعار والتسعيرات</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('quotations.create') }}" class="btn btn-primary">+ تسعيرة جديدة</a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card filter-card">
    <div class="filter-toggle">
        <span class="filter-toggle-title">فلترة وبحث</span>
        <span class="filter-toggle-icon">▼</span>
    </div>
    <div class="filter-body">
        <form action="{{ route('quotations.index') }}" method="GET" class="filter-form">
            <div class="filter-row">
                <input type="text" name="search" class="form-control" placeholder="بحث برقم التسعيرة أو اسم العميل..." value="{{ request('search') }}">
                <select name="status" class="form-control">
                    <option value="">كل الحالات</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>مؤكدة</option>
                </select>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">بحث</button>
                    <a href="{{ route('quotations.index') }}" class="btn">إعادة تعيين</a>
                </div>
            </div>
        </form>
    </div>

    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>#</th>
                    <th>رقم التسعيرة</th>
                    <th>العميل</th>
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
                    <td><strong>{{ $quotation->customer->name ?? '-' }}</strong></td>
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
                            <a href="{{ route('quotations.show', $quotation) }}" class="btn btn-sm">عرض</a>
                            @if($quotation->status === 'draft')
                            <a href="{{ route('quotations.edit', $quotation) }}" class="btn btn-sm">تعديل</a>
                            <form action="{{ route('quotations.confirm', $quotation) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من تأكيد التسعيرة؟ سيتم تحويلها لفاتورة مبيعات وخصم الكميات من المخزون.')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">تأكيد</button>
                            </form>
                            <form action="{{ route('quotations.destroy', $quotation) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
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
                            <div class="empty-state-icon">📋</div>
                            <h3>لا توجد تسعيرات</h3>
                            <p>ابدأ بإنشاء تسعيرة مبيعات جديدة</p>
                            <a href="{{ route('quotations.create') }}" class="btn btn-primary">+ تسعيرة جديدة</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($quotations->hasPages())
    <div class="card-footer">
        {{ $quotations->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
