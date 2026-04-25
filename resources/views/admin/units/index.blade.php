@extends('layouts.app')

@section('title', 'وحدات القياس')

@section('content')
<div class="page-header">
    <div>
        <h1>📏 وحدات القياس</h1>
        <p>وحدات تستخدم في الخدمات (يوم، فعالية، باقة، شهر، ساعة...)</p>
    </div>
    @can('create', \App\Domain\Catalog\Models\Unit::class)
        <div class="header-actions">
            <a href="{{ route('admin.units.create') }}" class="btn btn-primary">
                ➕ إضافة وحدة
            </a>
        </div>
    @endcan
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card" style="padding:1rem;margin-bottom:1rem">
    <form method="GET" action="{{ route('admin.units.index') }}"
          class="filter-row" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
        <div class="form-group" style="margin:0;flex:1;min-width:200px">
            <label for="search">بحث بالاسم</label>
            <input type="text" id="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="اسم الوحدة">
        </div>
        <div class="form-group" style="margin:0;min-width:160px">
            <label for="is_active">الحالة</label>
            <select id="is_active" name="is_active" class="form-control">
                <option value="">الكل</option>
                <option value="1" @selected(request('is_active') === '1')>نشط</option>
                <option value="0" @selected(request('is_active') === '0')>غير نشط</option>
            </select>
        </div>
        <div style="display:flex;gap:.5rem">
            <button type="submit" class="btn btn-primary">بحث</button>
            <a href="{{ route('admin.units.index') }}" class="btn">إعادة تعيين</a>
        </div>
    </form>
</div>

<div class="card" style="padding:1rem">
    <table class="table" style="width:100%">
        <thead>
            <tr>
                <th>#</th>
                <th>الاسم</th>
                <th>عدد الخدمات</th>
                <th>الحالة</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($units as $unit)
                <tr>
                    <td>{{ $unit->id }}</td>
                    <td><strong>{{ $unit->name }}</strong></td>
                    <td>{{ $unit->services_count }}</td>
                    <td>
                        @if ($unit->is_active)
                            <span class="badge badge-success">نشط</span>
                        @else
                            <span class="badge badge-secondary">غير نشط</span>
                        @endif
                    </td>
                    <td class="table-actions" style="display:flex;gap:.25rem">
                        @can('update', $unit)
                            <a href="{{ route('admin.units.edit', $unit) }}" class="btn btn-sm btn-primary">تعديل</a>
                        @endcan
                        @can('delete', $unit)
                            <form method="POST" action="{{ route('admin.units.destroy', $unit) }}"
                                  onsubmit="return confirm('هل أنت متأكد من حذف الوحدة؟');"
                                  style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;color:#6b7280">لا توجد وحدات</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:1rem">{{ $units->links() }}</div>
</div>
@endsection
