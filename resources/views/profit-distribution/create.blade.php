@extends('layouts.app')

@section('title', 'توزيع أرباح جديد')

@section('content')
<div class="page-header">
    <div>
        <h1>💰 توزيع أرباح جديد</h1>
        <p>توزيع الأرباح على الشركاء حسب نسب الملكية</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('profit-distribution.index') }}" class="btn">← رجوع</a>
    </div>
</div>

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if($totalPercentage != 100)
<div class="alert alert-warning">
    ⚠️ تنبيه: إجمالي نسب الملكية ({{ $totalPercentage }}%) لا يساوي 100%. سيتم توزيع الأرباح بنسبة كل شريك من الإجمالي.
</div>
@endif

<div class="grid-2">
    <div class="card">
        <div class="card-body">
            <h3 style="margin-bottom: 16px;">📋 بيانات التوزيع</h3>

            <form action="{{ route('profit-distribution.store') }}" method="POST" id="distributionForm">
                @csrf

                <div class="form-group">
                    <label for="total_profit" class="form-label">إجمالي الأرباح للتوزيع (ج.م) *</label>
                    <input type="number" step="0.01" name="total_profit" id="total_profit" class="form-control" value="{{ old('total_profit') }}" required min="0.01">
                    @error('total_profit')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="period" class="form-label">الفترة *</label>
                        <input type="text" name="period" id="period" class="form-control" value="{{ old('period', date('Y')) }}" required placeholder="مثال: 2026 أو 2026-Q4">
                        <small class="form-help">السنة أو الربع السنوي</small>
                        @error('period')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="distribution_date" class="form-label">تاريخ التوزيع *</label>
                        <input type="date" name="distribution_date" id="distribution_date" class="form-control" value="{{ old('distribution_date', date('Y-m-d')) }}" required>
                        @error('distribution_date')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="payment_method" class="form-label">طريقة الدفع *</label>
                    <select name="payment_method" id="payment_method" class="form-control" required>
                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>💵 نقدي</option>
                        <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>🏦 تحويل بنكي</option>
                        <option value="check" {{ old('payment_method') == 'check' ? 'selected' : '' }}>📝 شيك</option>
                    </select>
                    @error('payment_method')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">الوصف</label>
                    <textarea name="description" id="description" class="form-control" rows="2" placeholder="وصف التوزيع...">{{ old('description') }}</textarea>
                </div>

                <div class="form-group">
                    <label for="notes" class="form-label">ملاحظات</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="ملاحظات إضافية...">{{ old('notes') }}</textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-lg">💾 تنفيذ التوزيع</button>
                    <a href="{{ route('profit-distribution.index') }}" class="btn btn-lg">إلغاء</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 style="margin-bottom: 16px;">👥 معاينة التوزيع</h3>

            <div id="previewSection">
                <p class="text-muted">أدخل مبلغ الأرباح لمعاينة التوزيع</p>
            </div>

            <div id="partnersPreview" style="display: none;">
                <div class="preview-table overflow-auto">
                    <table class="table text-nowrap">
                        <thead>
                            <tr>
                                <th>الشريك</th>
                                <th>النسبة</th>
                                <th>المبلغ</th>
                            </tr>
                        </thead>
                        <tbody id="previewBody">
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td><strong>الإجمالي</strong></td>
                                <td><strong>{{ $totalPercentage }}%</strong></td>
                                <td><strong id="previewTotal">0.00</strong> ج.م</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="partners-info" style="margin-top: 1rem;">
                <h4>الشركاء النشطين:</h4>
                <ul>
                    @forelse($partners as $partner)
                    <li>{{ $partner->name }} - <strong>{{ $partner->ownership_percentage }}%</strong></li>
                    @empty
                    <li class="text-muted">لا يوجد شركاء نشطين</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.grid-2 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 340px), 1fr));
    gap: 1.5rem;
}
.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}
.form-help {
    display: block;
    color: var(--text-muted);
    font-size: 0.75rem;
    margin-top: 0.25rem;
}
.form-actions {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border);
}
.btn-lg {
    padding: 12px 24px;
    font-size: 1rem;
}
.preview-table {
    margin-top: 1rem;
}
.preview-table .table {
    font-size: 0.875rem;
}
.total-row {
    background: rgba(59,130,246,0.1);
}
.total-row td {
    border-top: 2px solid var(--primary);
}
.partners-info h4 {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin-bottom: 0.5rem;
}
.partners-info ul {
    list-style: none;
    padding: 0;
    margin: 0;
}
.partners-info li {
    padding: 0.25rem 0;
    font-size: 0.875rem;
}
.alert { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
.alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
.alert-warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }

@media (max-width: 768px) {
    .grid-2 {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const totalProfitInput = document.getElementById('total_profit');
    const previewSection = document.getElementById('previewSection');
    const partnersPreview = document.getElementById('partnersPreview');
    const previewBody = document.getElementById('previewBody');
    const previewTotal = document.getElementById('previewTotal');

    let debounceTimer;

    totalProfitInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(updatePreview, 300);
    });

    function updatePreview() {
        const totalProfit = parseFloat(totalProfitInput.value) || 0;

        if (totalProfit <= 0) {
            previewSection.style.display = 'block';
            partnersPreview.style.display = 'none';
            return;
        }

        fetch('{{ route("profit-distribution.preview") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ total_profit: totalProfit })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                previewSection.style.display = 'none';
                partnersPreview.style.display = 'block';

                previewBody.innerHTML = '';
                data.partners.forEach(partner => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${partner.name}</td>
                        <td>${partner.percentage}%</td>
                        <td class="text-success">${partner.share.toLocaleString('en-US', {minimumFractionDigits: 2})} ج.م</td>
                    `;
                    previewBody.appendChild(row);
                });

                previewTotal.textContent = data.total.toLocaleString('en-US', {minimumFractionDigits: 2});
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
});
</script>
@endsection
