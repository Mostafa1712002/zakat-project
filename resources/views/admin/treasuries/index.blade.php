@extends('layouts.app')

@section('title', 'الخزائن')

@section('content')
<div class="page-header">
    <h1>🏦 الخزائن</h1>
    <div class="header-actions">
        <a href="{{ route('admin.treasuries.create') }}" class="btn btn-primary">➕ خزينة جديدة</a>
    </div>
</div>

@if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

<div class="card">
    <table class="data-table">
        <thead>
            <tr><th>الاسم</th><th>النوع</th><th>الفرع</th><th>الرصيد</th><th>الحالة</th><th></th></tr>
        </thead>
        <tbody>
            @forelse ($treasuries as $t)
                <tr>
                    <td>{{ $t->name }}</td>
                    <td>{{ $t->type === 'cash' ? '💵 نقدي' : '🏦 بنكي' }}</td>
                    <td>{{ $t->branch?->name ?? '—' }}</td>
                    <td><strong>{{ number_format($t->balance, 2) }} ر.س</strong></td>
                    <td>
                        @if ($t->is_active)
                            <span class="badge badge-success">نشطة</span>
                        @else
                            <span class="badge badge-secondary">موقوفة</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.treasuries.edit', $t) }}" class="btn btn-sm">تعديل</a>
                        <form method="POST" action="{{ route('admin.treasuries.destroy', $t) }}"
                              onsubmit="return confirm('متأكد؟')" style="display:inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">حذف</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center">لا توجد خزائن</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $treasuries->links() }}
</div>
@endsection
