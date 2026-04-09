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
            <h3 style="margin-bottom: 16px;">🧾 إعدادات ZATCA</h3>
            <p style="color: #64748b; font-size: 13px; margin-bottom: 16px;">هذه الحقول هي الأساس المطلوب لتجهيز الفاتورة الإلكترونية السعودية داخل النظام.</p>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="zatca_enabled" value="1" {{ old('zatca_enabled', $settings['zatca_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                    <span>تفعيل متطلبات الفوترة الإلكترونية السعودية</span>
                </label>
                <small style="color: #64748b; font-size: 12px;">عند التفعيل، سيتم تجهيز UUID ونوع الفاتورة وبيانات QR عند تأكيد الفاتورة، مع منع تعديل الفواتير المؤكدة.</small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="zatca_environment" class="form-label">بيئة ZATCA</label>
                    <select name="zatca_environment" id="zatca_environment" class="form-control">
                        <option value="sandbox" {{ old('zatca_environment', $settings['zatca_environment'] ?? 'simulation') === 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                        <option value="simulation" {{ old('zatca_environment', $settings['zatca_environment'] ?? 'simulation') === 'simulation' ? 'selected' : '' }}>Simulation</option>
                        <option value="production" {{ old('zatca_environment', $settings['zatca_environment'] ?? 'simulation') === 'production' ? 'selected' : '' }}>Production</option>
                    </select>
                    @error('zatca_environment')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="zatca_business_category" class="form-label">النشاط التجاري</label>
                    <input type="text" name="zatca_business_category" id="zatca_business_category" class="form-control" value="{{ old('zatca_business_category', $settings['zatca_business_category'] ?? '') }}" placeholder="مثال: تجارة مواد البناء">
                    @error('zatca_business_category')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="zatca_building_number" class="form-label">رقم المبنى</label>
                    <input type="text" name="zatca_building_number" id="zatca_building_number" class="form-control" value="{{ old('zatca_building_number', $settings['zatca_building_number'] ?? '') }}">
                    @error('zatca_building_number')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="zatca_additional_number" class="form-label">الرقم الإضافي</label>
                    <input type="text" name="zatca_additional_number" id="zatca_additional_number" class="form-control" value="{{ old('zatca_additional_number', $settings['zatca_additional_number'] ?? '') }}">
                    @error('zatca_additional_number')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="zatca_district" class="form-label">الحي</label>
                    <input type="text" name="zatca_district" id="zatca_district" class="form-control" value="{{ old('zatca_district', $settings['zatca_district'] ?? '') }}">
                    @error('zatca_district')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="zatca_country_code" class="form-label">رمز الدولة</label>
                    <input type="text" name="zatca_country_code" id="zatca_country_code" class="form-control" value="{{ old('zatca_country_code', $settings['zatca_country_code'] ?? 'SA') }}" maxlength="2" dir="ltr">
                    @error('zatca_country_code')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="zatca_egs_serial" class="form-label">الرقم التسلسلي لوحدة الفوترة</label>
                    <input type="text" name="zatca_egs_serial" id="zatca_egs_serial" class="form-control" value="{{ old('zatca_egs_serial', $settings['zatca_egs_serial'] ?? '') }}" dir="ltr">
                    <small style="color: #64748b; font-size: 12px;">Serial أو UUID الخاص بجهاز/وحدة EGS المستخدمة لاحقًا في الربط.</small>
                    @error('zatca_egs_serial')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="zatca_solution_name" class="form-label">اسم الحل التقني</label>
                    <input type="text" name="zatca_solution_name" id="zatca_solution_name" class="form-control" value="{{ old('zatca_solution_name', $settings['zatca_solution_name'] ?? '') }}" placeholder="مثال: CRM Internal E-Invoicing">
                    @error('zatca_solution_name')
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
            <h3 style="margin-bottom: 16px;">📇 جهات اتصال الفاتورة</h3>
            <p style="color: #64748b; font-size: 13px; margin-bottom: 12px;">أشخاص التواصل التي تظهر في أسفل كل فاتورة مبيعات ومشتريات</p>

            <div id="contacts-container">
                @php $existingContacts = json_decode($settings['invoice_contacts'] ?? '[]', true) ?: []; @endphp
                @foreach($existingContacts as $index => $contact)
                <div class="contact-row" style="display: flex; gap: 12px; align-items: center; margin-bottom: 10px;">
                    <input type="text" name="contacts[{{ $index }}][name]" class="form-control" placeholder="اسم جهة الاتصال" value="{{ $contact['name'] ?? '' }}" style="flex: 1;">
                    <input type="text" name="contacts[{{ $index }}][phone]" class="form-control" placeholder="رقم الهاتف" value="{{ $contact['phone'] ?? '' }}" style="flex: 1;">
                    <button type="button" class="btn" onclick="removeContact(this)" style="padding: 8px 12px; color: #dc2626;">✕</button>
                </div>
                @endforeach
            </div>

            <button type="button" class="btn" onclick="addContact()" style="margin-bottom: 16px;">+ إضافة جهة اتصال</button>

            @if(feature_enabled('color_palette'))
            <hr style="margin: 24px 0; border-color: var(--border-color);">
            <h3 style="margin-bottom: 16px;">🎨 ألوان الموقع</h3>
            <p style="color: #64748b; font-size: 13px; margin-bottom: 16px;">اختر لوحة الألوان المفضلة للموقع</p>

            <div class="palette-picker" style="display: flex; flex-wrap: wrap; gap: 12px;">
                @php
                    $palettes = [
                        'cyan'    => ['label' => 'سماوي',  'primary' => '#0891b2', 'dark' => '#0e7490', 'light' => '#06b6d4'],
                        'blue'    => ['label' => 'أزرق',   'primary' => '#2563eb', 'dark' => '#1d4ed8', 'light' => '#3b82f6'],
                        'indigo'  => ['label' => 'نيلي',   'primary' => '#6366f1', 'dark' => '#4f46e5', 'light' => '#818cf8'],
                        'purple'  => ['label' => 'بنفسجي', 'primary' => '#7c3aed', 'dark' => '#6d28d9', 'light' => '#8b5cf6'],
                        'rose'    => ['label' => 'وردي',   'primary' => '#e11d48', 'dark' => '#be123c', 'light' => '#f43f5e'],
                        'emerald' => ['label' => 'أخضر',   'primary' => '#059669', 'dark' => '#047857', 'light' => '#10b981'],
                        'amber'   => ['label' => 'ذهبي',   'primary' => '#d97706', 'dark' => '#b45309', 'light' => '#f59e0b'],
                        'slate'   => ['label' => 'رمادي',  'primary' => '#475569', 'dark' => '#334155', 'light' => '#64748b'],
                    ];
                    $activePalette = $settings['color_palette'] ?? 'cyan';
                @endphp
                @foreach($palettes as $key => $palette)
                <label class="palette-swatch {{ $activePalette === $key ? 'palette-active' : '' }}" onclick="selectPalette('{{ $key }}', '{{ $palette['primary'] }}', '{{ $palette['dark'] }}', '{{ $palette['light'] }}')">
                    <input type="radio" name="color_palette" value="{{ $key }}" {{ $activePalette === $key ? 'checked' : '' }} hidden>
                    <div class="swatch-circle" style="background: {{ $palette['primary'] }};"></div>
                    <span class="swatch-label">{{ $palette['label'] }}</span>
                </label>
                @endforeach
            </div>
            @endif

            @if(feature_enabled('invoice_customization'))
            <hr style="margin: 24px 0; border-color: var(--border-color);">
            <h3 style="margin-bottom: 16px;">📝 تخصيص الفواتير</h3>

            <div class="form-group">
                <label for="invoice_note" class="form-label">ملاحظة الفاتورة</label>
                <textarea name="invoice_note" id="invoice_note" class="form-control" rows="2" placeholder="ملاحظة تظهر أسفل الفاتورة">{{ old('invoice_note', $settings['invoice_note'] ?? '') }}</textarea>
                <small style="color: #64748b; font-size: 12px;">تظهر في كل فاتورة مطبوعة</small>
                @error('invoice_note')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="invoice_footer" class="form-label">فوتر الفاتورة</label>
                <textarea name="invoice_footer" id="invoice_footer" class="form-control" rows="2" placeholder="نص يظهر في أسفل الفاتورة">{{ old('invoice_footer', $settings['invoice_footer'] ?? '') }}</textarea>
                @error('invoice_footer')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="show_customer_balance" value="1" {{ old('show_customer_balance', $settings['show_customer_balance'] ?? '0') == '1' ? 'checked' : '' }}>
                    <span>عرض رصيد العميل في الفاتورة</span>
                </label>
            </div>
            @endif

            <hr style="margin: 24px 0; border-color: var(--border-color);">
            <h3 style="margin-bottom: 16px;">👥 أنواع العملاء</h3>
            <p style="color: #64748b; font-size: 13px; margin-bottom: 12px;">تحديد أنواع (تصنيفات) العملاء المتاحة في النظام</p>

            <div id="item-types-container">
                @php
                    $existingTypes = [];
                    if (!empty($settings['customer_item_types'])) {
                        $decoded = json_decode($settings['customer_item_types'], true);
                        if (is_array($decoded)) $existingTypes = $decoded;
                    }
                    if (empty($existingTypes)) {
                        $existingTypes = customer_item_types();
                    }
                @endphp
                @foreach($existingTypes as $tIdx => $type)
                <div class="item-type-row" style="display: flex; gap: 12px; align-items: center; margin-bottom: 10px;">
                    <input type="text" name="customer_item_types[{{ $tIdx }}][value]" class="form-control" placeholder="القيمة (بالإنجليزية)" value="{{ $type['value'] ?? '' }}" style="flex: 1;" dir="ltr">
                    <input type="text" name="customer_item_types[{{ $tIdx }}][label]" class="form-control" placeholder="الاسم (بالعربية)" value="{{ $type['label'] ?? '' }}" style="flex: 1;">
                    <button type="button" class="btn" onclick="removeItemType(this)" style="padding: 8px 12px; color: #dc2626;">✕</button>
                </div>
                @endforeach
            </div>

            <button type="button" class="btn" onclick="addItemType()" style="margin-bottom: 16px;">+ إضافة نوع</button>

            <hr style="margin: 24px 0; border-color: var(--border-color);">
            <h3 style="margin-bottom: 16px;">💰 إعدادات مالية</h3>

            <div class="form-group">
                <label for="treasury_opening_balance" class="form-label">رأس المال / الرصيد الافتتاحي للخزنة</label>
                <input type="number" step="0.01" name="treasury_opening_balance" id="treasury_opening_balance" class="form-control" value="{{ old('treasury_opening_balance', $settings['treasury_opening_balance'] ?? 0) }}" min="0">
                <small style="color: #64748b; font-size: 12px;">المبلغ الذي بدأت به الشركة (رأس مال الشركاء) - يُضاف لرصيد الخزنة</small>
                @error('treasury_opening_balance')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

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
.palette-swatch {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    padding: 10px 14px;
    border-radius: 10px;
    border: 2px solid var(--border);
    transition: all 0.2s;
    min-width: 70px;
}
.palette-swatch:hover {
    border-color: var(--primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.palette-active {
    border-color: var(--primary) !important;
    background: var(--bg-light);
    box-shadow: 0 0 0 3px rgba(var(--primary-rgb, 8, 145, 178), 0.15);
}
.swatch-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
.swatch-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--text-secondary);
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

var contactIndex = {{ count($existingContacts) ?: 1 }};

function addContact() {
    var container = document.getElementById('contacts-container');
    var row = document.createElement('div');
    row.className = 'contact-row';
    row.style.cssText = 'display: flex; gap: 12px; align-items: center; margin-bottom: 10px;';
    row.innerHTML = '<input type="text" name="contacts[' + contactIndex + '][name]" class="form-control" placeholder="اسم جهة الاتصال" style="flex: 1;">' +
        '<input type="text" name="contacts[' + contactIndex + '][phone]" class="form-control" placeholder="رقم الهاتف" style="flex: 1;">' +
        '<button type="button" class="btn" onclick="removeContact(this)" style="padding: 8px 12px; color: #dc2626;">✕</button>';
    container.appendChild(row);
    contactIndex++;
}

function removeContact(btn) {
    btn.parentElement.remove();
}

var itemTypeIndex = {{ count($existingTypes) }};

function addItemType() {
    var container = document.getElementById('item-types-container');
    var row = document.createElement('div');
    row.className = 'item-type-row';
    row.style.cssText = 'display: flex; gap: 12px; align-items: center; margin-bottom: 10px;';
    row.innerHTML = '<input type="text" name="customer_item_types[' + itemTypeIndex + '][value]" class="form-control" placeholder="القيمة (بالإنجليزية)" style="flex: 1;" dir="ltr">' +
        '<input type="text" name="customer_item_types[' + itemTypeIndex + '][label]" class="form-control" placeholder="الاسم (بالعربية)" style="flex: 1;">' +
        '<button type="button" class="btn" onclick="removeItemType(this)" style="padding: 8px 12px; color: #dc2626;">✕</button>';
    container.appendChild(row);
    itemTypeIndex++;
}

function removeItemType(btn) {
    btn.parentElement.remove();
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

function selectPalette(key, primary, dark, light) {
    // Update active state
    document.querySelectorAll('.palette-swatch').forEach(function(s) {
        s.classList.remove('palette-active');
    });
    event.currentTarget.classList.add('palette-active');

    // Live preview - update CSS variables
    document.documentElement.style.setProperty('--primary', primary);
    document.documentElement.style.setProperty('--primary-dark', dark);
    document.documentElement.style.setProperty('--primary-light', light);
}
</script>
@endpush
