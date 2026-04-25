@php
    /** @var \App\Domain\Customer\Models\Customer|null $customer */
    $customer ??= null;
    $val = fn (string $field, $default = null) => old($field, $customer?->{$field} ?? $default);
    $isExempt = (bool) old('is_tax_exempt', $customer?->is_tax_exempt ?? false);
    $type = old('type', $customer?->type ?? 'company');
@endphp

<div x-data="{ type: @js($type), isExempt: @js($isExempt) }">
    <div class="card" style="padding:1rem;margin-bottom:1rem">
        <h3>📋 المعلومات الأساسية</h3>

        <div class="form-row" style="display:grid;grid-template-columns:repeat(2,1fr);gap:1rem">
            <div class="form-group">
                <label for="name">الاسم <span style="color:red">*</span></label>
                <input type="text" id="name" name="name" class="form-control"
                       value="{{ $val('name') }}" required maxlength="255">
                @error('name')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="type">نوع العميل <span style="color:red">*</span></label>
                <select id="type" name="type" class="form-control" x-model="type" required>
                    <option value="company">شركة</option>
                    <option value="government">جهة حكومية</option>
                    <option value="individual">فرد</option>
                </select>
                @error('type')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="phone">الجوال</label>
                <input type="text" id="phone" name="phone" class="form-control"
                       value="{{ $val('phone') }}" maxlength="20">
                @error('phone')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="email">البريد الإلكتروني</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="{{ $val('email') }}" maxlength="255">
                @error('email')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="account_manager_id">مدير الحساب</label>
                <select id="account_manager_id" name="account_manager_id" class="form-control">
                    <option value="">— لا يوجد —</option>
                    @foreach ($managers as $manager)
                        <option value="{{ $manager->id }}" @selected((string) $val('account_manager_id') === (string) $manager->id)>
                            {{ $manager->name }}
                        </option>
                    @endforeach
                </select>
                @error('account_manager_id')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="branch_id">الفرع</label>
                <select id="branch_id" name="branch_id" class="form-control">
                    <option value="">— غير محدد —</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) $val('branch_id') === (string) $branch->id)>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
                @error('branch_id')<span class="form-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="form-group">
            <label for="notes">ملاحظات</label>
            <textarea id="notes" name="notes" class="form-control" rows="3">{{ $val('notes') }}</textarea>
            @error('notes')<span class="form-error">{{ $message }}</span>@enderror
        </div>
    </div>

    <div class="card" style="padding:1rem;margin-bottom:1rem">
        <h3>🧾 بيانات الفوترة (ZATCA)</h3>

        <div class="form-row" style="display:grid;grid-template-columns:repeat(2,1fr);gap:1rem">
            <div class="form-group" x-show="type === 'company'">
                <label class="form-check">
                    <input type="hidden" name="is_tax_exempt" value="0">
                    <input type="checkbox" name="is_tax_exempt" value="1"
                           x-model="isExempt"
                           @if($isExempt) checked @endif>
                    معفى من الضريبة
                </label>
            </div>

            <div class="form-group" x-show="type === 'company' && !isExempt" x-cloak>
                <label for="vat_number">الرقم الضريبي (15 رقم يبدأ وينتهي بـ 3) <span style="color:red">*</span></label>
                <input type="text" id="vat_number" name="vat_number" class="form-control"
                       value="{{ $val('vat_number') }}" maxlength="15"
                       pattern="3\d{13}3" placeholder="3xxxxxxxxxxxxx3">
                @error('vat_number')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="cr_number">السجل التجاري</label>
                <input type="text" id="cr_number" name="cr_number" class="form-control"
                       value="{{ $val('cr_number') }}" maxlength="20">
                @error('cr_number')<span class="form-error">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>

    <div class="card" style="padding:1rem;margin-bottom:1rem">
        <h3>📍 العنوان الوطني (REGA)</h3>
        <p class="text-muted" style="margin-bottom:1rem">مطلوب لإصدار فواتير ZATCA</p>

        <div class="form-row" style="display:grid;grid-template-columns:repeat(2,1fr);gap:1rem">
            <div class="form-group">
                <label for="street_name">اسم الشارع</label>
                <input type="text" id="street_name" name="street_name" class="form-control"
                       value="{{ $val('street_name') }}" maxlength="127">
                @error('street_name')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="building_number">رقم المبنى (4 أرقام)</label>
                <input type="text" id="building_number" name="building_number" class="form-control"
                       value="{{ $val('building_number') }}" maxlength="4" pattern="\d{4}">
                @error('building_number')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="secondary_number">الرقم الفرعي (4 أرقام)</label>
                <input type="text" id="secondary_number" name="secondary_number" class="form-control"
                       value="{{ $val('secondary_number') }}" maxlength="4" pattern="\d{4}">
                @error('secondary_number')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="district">الحي</label>
                <input type="text" id="district" name="district" class="form-control"
                       value="{{ $val('district') }}" maxlength="127">
                @error('district')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="city">المدينة</label>
                <input type="text" id="city" name="city" class="form-control"
                       value="{{ $val('city') }}" maxlength="127">
                @error('city')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="postal_code">الرمز البريدي (5 أرقام)</label>
                <input type="text" id="postal_code" name="postal_code" class="form-control"
                       value="{{ $val('postal_code') }}" maxlength="5" pattern="\d{5}">
                @error('postal_code')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="country_code">رمز الدولة</label>
                <input type="text" id="country_code" name="country_code" class="form-control"
                       value="{{ $val('country_code', 'SA') }}" maxlength="2">
                @error('country_code')<span class="form-error">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>
</div>
