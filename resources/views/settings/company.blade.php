@extends('layouts.app')

@section('title', 'إعدادات الشركة')

@section('content')
<div class="page-header">
    <div>
        <h1>🏢 إعدادات الشركة</h1>
        <p>تعديل بيانات الشركة والشعار والختم ومعلومات الاتصال</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('settings.index') }}" class="btn">← رجوع للإعدادات</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('settings.company.update') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <h3 style="margin-bottom: 16px;">🖼️ الشعار والختم</h3>

            <div class="form-row">
                {{-- Logo Upload --}}
                <div class="form-group">
                    <label class="form-label">شعار الشركة</label>
                    <div class="file-upload-area" id="logoArea">
                        @if(!empty($settings['company_logo']))
                            <div class="file-preview" id="logoPreview">
                                <img src="{{ asset('storage/' . $settings['company_logo']) }}" alt="شعار الشركة">
                                <button type="button" class="file-remove-btn" onclick="removeFile('logo')">✕</button>
                            </div>
                        @else
                            <div class="file-preview" id="logoPreview" style="display:none;">
                                <img src="" alt="شعار الشركة">
                                <button type="button" class="file-remove-btn" onclick="removeFile('logo')">✕</button>
                            </div>
                        @endif
                        <label class="file-upload-btn" id="logoUploadBtn" @if(!empty($settings['company_logo'])) style="display:none;" @endif>
                            <input type="file" name="logo" accept="image/*" onchange="previewFile(this, 'logo')" hidden>
                            <span>📁</span>
                            <span>اختر صورة الشعار</span>
                            <small>PNG, JPG, WEBP - حد أقصى 2MB</small>
                        </label>
                    </div>
                    <input type="hidden" name="remove_logo" id="remove_logo" value="0">
                    @error('logo')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Stamp Upload --}}
                <div class="form-group">
                    <label class="form-label">ختم الشركة</label>
                    <div class="file-upload-area" id="stampArea">
                        @if(!empty($settings['company_stamp']))
                            <div class="file-preview" id="stampPreview">
                                <img src="{{ asset('storage/' . $settings['company_stamp']) }}" alt="ختم الشركة">
                                <button type="button" class="file-remove-btn" onclick="removeFile('stamp')">✕</button>
                            </div>
                        @else
                            <div class="file-preview" id="stampPreview" style="display:none;">
                                <img src="" alt="ختم الشركة">
                                <button type="button" class="file-remove-btn" onclick="removeFile('stamp')">✕</button>
                            </div>
                        @endif
                        <label class="file-upload-btn" id="stampUploadBtn" @if(!empty($settings['company_stamp'])) style="display:none;" @endif>
                            <input type="file" name="stamp" accept="image/*" onchange="previewFile(this, 'stamp')" hidden>
                            <span>📁</span>
                            <span>اختر صورة الختم</span>
                            <small>PNG, JPG, WEBP - حد أقصى 2MB</small>
                        </label>
                    </div>
                    <input type="hidden" name="remove_stamp" id="remove_stamp" value="0">
                    <small style="color: #64748b; font-size: 12px;">يظهر في فاتورة المبيعات والمشتريات المطبوعة</small>
                    @error('stamp')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr style="margin: 24px 0; border-color: var(--border-color);">
            <h3 style="margin-bottom: 16px;">📋 البيانات الأساسية</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="company_name" class="form-label">اسم الشركة *</label>
                    <input type="text" name="company_name" id="company_name" class="form-control" value="{{ old('company_name', $settings['company_name'] ?? config('app.name')) }}" required>
                    @error('company_name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="company_name_en" class="form-label">اسم الشركة (إنجليزي)</label>
                    <input type="text" name="company_name_en" id="company_name_en" class="form-control" value="{{ old('company_name_en', $settings['company_name_en'] ?? '') }}" dir="ltr">
                    @error('company_name_en')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="tax_number" class="form-label">الرقم الضريبي</label>
                    <input type="text" name="tax_number" id="tax_number" class="form-control" value="{{ old('tax_number', $settings['tax_number'] ?? '') }}">
                    @error('tax_number')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="commercial_register" class="form-label">السجل التجاري</label>
                    <input type="text" name="commercial_register" id="commercial_register" class="form-control" value="{{ old('commercial_register', $settings['commercial_register'] ?? '') }}">
                    @error('commercial_register')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr style="margin: 24px 0; border-color: var(--border-color);">
            <h3 style="margin-bottom: 16px;">📞 معلومات الاتصال</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone" class="form-label">الهاتف</label>
                    <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $settings['phone'] ?? '') }}">
                    @error('phone')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $settings['email'] ?? '') }}">
                    @error('email')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="supervisor_phone" class="form-label">هاتف مشرف الخط</label>
                <input type="text" name="supervisor_phone" id="supervisor_phone" class="form-control" value="{{ old('supervisor_phone', $settings['supervisor_phone'] ?? '') }}" placeholder="رقم هاتف مشرف الخط (يظهر في الفاتورة)">
                <small style="color: #64748b; font-size: 12px;">يظهر في فاتورة المبيعات المطبوعة</small>
                @error('supervisor_phone')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="website" class="form-label">الموقع الإلكتروني</label>
                    <input type="url" name="website" id="website" class="form-control" value="{{ old('website', $settings['website'] ?? '') }}" placeholder="https://example.com">
                    @error('website')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="address" class="form-label">العنوان</label>
                <textarea name="address" id="address" class="form-control" rows="2">{{ old('address', $settings['address'] ?? '') }}</textarea>
                @error('address')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <hr style="margin: 24px 0; border-color: var(--border-color);">
            <h3 style="margin-bottom: 16px;">💰 إعدادات مالية</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="currency" class="form-label">العملة</label>
                    <select name="currency" id="currency" class="form-control">
                        <option value="EGP" {{ ($settings['currency'] ?? '') == 'EGP' ? 'selected' : '' }}>جنيه مصري (ج.م)</option>
                        <option value="SAR" {{ ($settings['currency'] ?? '') == 'SAR' ? 'selected' : '' }}>ريال سعودي (ر.س)</option>
                        <option value="USD" {{ ($settings['currency'] ?? '') == 'USD' ? 'selected' : '' }}>دولار أمريكي ($)</option>
                        <option value="EUR" {{ ($settings['currency'] ?? '') == 'EUR' ? 'selected' : '' }}>يورو (€)</option>
                    </select>
                    @error('currency')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="tax_rate" class="form-label">نسبة الضريبة الافتراضية (%)</label>
                    <input type="number" step="0.01" name="tax_rate" id="tax_rate" class="form-control" value="{{ old('tax_rate', $settings['tax_rate'] ?? 14) }}" min="0" max="100">
                    @error('tax_rate')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">💾 حفظ الإعدادات</button>
                <a href="{{ route('settings.index') }}" class="btn">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
