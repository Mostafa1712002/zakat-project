@extends('layouts.app')

@section('title', 'تعديل الموظف')

@section('content')
<div class="page-header">
    <div>
        <h1>تعديل الموظف</h1>
        <p>{{ $employee->name }} - {{ $employee->employee_code }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('employees.show', $employee) }}" class="btn">عرض البيانات</a>
        <a href="{{ route('employees.index') }}" class="btn">رجوع</a>
    </div>
</div>

<form action="{{ route('employees.update', $employee) }}" method="POST">
    @csrf
    @method('PUT')

    @if ($errors->any())
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <strong>يرجى تصحيح الأخطاء التالية:</strong>
            <ul style="margin: 10px 0 0 0; padding-right: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- User Account Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">حساب تسجيل الدخول</h3>
        </div>
        <div class="card-body">
            @if($employee->user_id)
                {{-- الموظف لديه حساب مسبقاً --}}
                <div class="alert alert-success" style="margin-bottom: 16px;">
                    <strong>الموظف لديه حساب:</strong> {{ $employee->user->email }}
                </div>

                <div class="form-group">
                    <label class="checkbox-label-main">
                        <input type="checkbox" name="remove_account" id="removeAccount" value="1">
                        <span style="color: #dc2626;">إلغاء ربط الحساب بالموظف</span>
                    </label>
                    <small class="text-muted d-block">سيتم فقط إلغاء الربط ولن يتم حذف حساب المستخدم</small>
                </div>

                <div id="accountEditSection">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email" class="form-label">البريد الإلكتروني *</label>
                            <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $employee->user->email) }}" required>
                            @error('email')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password" class="form-label">كلمة المرور الجديدة</label>
                            <input type="password" name="password" id="password" class="form-control" minlength="8">
                            <small class="text-muted">اتركه فارغاً للإبقاء على كلمة المرور الحالية</small>
                            @error('password')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation" class="form-label">تأكيد كلمة المرور</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                        </div>
                    </div>
                </div>
            @else
                {{-- الموظف ليس لديه حساب --}}
                <div class="form-group">
                    <label class="checkbox-label-main">
                        <input type="checkbox" name="create_account" id="createAccount" value="1" {{ old('create_account') ? 'checked' : '' }}>
                        <span>إنشاء حساب تسجيل دخول للموظف</span>
                    </label>
                    <small class="text-muted d-block">يمكن للموظف تسجيل الدخول للنظام ورؤية بياناته ومعاملاته</small>
                </div>

                <div id="accountSection" style="display: none;">
                    @if($availableUsers->count() > 0)
                    <div class="form-group">
                        <label class="form-label">طريقة الإنشاء</label>
                        <div class="user-type-selector">
                            <label class="radio-label">
                                <input type="radio" name="user_type" value="existing" id="userTypeExisting" {{ old('user_type', 'existing') == 'existing' ? 'checked' : '' }}>
                                <span>اختيار من مستخدم موجود</span>
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="user_type" value="new" id="userTypeNew" {{ old('user_type') == 'new' ? 'checked' : '' }}>
                                <span>إنشاء مستخدم جديد</span>
                            </label>
                        </div>
                    </div>

                    <div id="existingUserSection" class="user-section">
                        <div class="form-group">
                            <label for="existing_user_id" class="form-label">اختر المستخدم *</label>
                            <select name="existing_user_id" id="existing_user_id" class="form-control">
                                <option value="">اختر مستخدم...</option>
                                @foreach($availableUsers as $user)
                                    <option value="{{ $user->id }}" {{ old('existing_user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} - {{ $user->email }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">المستخدمين الذين لديهم دور "موظف" ولم يتم ربطهم بموظف بعد</small>
                            @error('existing_user_id')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div id="newUserSection" class="user-section" style="display: none;">
                        <input type="hidden" name="create_new_user" id="createNewUser" value="0">
                    @else
                    <div id="newUserSection" class="user-section">
                        <input type="hidden" name="create_new_user" value="1">
                        <div class="alert alert-info" style="margin-bottom: 16px;">
                            <strong>ملاحظة:</strong> لا يوجد مستخدمين متاحين بدور "موظف". سيتم إنشاء حساب جديد.
                        </div>
                    @endif
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email" class="form-label">البريد الإلكتروني *</label>
                                <input type="email" name="email" id="emailNew" class="form-control" value="{{ old('email') }}">
                                @error('email')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="password" class="form-label">كلمة المرور *</label>
                                <input type="password" name="password" id="passwordNew" class="form-control" minlength="8">
                                @error('password')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password_confirmation" class="form-label">تأكيد كلمة المرور *</label>
                                <input type="password" name="password_confirmation" id="password_confirmation_new" class="form-control">
                            </div>
                        </div>
                    </div>
                    @if($availableUsers->count() > 0)
                    </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Employee Info Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">البيانات الشخصية</h3>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label for="name" class="form-label">الاسم *</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $employee->name) }}" required>
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="employee_code" class="form-label">كود الموظف</label>
                    <input type="text" name="employee_code" id="employee_code" class="form-control" value="{{ old('employee_code', $employee->employee_code) }}">
                    @error('employee_code')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="phone" class="form-label">الهاتف</label>
                <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $employee->phone) }}">
                @error('phone')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="address" class="form-label">العنوان</label>
                <textarea name="address" id="address" class="form-control" rows="2">{{ old('address', $employee->address) }}</textarea>
                @error('address')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <!-- Job Info Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">البيانات الوظيفية</h3>
        </div>
        <div class="card-body">
            <div class="form-row-3">
                <div class="form-group">
                    <label for="job_title" class="form-label">المسمى الوظيفي</label>
                    <input type="text" name="job_title" id="job_title" class="form-control" value="{{ old('job_title', $employee->job_title) }}">
                    @error('job_title')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="department" class="form-label">القسم</label>
                    <input type="text" name="department" id="department" class="form-control" value="{{ old('department', $employee->department) }}">
                    @error('department')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="branch_id" class="form-label">الفرع</label>
                    <select name="branch_id" id="branch_id" class="form-control">
                        <option value="">بدون فرع</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id', $employee->branch_id) == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('branch_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row-3">
                <div class="form-group">
                    <label for="hire_date" class="form-label">تاريخ التعيين</label>
                    <input type="date" name="hire_date" id="hire_date" class="form-control" value="{{ old('hire_date', $employee->hire_date?->format('Y-m-d')) }}">
                    @error('hire_date')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="termination_date" class="form-label">تاريخ إنهاء الخدمة</label>
                    <input type="date" name="termination_date" id="termination_date" class="form-control" value="{{ old('termination_date', $employee->termination_date?->format('Y-m-d')) }}">
                    @error('termination_date')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="salary" class="form-label">الراتب</label>
                    <input type="number" step="0.01" name="salary" id="salary" class="form-control" value="{{ old('salary', $employee->salary) }}" min="0">
                    @error('salary')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="is_active" class="form-label">الحالة</label>
                    <select name="is_active" id="is_active" class="form-control">
                        <option value="1" {{ old('is_active', $employee->is_active) == 1 ? 'selected' : '' }}>نشط</option>
                        <option value="0" {{ old('is_active', $employee->is_active) == 0 ? 'selected' : '' }}>غير نشط</option>
                    </select>
                    @error('is_active')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Notes Section -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="form-group">
                <label for="notes" class="form-label">ملاحظات</label>
                <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes', $employee->notes) }}</textarea>
                @error('notes')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
        <a href="{{ route('employees.index') }}" class="btn">إلغاء</a>
    </div>
