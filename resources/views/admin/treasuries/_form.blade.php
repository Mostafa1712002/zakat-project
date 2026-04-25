@if ($errors->any())
    <div class="alert alert-danger">
        <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<div class="form-row">
    <label>الاسم <span class="required">*</span></label>
    <input type="text" name="name" required value="{{ old('name', $treasury->name ?? '') }}">
</div>

<div class="form-row">
    <label>النوع <span class="required">*</span></label>
    <select name="type" required>
        <option value="cash" @selected(old('type', $treasury->type ?? 'cash') === 'cash')>💵 نقدي</option>
        <option value="bank" @selected(old('type', $treasury->type ?? '') === 'bank')>🏦 بنكي</option>
    </select>
</div>

<div class="form-row">
    <label>الفرع</label>
    <select name="branch_id">
        <option value="">— عام —</option>
        @foreach ($branches as $b)
            <option value="{{ $b->id }}" @selected(old('branch_id', $treasury->branch_id ?? null) == $b->id)>{{ $b->name }}</option>
        @endforeach
    </select>
</div>

@if (!isset($treasury))
<div class="form-row">
    <label>الرصيد الافتتاحي</label>
    <input type="number" name="balance" step="0.01" value="{{ old('balance', 0) }}">
</div>
@endif

<div class="form-row">
    <label>
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $treasury->is_active ?? true))>
        نشطة
    </label>
</div>
