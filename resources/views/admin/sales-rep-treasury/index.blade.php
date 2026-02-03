@extends('layouts.app')

@section('title', 'خزينات المندوبين')

@section('content')
<div class="page-header">
    <div>
        <h1>💰 خزينات المندوبين</h1>
        <p>إدارة خزينات المندوبين وسحب الأرصدة</p>
    </div>
</div>

<!-- إجمالي الأرصدة -->
<div class="card" style="margin-bottom: 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
    <div class="card-body" style="text-align: center; padding: 32px;">
        <h2 style="font-size: 36px; margin-bottom: 8px;">{{ number_format($totalBalance, 2) }} ج.م</h2>
        <p style="margin: 0; opacity: 0.9;">إجمالي أرصدة جميع المندوبين</p>
    </div>
</div>

<!-- قائمة المندوبين -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">📋 أرصدة المندوبين</h3>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>المندوب</th>
                    <th>الفرع</th>
                    <th>الرصيد</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salesReps as $salesRep)
                <tr>
                    <td>
                        <strong>{{ $salesRep->name }}</strong>
                        <br><small class="text-muted">{{ $salesRep->code }}</small>
                    </td>
                    <td>{{ $salesRep->branch?->name ?? '-' }}</td>
                    <td>
                        <strong class="{{ $salesRep->treasury_balance > 0 ? 'text-success' : '' }}" style="font-size: 18px;">
                            {{ number_format($salesRep->treasury_balance, 2) }} ج.م
                        </strong>
                    </td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('admin.sales-rep-treasury.show', $salesRep) }}" class="btn btn-sm">
                                👁️ التفاصيل
                            </a>
                            @if($salesRep->treasury_balance > 0)
                            <button type="button" class="btn btn-sm btn-primary" onclick="showWithdrawModal({{ $salesRep->id }}, '{{ $salesRep->name }}', {{ $salesRep->treasury_balance }})">
                                💸 سحب
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">لا يوجد مندوبين نشطين</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal السحب -->
<div id="withdrawModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>💸 سحب رصيد للخزينة الرئيسية</h3>
            <button type="button" class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="withdrawForm" method="POST">
            @csrf
            <div class="modal-body">
                <p>سحب من خزينة: <strong id="repName"></strong></p>
                <p>الرصيد المتاح: <strong id="repBalance" class="text-success"></strong></p>

                <div class="form-group">
                    <label for="amount" class="form-label">المبلغ المراد سحبه</label>
                    <input type="number" step="0.01" name="amount" id="withdrawAmount" class="form-control" required>
                    <small class="text-muted">اتركه فارغاً لسحب كامل الرصيد</small>
                </div>

                <div class="form-group">
                    <label for="notes" class="form-label">ملاحظات</label>
                    <textarea name="notes" id="withdrawNotes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" onclick="closeModal()">إلغاء</button>
                <button type="submit" class="btn btn-primary">💸 تأكيد السحب</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}
.modal-content {
    background: white;
    border-radius: 12px;
    width: 100%;
    max-width: 500px;
    margin: 20px;
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
}
.modal-header h3 {
    margin: 0;
}
.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: var(--text-muted);
}
.modal-body {
    padding: 20px;
}
.modal-footer {
    padding: 16px 20px;
    border-top: 1px solid var(--border);
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}
</style>
@endpush

@push('scripts')
<script>
function showWithdrawModal(repId, repName, balance) {
    document.getElementById('withdrawForm').action = '/admin/sales-rep-treasury/' + repId + '/withdraw';
    document.getElementById('repName').textContent = repName;
    document.getElementById('repBalance').textContent = balance.toFixed(2) + ' ج.م';
    document.getElementById('withdrawAmount').max = balance;
    document.getElementById('withdrawAmount').value = balance;
    document.getElementById('withdrawModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('withdrawModal').style.display = 'none';
}

document.getElementById('withdrawModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
@endpush