.file-upload-area {
    border: 2px dashed var(--border);
    border-radius: 8px;
    padding: 16px;
    text-align: center;
    transition: border-color 0.2s;
    min-height: 160px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.file-upload-area:hover {
    border-color: var(--primary);
}
.file-upload-btn {
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    color: var(--text-muted);
    font-size: 14px;
    padding: 16px;
    width: 100%;
}
.file-upload-btn span:first-child {
    font-size: 32px;
}
.file-upload-btn small {
    font-size: 11px;
    color: #94a3b8;
}
.file-preview {
    position: relative;
    display: inline-block;
}
.file-preview img {
    max-width: 180px;
    max-height: 120px;
    object-fit: contain;
    border-radius: 6px;
    border: 1px solid var(--border);
    background: #f8fafc;
    padding: 4px;
}
.file-remove-btn {
    position: absolute;
    top: -8px;
    left: -8px;
    width: 24px;
    height: 24px;
    background: var(--danger);
    color: white;
    border: 2px solid white;
    border-radius: 50%;
    font-size: 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
</style>
@endpush

@push('scripts')
<script>
function previewFile(input, type) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var preview = document.getElementById(type + 'Preview');
            preview.querySelector('img').src = e.target.result;
            preview.style.display = 'inline-block';
            document.getElementById(type + 'UploadBtn').style.display = 'none';
            document.getElementById('remove_' + type).value = '0';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeFile(type) {
    var preview = document.getElementById(type + 'Preview');
    preview.style.display = 'none';
    preview.querySelector('img').src = '';
    document.getElementById(type + 'UploadBtn').style.display = 'flex';
    document.getElementById('remove_' + type).value = '1';
    // Clear file input
    var fileInput = document.getElementById(type + 'UploadBtn').querySelector('input[type="file"]');
    if (fileInput) fileInput.value = '';
}
</script>
@endpush
