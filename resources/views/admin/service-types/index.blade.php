@extends('layouts.app')

@section('title', 'أنواع الخدمات')

@section('content')
<div class="page-header">
    <div>
        <h1>🗂️ أنواع الخدمات</h1>
        <p>تصنيف الخدمات الرئيسية في المنصة</p>
    </div>
    @can('create', \App\Domain\Catalog\Models\ServiceType::class)
        <div class="header-actions">
            <a href="{{ route('admin.service-types.create') }}" class="btn btn-primary">
                ➕ إضافة نوع خدمة
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
    <form method="GET" action="{{ route('admin.service-types.index') }}"
          class="filter-row" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
        <div class="form-group" style="margin:0;flex:1;min-width:200px">
            <label for="search">بحث بالاسم</label>
            <input type="text" id="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="اسم نوع الخدمة">
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
            <a href="{{ route('admin.service-types.index') }}" class="btn">إعادة تعيين</a>
        </div>
    </form>
</div>

<div class="card" style="padding:1rem">
    <table class="table" style="width:100%">
        <thead>
            <tr>
                <th>#</th>
                <th>الاسم</th>
                <th>الأيقونة</th>
                <th>الترتيب</th>
                <th>عدد الخدمات</th>
                <th>الحالة</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($serviceTypes as $type)
                <tr>
                    <td>{{ $type->id }}</td>
                    <td><strong>{{ $type->name }}</strong></td>
                    <td>
                        @if ($type->icon)
                            <code style="font-size:.85rem">{{ $type->icon }}</code>
                        @else
                            <span style="color:#9ca3af">—</span>
                        @endif
                    </td>
                    <td>{{ $type->sort_order }}</td>
                    <td>{{ $type->services_count }}</td>
                    <td>
                        @if ($type->is_active)
                            <span class="badge badge-success">نشط</span>
                        @else
                            <span class="badge badge-secondary">غير نشط</span>
                        @endif
                    </td>
                    <td class="table-actions" style="display:flex;gap:.25rem">
                        @can('update', $type)
                            <a href="{{ route('admin.service-types.edit', $type) }}" class="btn btn-sm btn-primary">تعديل</a>
                        @endcan
                        @can('delete', $type)
                            <form method="POST" action="{{ route('admin.service-types.destroy', $type) }}"
                                  onsubmit="return confirm('هل أنت متأكد من حذف نوع الخدمة؟');"
                                  style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;color:#6b7280">لا توجد أنواع خدمات</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:1rem">{{ $serviceTypes->links() }}</div>
</div>
@endsection
