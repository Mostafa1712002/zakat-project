<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Catalog\Models\Service;
use App\Domain\Customer\Models\Customer;
use App\Domain\Sales\Actions\ConvertQuoteToInvoice;
use App\Domain\Sales\Actions\IssueInvoice;
use App\Domain\Sales\Models\Invoice;
use App\Domain\Sales\Models\Quote;
use App\Domain\Sales\Services\InvoiceCalculator;
use App\Domain\Sales\Services\InvoiceNumberGenerator;
use App\Http\Controllers\Controller;
use App\Jobs\SubmitInvoiceToZatca;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Admin Invoice management — list, create (direct or via quote conversion),
 * edit (drafts only), show, issue, resend-to-ZATCA, cancel, PDF.
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 5.7)
 */
class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Invoice::class);

        $query = Invoice::query()->with(['customer', 'creator']);

        $user = $request->user();
        if ($user && ! $user->can('invoices.view-all')) {
            $query->where('created_by', $user->getAuthIdentifier());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('zatca_status')) {
            $query->where('zatca_status', $request->string('zatca_status'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to'));
        }
        if ($search = trim((string) $request->get('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('event_name', 'like', "%{$search}%");
            });
        }

        $invoices = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        return view('admin.invoices.index', compact('invoices'));
    }

    public function create(): View
    {
        $this->authorize('create', Invoice::class);

        return view('admin.invoices.create', [
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'is_tax_exempt']),
            'services' => Service::with('unit')->where('is_active', true)
                ->orderBy('name')->get(['id', 'name', 'unit_id']),
            'defaultTaxRate' => (float) Setting::get('default_tax_rate', 15),
        ]);
    }

    public function store(
        Request $request,
        InvoiceNumberGenerator $numberGenerator,
        InvoiceCalculator $calculator,
    ): RedirectResponse {
        $this->authorize('create', Invoice::class);

        $data = $this->validateInvoice($request);

        $invoice = DB::transaction(function () use ($data, $request, $numberGenerator, $calculator) {
            $dueDays = (int) Setting::get('invoice_due_days', 30);
            $defaultRate = (float) Setting::get('default_tax_rate', 15);

            $invoice = Invoice::create([
                'invoice_number' => $numberGenerator->next(),
                'customer_id' => $data['customer_id'],
                'created_by' => $request->user()->getAuthIdentifier(),
                'event_name' => $data['event_name'],
                'event_start_date' => $data['event_start_date'] ?? null,
                'event_end_date' => $data['event_end_date'] ?? null,
                'event_location' => $data['event_location'] ?? null,
                'event_type' => $data['event_type'] ?? null,
                'status' => Invoice::STATUS_DRAFT,
                'zatca_status' => Invoice::ZATCA_PENDING,
                'due_date' => $data['due_date'] ?? Carbon::now()->addDays($dueDays)->toDateString(),
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] ?? [] as $i => $row) {
                $invoice->items()->create([
                    'service_id' => $row['service_id'],
                    'description' => $row['description'] ?? null,
                    'quantity' => $row['quantity'] ?? 1,
                    'unit_price' => $row['unit_price'] ?? 0,
                    'discount_amount' => $row['discount_amount'] ?? 0,
                    'tax_rate' => $row['tax_rate'] ?? $defaultRate,
                    'sort_order' => $row['sort_order'] ?? $i,
                ]);
            }

            $calculator->recalculate($invoice);
            return $invoice->refresh();
        });

        return redirect()
            ->route('admin.invoices.show', $invoice)
            ->with('success', 'تم إنشاء الفاتورة كمسودة');
    }

    public function show(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        $invoice->load(['customer', 'creator', 'quote', 'items.service.unit']);

        return view('admin.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice): View
    {
        $this->authorize('update', $invoice);

        $invoice->load('items.service');

        return view('admin.invoices.edit', [
            'invoice' => $invoice,
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'is_tax_exempt']),
            'services' => Service::with('unit')->where('is_active', true)
                ->orderBy('name')->get(['id', 'name', 'unit_id']),
            'defaultTaxRate' => (float) Setting::get('default_tax_rate', 15),
        ]);
    }

    public function update(Request $request, Invoice $invoice, InvoiceCalculator $calculator): RedirectResponse
    {
        $this->authorize('update', $invoice);

        $data = $this->validateInvoice($request);

        DB::transaction(function () use ($invoice, $data, $calculator) {
            $invoice->update([
                'customer_id' => $data['customer_id'],
                'event_name' => $data['event_name'],
                'event_start_date' => $data['event_start_date'] ?? null,
                'event_end_date' => $data['event_end_date'] ?? null,
                'event_location' => $data['event_location'] ?? null,
                'event_type' => $data['event_type'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $invoice->items()->delete();
            foreach ($data['items'] ?? [] as $i => $row) {
                $invoice->items()->create([
                    'service_id' => $row['service_id'],
                    'description' => $row['description'] ?? null,
                    'quantity' => $row['quantity'] ?? 1,
                    'unit_price' => $row['unit_price'] ?? 0,
                    'discount_amount' => $row['discount_amount'] ?? 0,
                    'tax_rate' => $row['tax_rate'] ?? 15,
                    'sort_order' => $row['sort_order'] ?? $i,
                ]);
            }

            $calculator->recalculate($invoice);
        });

        return redirect()
            ->route('admin.invoices.show', $invoice)
            ->with('success', 'تم تحديث الفاتورة');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $this->authorize('cancel', $invoice);

        $invoice->update(['status' => Invoice::STATUS_CANCELLED]);
        $invoice->delete();

        return redirect()
            ->route('admin.invoices.index')
            ->with('success', 'تم إلغاء الفاتورة');
    }

    public function convertFromQuote(Quote $quote, ConvertQuoteToInvoice $action): RedirectResponse
    {
        $this->authorize('create', Invoice::class);

        $invoice = $action->execute($quote);

        return redirect()
            ->route('admin.invoices.show', $invoice)
            ->with('success', 'تم تحويل عرض السعر إلى فاتورة');
    }

    public function issue(Invoice $invoice, IssueInvoice $action): RedirectResponse
    {
        $this->authorize('issue', $invoice);

        $action->execute($invoice);

        return redirect()
            ->route('admin.invoices.show', $invoice)
            ->with('success', 'تم إصدار الفاتورة');
    }

    public function resendToZatca(Invoice $invoice): RedirectResponse
    {
        $this->authorize('sendZatca', $invoice);

        SubmitInvoiceToZatca::dispatch($invoice->id);

        return redirect()
            ->route('admin.invoices.show', $invoice)
            ->with('success', 'تم إعادة إرسال الفاتورة إلى زاتكا');
    }

    public function pdf(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        $invoice->load(['customer', 'items.service']);

        return view('admin.invoices.pdf', compact('invoice'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateInvoice(Request $request): array
    {
        return $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'event_name' => ['required', 'string', 'max:255'],
            'event_start_date' => ['nullable', 'date'],
            'event_end_date' => ['nullable', 'date', 'after_or_equal:event_start_date'],
            'event_location' => ['nullable', 'string', 'max:255'],
            'event_type' => ['nullable', 'string', 'max:100'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.service_id' => ['required', 'integer', 'exists:services,id'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.sort_order' => ['nullable', 'integer'],
        ]);
    }
}
