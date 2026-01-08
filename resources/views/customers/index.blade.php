@extends('layouts.app')

@section('title', 'العملاء')

@section('content')
<div class="page-header">
    <div>
        <h1>👥 العملاء</h1>
        <p>إدارة بيانات العملاء</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('customers.create') }}" class="btn btn-primary">+ إضافة عميل</a>
    </div>
</div>

<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الاسم</th>
                    <th>البريد</th>
                    <th>الهاتف</th>
                    <th>النوع</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                <tr>
                    <td>{{ $customer->id }}</td>
                    <td><strong>{{ $customer->name }}</strong></td>
                    <td class="text-muted">{{ $customer->email ?? '-' }}</td>
                    <td>{{ $customer->phone ?? '-' }}</td>
                    <td>
                        @php
                            $typeLabel = match ($customer->type) {
                                'wholesale' => 'جملة',
                                'corporate' => 'شركة',
                                default => 'قطاعي',
                            };
                            $typeClass = match ($customer->type) {
                                'corporate' => 'badge-primary',
                                'wholesale' => 'badge-warning',
                                default => 'badge-secondary',
                            };
                        @endphp
                        <span class="badge {{ $typeClass }}">{{ $typeLabel }}</span>
                    </td>
                    <td>
                        <div class="table-actions">
                            <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm">عرض</a>
                            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm">تعديل</a>
                            <form action="{{ route('customers.destroy', $customer) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
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
