<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Sales\Models\Invoice;
use App\Domain\Treasury\Actions\RecordPayment;
use App\Domain\Treasury\Actions\RefundPayment;
use App\Domain\Treasury\Models\Payment;
use App\Domain\Treasury\Models\Treasury;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::query()
            ->with(['invoice', 'customer', 'treasury', 'creator'])
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->filled('invoice_id'), fn ($q) => $q->where('invoice_id', $request->invoice_id))
            ->when($request->filled('method'), fn ($q) => $q->where('method', $request->method))
            ->when($request->filled('from'), fn ($q) => $q->where('payment_date', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->where('payment_date', '<=', $request->to))
            ->latest('payment_date')
            ->paginate(25)
            ->withQueryString();

        return view('admin.payments.index', compact('payments'));
    }

    public function create(Request $request)
    {
        $invoices = Invoice::with('customer')
            ->whereIn('status', ['issued', 'paid_partial'])
            ->whereColumn('paid_amount', '<', 'grand_total')
            ->latest('issued_at')
            ->limit(100)
            ->get();

        $treasuries = Treasury::where('is_active', true)->get();

        $selectedInvoice = $request->filled('invoice_id')
            ? Invoice::find($request->invoice_id)
            : null;

        return view('admin.payments.create', compact('invoices', 'treasuries', 'selectedInvoice'));
    }

    public function store(Request $request, RecordPayment $action)
    {
        $data = $request->validate([
            'invoice_id' => ['required', 'exists:invoices,id'],
            'treasury_id' => ['required', 'exists:treasuries,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'in:cash,bank,transfer,check'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'payment_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $payment = $action->execute($data, $request->user());

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('success', 'تم تسجيل الدفعة: ' . $payment->payment_number);
    }

    public function show(Payment $payment)
    {
        $payment->load(['invoice.customer', 'customer', 'treasury', 'creator']);

        return view('admin.payments.show', compact('payment'));
    }

    public function destroy(Payment $payment, RefundPayment $action, Request $request)
    {
        $action->execute($payment, $request->user());

        return redirect()
            ->route('admin.payments.index')
            ->with('success', 'تم استرجاع الدفعة');
    }
}
