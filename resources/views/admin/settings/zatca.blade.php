@extends('layouts.app')

@section('title', 'إعدادات ZATCA')

@section('content')
<div class="page-header">
    <div>
        <h1>🧾 إعدادات الفوترة الإلكترونية (ZATCA)</h1>
        <p>تكوين بيئة العمل مع هيئة الزكاة والضريبة والجمارك</p>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul style="margin:0;padding-right:1rem">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('admin.settings.zatca.update') }}" class="card" style="padding:1.5rem;max-width:720px">
    @csrf
    @method('PUT')

    <div class="form-group">
        <label for="zatca_environment">بيئة ZATCA</label>
        <select id="zatca_environment" name="zatca_environment" class="form-control" required>
            @foreach (['sandbox' => 'تجريبية (Sandbox)', 'simulation' => 'محاكاة (Simulation)', 'production' => 'الإنتاج (Production)'] as $value => $label)
                <option value="{{ $value }}" @selected(old('zatca_environment', $settings['zatca_environment']) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <small>اختر بيئة الإنتاج فقط بعد اعتماد الشهادة من ZATCA</small>
    </div>

    <button type="submit" class="btn btn-primary">حفظ الإعدادات</button>
</form>
@endsection
