@extends('layouts.app')

@section('title', 'الموردين')

@section('content')
<div class="page-header">
    <div>
        <h1>🏭 الموردين</h1>
        <p>إدارة الموردين</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary">+ إضافة مورد</a>
    </div>
</div>

<div class="card filter-card">
    <div class="filter-toggle">
        <span class="filter-toggle-title">فلترة وبحث</span>
        <span class="filter-toggle-icon">▼</span>
    </div>
    <div class="filter-body">
        <form action="{{ route('suppliers.index') }}" method="GET" class="filter-form">
            <div class="filter-row">
                <input type="text" name="search" class="form-control" placeholder="بحث بالاسم أو الهاتف..." value="{{ request('search') }}">
                <select name="is_active" class="form-control">
                    <option value="">كل الحالات</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>نشط</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>غير نشط</option>
                </select>
                <select name="balance" class="form-control">
                    <option value="">كل الأرصدة</option>
                    <option value="has_balance" {{ request('balance') == 'has_balance' ? 'selected' : '' }}>عليه رصيد</option>
                    <option value="no_balance" {{ request('balance') == 'no_balance' ? 'selected' : '' }}>بدون رصيد</option>
                </select>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">بحث</button>
                    <a href="{{ route('suppliers.index') }}" class="btn">إعادة تعيين</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>الهاتف</th>
                    <th>الرصيد</th>
                    <th>عدد الأصناف</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $supplier)
                <tr>
                    <td><strong>{{ $supplier->name }}</strong></td>
                    <td>{{ $supplier->phone ?? $supplier->mobile ?? '-' }}</td>
                    <td>
                        @if(($supplier->total_remaining ?? 0) > 0)
                            <strong class="text-danger">{{ number_format($supplier->total_remaining, 2) }} ج.م</strong>
                        @else
                            <span class="text-muted">0.00 ج.م</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-primary">{{ $supplier->supplied_products_count ?? 0 }}</span>
                    </td>
                    <td>
                        @if($supplier->is_active)
                            <span class="badge badge-success">نشط</span>
                        @else
                            <span class="badge badge-danger">غير نشط</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-sm btn-primary">عرض</a>
                            @if(($supplier->total_remaining ?? 0) > 0)
                            <a href="{{ route('suppliers.pay.form', $supplier) }}" class="btn btn-sm btn-success">💰 دفع</a>
                            @endif
                            <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-sm">تعديل</a>
                            <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">لا يوجد موردين</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($suppliers->hasPages())
    <div class="card-footer">
        {{ $suppliers->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
