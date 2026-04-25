@extends('layouts.app')

@section('title', 'تعديل وحدة')

@section('content')
<div class="page-header">
    <div>
        <h1>📏 تعديل الوحدة: {{ $unit->name }}</h1>
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

<form method="POST" action="{{ route('admin.units.update', $unit) }}" class="card" style="padding:1.5rem;max-width:560px">
    @csrf
    @method('PUT')

    <div class="form-group">
        <label for="name">الاسم <span style="color:#ef4444">*</span></label>
        <input type="text" id="name" name="name" value="{{ old('name', $unit->name) }}"
               class="form-control" maxlength="50" required>
    </div>

    <div class="form-group">
        <label style="display:flex;gap:.5rem;align-items:center">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $unit->is_active ? '1' : '0') === '1')>
            <span>نشط</span>
        </label>
    </div>

    <div style="display:flex;gap:.5rem">
        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
        <a href="{{ route('admin.units.index') }}" class="btn">إلغاء</a>
    </div>
</form>
@endsection
