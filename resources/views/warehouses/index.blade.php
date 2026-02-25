@extends('layouts.app')

@section('title', 'المخازن')

@section('content')
<div class="page-header">
    <div>
        <h1>🏭 المخازن</h1>
        <p>إدارة المخازن والمخازن</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('warehouses.transfer') }}" class="btn" style="background: linear-gradient(135deg, #8b5cf6, #6366f1); color: white; margin-left: 10px;">🔄 ترحيل الأصناف</a>
        <a href="{{ route('warehouses.create') }}" class="btn btn-primary">+ إضافة مخزن</a>
    </div>
</div>

<div class="card">
    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>#</th>
                    <th>المخزن</th>
                    <th>الفرع</th>
                    <th>العنوان</th>
                    <th>المدير</th>
                    <th>عدد الأصناف</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($warehouses as $warehouse)
                <tr>
                    <td>{{ $warehouse->id }}</td>
                    <td><strong>{{ $warehouse->name }}</strong></td>
                    <td>{{ $warehouse->branch->name ?? '-' }}</td>
                    <td class="text-muted">{{ $warehouse->address ?? '-' }}</td>
                    <td>{{ $warehouse->manager_name ?? '-' }}</td>
                    <td><span class="badge badge-primary">{{ $warehouse->inventory_levels_count ?? 0 }}</span></td>
                    <td>
                        <div class="table-actions">
                            <a href="{{ route('warehouses.show', $warehouse) }}" class="btn btn-sm">عرض</a>
                            <a href="{{ route('warehouses.edit', $warehouse) }}" class="btn btn-sm">تعديل</a>
                            <form action="{{ route('warehouses.destroy', $warehouse) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <div class="empty-state-icon">🏭</div>
                            <h3>لا توجد مخازن</h3>
                            <p>ابدأ بإضافة مخزن جديد</p>
                            <a href="{{ route('warehouses.create') }}" class="btn btn-primary">+ إضافة مخزن</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($warehouses->hasPages())
    <div class="pagination">
        {{ $warehouses->links() }}
    </div>
    @endif
</div>
@endsection
