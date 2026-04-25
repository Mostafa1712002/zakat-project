@extends('layouts.app')

@section('title', 'تعديل خدمة')

@section('content')
<div class="page-header">
    <div>
        <h1>🛠️ تعديل الخدمة: {{ $service->name }}</h1>
        <p>الخدمة لا تحمل سعراً ثابتاً</p>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul style="margin:0;padding-right:1rem">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('admin.services.update', $service) }}" class="card" style="padding:1.5rem;max-width:720px">
    @csrf
    @method('PUT')

    <div class="form-group">
        <label for="service_type_id">نوع الخدمة <span style="color:#ef4444">*</span></label>
        <select id="service_type_id" name="service_type_id" class="form-control" required>
            @foreach ($serviceTypes as $type)
                <option value="{{ $type->id }}" @selected(old('service_type_id', $service->service_type_id) == $type->id)>{{ $type->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="name">اسم الخدمة <span style="color:#ef4444">*</span></label>
        <input type="text" id="name" name="name" value="{{ old('name', $service->name) }}"
               class="form-control" maxlength="255" required>
    </div>

    <div class="form-group">
        <label for="unit_id">الوحدة <span style="color:#ef4444">*</span></label>
        <select id="unit_id" name="unit_id" class="form-control" required>
            @foreach ($units as $unit)
                <option value="{{ $unit->id }}" @selected(old('unit_id', $service->unit_id) == $unit->id)>{{ $unit->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="description">الوصف</label>
        <textarea id="description" name="description" class="form-control" rows="4">{{ old('description', $service->description) }}</textarea>
    </div>

    <div class="form-group">
        <label for="default_zatca_classification">تصنيف ZATCA <span style="color:#ef4444">*</span></label>
        <select id="default_zatca_classification" name="default_zatca_classification" class="form-control" required>
            <option value="S" @selected(old('default_zatca_classification', $service->default_zatca_classification) === 'S')>S — قياسية (15%)</option>
            <option value="Z" @selected(old('default_zatca_classification', $service->default_zatca_classification) === 'Z')>Z — صفرية</option>
            <option value="E" @selected(old('default_zatca_classification', $service->default_zatca_classification) === 'E')>E — معفاة</option>
        </select>
    </div>

    <div class="form-group">
        <label style="display:flex;gap:.5rem;align-items:center">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $service->is_active ? '1' : '0') === '1')>
            <span>نشط</span>
        </label>
    </div>

    <div style="display:flex;gap:.5rem">
        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
        <a href="{{ route('admin.services.index') }}" class="btn">إلغاء</a>
    </div>
</form>
@endsection
