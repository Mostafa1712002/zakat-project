<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Catalog\Models\Service;
use App\Domain\Customer\Models\Customer;
use App\Domain\Sales\Actions\ApproveQuote;
use App\Domain\Sales\Actions\CreateQuote;
use App\Domain\Sales\Actions\RejectQuote;
use App\Domain\Sales\Actions\SubmitQuote;
use App\Domain\Sales\Models\Quote;
use App\Domain\Sales\Services\QuoteCalculator;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Admin Quote management — list, create, edit, show + workflow endpoints
 * (submit / approve / reject). Conversion to invoice arrives in Phase 5b.
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 5.4)
 */
class QuoteController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Quote::class);

        $query = Quote::query()->with(['customer', 'creator']);

        // Account Managers without elevated access only see their own quotes.
        $user = $request->user();
        if ($user && ! $user->can('quotes.view-all')) {
            $query->where('created_by', $user->getAuthIdentifier());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->integer('customer_id'));
        }

        if ($search = trim((string) $request->get('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('quote_number', 'like', "%{$search}%")
                    ->orWhere('event_name', 'like', "%{$search}%");
            });
        }

        $quotes = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();
        $customers = Customer::orderBy('name')->get(['id', 'name']);

        return view('admin.quotes.index', compact('quotes', 'customers'));
    }

    public function create(): View
    {
        $this->authorize('create', Quote::class);

        return view('admin.quotes.create', [
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'is_tax_exempt']),
            'services' => Service::with('unit')->where('is_active', true)
                ->orderBy('name')->get(['id', 'name', 'unit_id']),
            'defaultTaxRate' => (float) Setting::get('default_tax_rate', 15),
        ]);
    }

    public function store(Request $request, CreateQuote $action): RedirectResponse
    {
        $this->authorize('create', Quote::class);

        $data = $this->validateQuote($request);
        $quote = $action->execute($data, $request->user());

        return redirect()
            ->route('admin.quotes.show', $quote)
            ->with('success', 'تم إنشاء عرض السعر');
    }

    public function show(Quote $quote): View
    {
        $this->authorize('view', $quote);

        $quote->load(['customer', 'creator', 'approver', 'items.service.unit']);

        return view('admin.quotes.show', compact('quote'));
    }

    public function edit(Quote $quote): View
    {
        $this->authorize('update', $quote);

        $quote->load('items.service');

        return view('admin.quotes.edit', [
            'quote' => $quote,
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'is_tax_exempt']),
            'services' => Service::with('unit')->where('is_active', true)
                ->orderBy('name')->get(['id', 'name', 'unit_id']),
            'defaultTaxRate' => (float) Setting::get('default_tax_rate', 15),
        ]);
    }

    public function update(Request $request, Quote $quote, QuoteCalculator $calculator): RedirectResponse
    {
        $this->authorize('update', $quote);

        $data = $this->validateQuote($request);

        DB::transaction(function () use ($quote, $data, $calculator) {
            $quote->update([
                'customer_id' => $data['customer_id'],
                'event_name' => $data['event_name'],
                'event_start_date' => $data['event_start_date'] ?? null,
                'event_end_date' => $data['event_end_date'] ?? null,
                'event_location' => $data['event_location'] ?? null,
                'event_type' => $data['event_type'] ?? null,
                'valid_until' => $data['valid_until'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Replace line items wholesale — simplest correct behaviour for draft edits.
            $quote->items()->delete();
            foreach ($data['items'] ?? [] as $i => $row) {
                $quote->items()->create([
                    'service_id' => $row['service_id'],
                    'description' => $row['description'] ?? null,
                    'quantity' => $row['quantity'] ?? 1,
                    'unit_price' => $row['unit_price'] ?? 0,
                    'discount_amount' => $row['discount_amount'] ?? 0,
                    'tax_rate' => $row['tax_rate'] ?? 15,
                    'sort_order' => $row['sort_order'] ?? $i,
                ]);
            }

            $calculator->recalculate($quote);
        });

        return redirect()
            ->route('admin.quotes.show', $quote)
            ->with('success', 'تم تحديث عرض السعر');
    }

    public function destroy(Quote $quote): RedirectResponse
    {
        $this->authorize('delete', $quote);

        $quote->delete();

        return redirect()
            ->route('admin.quotes.index')
            ->with('success', 'تم حذف عرض السعر');
    }

    public function submit(Quote $quote, SubmitQuote $action): RedirectResponse
    {
        $this->authorize('submit', $quote);

        $action->execute($quote);

        return redirect()
            ->route('admin.quotes.show', $quote)
            ->with('success', 'تم تقديم عرض السعر للاعتماد');
    }

    public function approve(Request $request, Quote $quote, ApproveQuote $action): RedirectResponse
    {
        $this->authorize('approve', $quote);

        $action->execute($quote, $request->user());

        return redirect()
            ->route('admin.quotes.show', $quote)
            ->with('success', 'تم اعتماد عرض السعر');
    }

    public function reject(Request $request, Quote $quote, RejectQuote $action): RedirectResponse
    {
        $this->authorize('reject', $quote);

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $action->execute($quote, $request->user(), $validated['rejection_reason']);

        return redirect()
            ->route('admin.quotes.show', $quote)
            ->with('success', 'تم رفض عرض السعر');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateQuote(Request $request): array
    {
        return $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'event_name' => ['required', 'string', 'max:255'],
            'event_start_date' => ['nullable', 'date'],
            'event_end_date' => ['nullable', 'date', 'after_or_equal:event_start_date'],
            'event_location' => ['nullable', 'string', 'max:255'],
            'event_type' => ['nullable', 'string', 'max:100'],
            'valid_until' => ['nullable', 'date'],
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
