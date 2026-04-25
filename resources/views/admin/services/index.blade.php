@extends('layouts.app')

@section('title', 'الخدمات')

@section('content')
<div class="page-header">
    <div>
        <h1>🛠️ الخدمات</h1>
        <p>كتالوج الخدمات (الأسعار تُحدد لاحقاً عند إصدار الفاتورة)</p>
    </div>
    @can('create', \App\Domain\Catalog\Models\Service::class)
        <div class="header-actions">
            <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
                ➕ إضافة خدمة
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
    <form method="GET" action="{{ route('admin.services.index') }}"
          class="filter-row" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
        <div class="form-group" style="margin:0;flex:1;min-width:200px">
            <label for="search">بحث بالاسم</label>
            <input type="text" id="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="اسم الخدمة">
        </div>
        <div class="form-group" style="margin:0;min-width:200px">
            <label for="service_type_id">نوع الخدمة</label>
            <select id="service_type_id" name="service_type_id" class="form-control">
                <option value="">الكل</option>
                @foreach ($serviceTypes as $type)
                    <option value="{{ $type->id }}" @selected(request('service_type_id') == $type->id)>{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group" style="margin:0;min-width:140px">
            <label for="is_active">الحالة</label>
            <select id="is_active" name="is_active" class="form-control">
                <option value="">الكل</option>
                <option value="1" @selected(request('is_active') === '1')>نشط</option>
                <option value="0" @selected(request('is_active') === '0')>غير نشط</option>
            </select>
        </div>
        <div style="display:flex;gap:.5rem">
            <button type="submit" class="btn btn-primary">بحث</button>
            <a href="{{ route('admin.services.index') }}" class="btn">إعادة تعيين</a>
        </div>
    </form>
</div>

<div class="card" style="padding:1rem">
    <table class="table" style="width:100%">
        <thead>
            <tr>
                <th>#</th>
                <th>الاسم</th>
                <th>النوع</th>
                <th>الوحدة</th>
                <th>تصنيف ZATCA</th>
                <th>الحالة</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($services as $service)
                <tr>
                    <td>{{ $service->id }}</td>
                    <td><strong>{{ $service->name }}</strong></td>
                    <td>{{ $service->serviceType?->name ?? '—' }}</td>
                    <td>{{ $service->unit?->name ?? '—' }}</td>
                    <td>
                        <code>{{ $service->default_zatca_classification }}</code>
                    </td>
                    <td>
                        @if ($service->is_active)
                            <span class="badge badge-success">نشط</span>
                        @else
                            <span class="badge badge-secondary">غير نشط</span>
                        @endif
                    </td>
                    <td class="table-actions" style="display:flex;gap:.25rem">
                        @can('update', $service)
                            <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-sm btn-primary">تعديل</a>
                        @endcan
                        @can('delete', $service)
                            <form method="POST" action="{{ route('admin.services.destroy', $service) }}"
                                  onsubmit="return confirm('هل أنت متأكد من حذف الخدمة؟');"
                                  style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;color:#6b7280">لا توجد خدمات بعد</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:1rem">{{ $services->links() }}</div>
</div>
@endsection
