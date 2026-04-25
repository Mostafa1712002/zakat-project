@php
    /** @var \App\Domain\Sales\Models\Quote|null $quote */
    $quote ??= null;
    $isEdit = $quote && $quote->exists;
    $existingItems = $isEdit
        ? $quote->items->map(fn ($it) => [
            'service_id' => $it->service_id,
            'description' => $it->description,
            'quantity' => (float) $it->quantity,
            'unit_price' => (float) $it->unit_price,
            'discount_amount' => (float) $it->discount_amount,
            'tax_rate' => (float) $it->tax_rate,
        ])->values()->all()
        : [];
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
    </div>
@endif

<div x-data='quoteForm({
        defaultRate: {{ $defaultTaxRate }},
        services: @json($services),
        existing: @json($existingItems),
    })'>

    <div class="card">
        <h3>بيانات العميل والفعالية</h3>
        <div class="form-row">
            <div class="form-group">
                <label>العميل *</label>
                <select name="customer_id" class="form-control" required>
                    <option value="">— اختر —</option>
                    @foreach ($customers as $c)
                        <option value="{{ $c->id }}" @selected(old('customer_id', $quote?->customer_id) == $c->id)>
                            {{ $c->name }}@if ($c->is_tax_exempt) (معفى من الضريبة)@endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>اسم الفعالية *</label>
                <input type="text" name="event_name" class="form-control"
                       value="{{ old('event_name', $quote?->event_name) }}" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>تاريخ البدء</label>
                <input type="date" name="event_start_date" class="form-control"
                       value="{{ old('event_start_date', $quote?->event_start_date?->format('Y-m-d')) }}">
            </div>
            <div class="form-group">
                <label>تاريخ الانتهاء</label>
                <input type="date" name="event_end_date" class="form-control"
                       value="{{ old('event_end_date', $quote?->event_end_date?->format('Y-m-d')) }}">
            </div>
            <div class="form-group">
                <label>الموقع</label>
                <input type="text" name="event_location" class="form-control"
                       value="{{ old('event_location', $quote?->event_location) }}">
            </div>
            <div class="form-group">
                <label>نوع الفعالية</label>
                <input type="text" name="event_type" class="form-control"
                       value="{{ old('event_type', $quote?->event_type) }}">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>صالح حتى</label>
                <input type="date" name="valid_until" class="form-control"
                       value="{{ old('valid_until', $quote?->valid_until?->format('Y-m-d')) }}">
            </div>
        </div>

        <div class="form-group">
            <label>ملاحظات</label>
            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $quote?->notes) }}</textarea>
        </div>
    </div>

    <div class="card">
        <h3>البنود</h3>
        <table class="table">
            <thead>
                <tr>
                    <th style="width:25%">الخدمة</th>
                    <th>الوصف</th>
                    <th style="width:8%">الكمية</th>
                    <th style="width:10%">سعر الوحدة</th>
                    <th style="width:10%">الخصم</th>
                    <th style="width:8%">الضريبة %</th>
                    <th style="width:10%">الإجمالي</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(it, idx) in items" :key="idx">
                    <tr>
                        <td>
                            <select :name="`items[${idx}][service_id]`" x-model="it.service_id" required class="form-control">
                                <option value="">—</option>
                                <template x-for="s in services" :key="s.id">
                                    <option :value="s.id" x-text="s.name"></option>
                                </template>
                            </select>
                        </td>
                        <td>
                            <input type="text" :name="`items[${idx}][description]`" x-model="it.description" class="form-control">
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0.01" :name="`items[${idx}][quantity]`"
                                   x-model.number="it.quantity" class="form-control" required>
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" :name="`items[${idx}][unit_price]`"
                                   x-model.number="it.unit_price" class="form-control" required>
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" :name="`items[${idx}][discount_amount]`"
                                   x-model.number="it.discount_amount" class="form-control">
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" max="100" :name="`items[${idx}][tax_rate]`"
                                   x-model.number="it.tax_rate" class="form-control">
                        </td>
                        <td><strong x-text="lineTotal(it).toFixed(2)"></strong></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger" @click="removeItem(idx)">حذف</button>
                        </td>
                    </tr>
                </template>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="8">
                        <button type="button" class="btn btn-secondary" @click="addItem()">➕ إضافة بند</button>
                    </td>
                </tr>
                <tr>
                    <td colspan="6" class="text-end"><strong>الإجمالي قبل الضريبة:</strong></td>
                    <td colspan="2"><strong x-text="subtotal().toFixed(2)"></strong></td>
                </tr>
                <tr>
                    <td colspan="6" class="text-end"><strong>إجمالي الضريبة:</strong></td>
                    <td colspan="2"><strong x-text="taxTotal().toFixed(2)"></strong></td>
                </tr>
                <tr>
                    <td colspan="6" class="text-end"><strong>الإجمالي النهائي:</strong></td>
                    <td colspan="2"><strong x-text="grandTotal().toFixed(2)"></strong></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'تحديث' : 'حفظ كمسودة' }}</button>
        <a href="{{ route('admin.quotes.index') }}" class="btn btn-secondary">إلغاء</a>
    </div>
</div>

<script>
function quoteForm(config) {
    return {
        services: config.services,
        defaultRate: config.defaultRate,
        items: config.existing.length ? config.existing : [{
            service_id: '', description: '', quantity: 1,
            unit_price: 0, discount_amount: 0, tax_rate: config.defaultRate,
        }],
        addItem() {
            this.items.push({
                service_id: '', description: '', quantity: 1,
                unit_price: 0, discount_amount: 0, tax_rate: this.defaultRate,
            });
        },
        removeItem(i) {
            this.items.splice(i, 1);
            if (this.items.length === 0) this.addItem();
        },
        lineTaxable(it) {
            return Math.max(0, ((it.quantity || 0) * (it.unit_price || 0)) - (it.discount_amount || 0));
        },
        lineTax(it) {
            return this.lineTaxable(it) * ((it.tax_rate || 0) / 100);
        },
        lineTotal(it) {
            return this.lineTaxable(it) + this.lineTax(it);
        },
        subtotal() { return this.items.reduce((s, it) => s + (it.quantity || 0) * (it.unit_price || 0), 0); },
        taxTotal() { return this.items.reduce((s, it) => s + this.lineTax(it), 0); },
        grandTotal() { return this.items.reduce((s, it) => s + this.lineTotal(it), 0); },
    };
}
</script>