</form>
@endsection

@push('styles')
<style>
    .mb-4 { margin-bottom: 20px; }
    .d-block { display: block; }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 16px;
    }

    .form-row-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 16px;
    }

    .checkbox-label-main {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
        cursor: pointer;
    }

    .checkbox-label-main input[type="checkbox"] {
        width: 20px;
        height: 20px;
    }

    .text-muted {
        color: var(--text-muted);
        font-size: 12px;
    }

    .user-type-selector {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 20px;
    }

    .radio-label {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        background: var(--bg);
        border: 2px solid var(--border);
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        flex: 1;
        min-width: 200px;
        max-width: 300px;
    }

    .radio-label:hover {
        border-color: var(--primary);
    }

    .radio-label input[type="radio"]:checked + span {
        color: var(--primary);
        font-weight: 600;
    }

    .radio-label:has(input[type="radio"]:checked) {
        border-color: var(--primary);
        background: rgba(8,145,178,0.05);
    }

    .user-section {
        padding: 16px;
        background: var(--bg);
        border-radius: 8px;
        margin-top: 16px;
    }

    .alert-success {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
        padding: 12px 16px;
        border-radius: 8px;
    }

    .alert-info {
        background: #e0f2fe;
        color: #0369a1;
        border: 1px solid #7dd3fc;
        padding: 12px 16px;
        border-radius: 8px;
    }

    @media (max-width: 992px) {
        .form-row-3 {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .user-type-selector {
            flex-direction: column;
        }
        .radio-label {
            width: 100%;
            min-width: unset;
            max-width: unset;
        }
        .form-row,
        .form-row-3 {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($employee->user_id)
    // للموظف الذي لديه حساب
    const removeAccountCheckbox = document.getElementById('removeAccount');
    const accountEditSection = document.getElementById('accountEditSection');

    if (removeAccountCheckbox && accountEditSection) {
        removeAccountCheckbox.addEventListener('change', function() {
            if (this.checked) {
                accountEditSection.style.display = 'none';
            } else {
                accountEditSection.style.display = 'block';
            }
        });
    }
    @else
    // للموظف الذي ليس لديه حساب
    const createAccountCheckbox = document.getElementById('createAccount');
    const accountSection = document.getElementById('accountSection');
    const userTypeExisting = document.getElementById('userTypeExisting');
    const userTypeNew = document.getElementById('userTypeNew');
    const existingUserSection = document.getElementById('existingUserSection');
    const newUserSection = document.getElementById('newUserSection');
    const createNewUserInput = document.getElementById('createNewUser');

    function toggleAccountSection() {
        if (createAccountCheckbox && createAccountCheckbox.checked) {
            accountSection.style.display = 'block';
            toggleUserSections();
        } else if (accountSection) {
            accountSection.style.display = 'none';
        }
    }

    function toggleUserSections() {
        if (!userTypeExisting || !userTypeNew) return;

        if (userTypeExisting.checked) {
            if (existingUserSection) existingUserSection.style.display = 'block';
            if (newUserSection) newUserSection.style.display = 'none';
            if (createNewUserInput) createNewUserInput.value = '0';
        } else {
            if (existingUserSection) existingUserSection.style.display = 'none';
            if (newUserSection) newUserSection.style.display = 'block';
            if (createNewUserInput) createNewUserInput.value = '1';
        }
    }

    if (createAccountCheckbox) {
        toggleAccountSection();
        createAccountCheckbox.addEventListener('change', toggleAccountSection);
    }

    if (userTypeExisting && userTypeNew) {
        userTypeExisting.addEventListener('change', toggleUserSections);
        userTypeNew.addEventListener('change', toggleUserSections);
    }
    @endif
});
</script>
@endpush
