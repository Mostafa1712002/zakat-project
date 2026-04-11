@extends('layouts.app')

@section('title', 'إشعار دائن - فاتورة ' . $sale->invoice_number)

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>إصدار إشعار دائن للفاتورة: {{ $sale->invoice_number }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('sales.credit-note.store', $sale) }}" method="POST">
                @csrf

                <div class="form-group mb-3">
                    <label for="reason">سبب الإشعار الدائن <span class="text-danger">*</span></label>
                    <textarea name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror"
                        rows="3" required>{{ old('reason') }}</textarea>
                    @error('reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <h6 class="mt-4 mb-3">بنود الفاتورة الأصلية</h6>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>تضمين</th>
                            <th>المنتج</th>
                            <th>الكمية الأصلية</th>
                            <th>كمية الإرجاع</th>
                            <th>سعر الوحدة</th>
                            <th>نسبة الضريبة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $index => $item)
                        <tr>
                            <td>
                                <input type="checkbox" name="include_item[{{ $index }}]"
                                    value="1" class="form-check-input item-checkbox"
                                    data-index="{{ $index }}" checked>
                            </td>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>
                                <input type="hidden" name="items[{{ $index }}][sale_item_id]" value="{{ $item->id }}">
                                <input type="number" name="items[{{ $index }}][quantity]"
                                    class="form-control form-control-sm item-qty"
                                    data-index="{{ $index }}"
                                    value="{{ $item->quantity }}"
                                    min="0.01" max="{{ $item->quantity }}" step="0.01">
                            </td>
                            <td>{{ number_format($item->unit_price, 2) }}</td>
                            <td>{{ $item->tax_rate }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('sales.show', $sale) }}" class="btn btn-secondary">إلغاء</a>
                    <button type="submit" class="btn btn-danger">إصدار إشعار دائن</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
