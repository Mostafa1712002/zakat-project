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

<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>الكود</th>
                    <th>الهاتف</th>
                    <th>جهة الاتصال</th>
                    <th>الرصيد</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $supplier)
                <tr>
                    <td><strong>{{ $supplier->name }}</strong></td>
                    <td>{{ $supplier->code ?? '-' }}</td>
                    <td>{{ $supplier->phone ?? $supplier->mobile ?? '-' }}</td>
                    <td>{{ $supplier->contact_person ?? '-' }}</td>
                    <td>{{ number_format($supplier->current_balance, 2) }} ج.م</td>
                    <td>
                        @if($supplier->is_active)
                            <span class="badge badge-success">نشط</span>
                        @else
                            <span class="badge badge-danger">غير نشط</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group">
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
                    <td colspan="7" class="text-center">لا يوجد موردين</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($suppliers->hasPages())
    <div class="pagination">
        {{ $suppliers->links() }}
    </div>
    @endif
</div>
@endsection
