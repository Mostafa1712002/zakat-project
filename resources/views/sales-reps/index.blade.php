@extends('layouts.app')

@section('title', 'المندوبين')

@section('content')
<div class="page-header">
    <div>
        <h1>👔 المندوبين</h1>
        <p>إدارة مندوبي المبيعات</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('sales-reps.create') }}" class="btn btn-primary">+ إضافة مندوب</a>
    </div>
</div>

<div class="card">
    <div class="table-container overflow-auto">
        <table class="table text-nowrap">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>الكود</th>
                    <th>الهاتف</th>
                    <th>نسبة العمولة</th>
                    <th>الهدف</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salesReps as $rep)
                <tr>
                    <td><strong>{{ $rep->name }}</strong></td>
                    <td>{{ $rep->code ?? '-' }}</td>
                    <td>{{ $rep->phone ?? '-' }}</td>
                    <td>{{ $rep->commission_rate }}%</td>
                    <td>{{ number_format($rep->sales_target, 2) }} ج.م</td>
                    <td>
                        @if($rep->is_active)
                            <span class="badge badge-success">نشط</span>
                        @else
                            <span class="badge badge-danger">غير نشط</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('sales-reps.edit', $rep) }}" class="btn btn-sm">تعديل</a>
                            <form action="{{ route('sales-reps.destroy', $rep) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">لا يوجد مندوبين</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($salesReps->hasPages())
    <div class="pagination">
        {{ $salesReps->links() }}
    </div>
    @endif
</div>
@endsection
