<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Customer\Models\Customer;
use App\Domain\Customer\Validators\ZatcaCustomerValidator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CustomerStoreRequest;
use App\Http\Requests\Customer\CustomerUpdateRequest;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin Customer CRUD with Account Manager scoping and ZATCA fields.
 *
 * Reference: .kiro/specs/ammrk-platform/requirements.md (US-010, US-011)
 *            .kiro/specs/ammrk-platform/tasks.md (Task 4.3)
 */
class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Customer::class);

        $query = Customer::query()->with(['accountManager', 'branch']);

        if ($search = trim((string) $request->get('search', ''))) {
            $query->search($search);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        $customers = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();
        $branches  = Branch::orderBy('name')->get(['id', 'name']);

        return view('admin.customers.index', compact('customers', 'branches'));
    }

    public function create(): View
    {
        $this->authorize('create', Customer::class);

        return view('admin.customers.create', [
            'managers' => $this->managerOptions(),
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(CustomerStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_tax_exempt'] = $request->boolean('is_tax_exempt');
        $data['country_code'] = $data['country_code'] ?? 'SA';

        // Account Manager (without elevated roles) can only own their customers.
        $user = $request->user();
        if ($user && $user->hasRole('Account Manager') && ! $user->hasAnyRole(['Admin', 'Super Admin', 'Accountant'])) {
            $data['account_manager_id'] = $user->getAuthIdentifier();
        }

        $customer = Customer::create($data);

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('success', 'تم إضافة العميل بنجاح');
    }

    public function show(Customer $customer): View
    {
        $this->authorize('view', $customer);

        $customer->load(['accountManager', 'branch', 'contacts']);
        $zatcaCheck = ZatcaCustomerValidator::isReadyForInvoicing($customer);

        return view('admin.customers.show', compact('customer', 'zatcaCheck'));
    }

    public function edit(Customer $customer): View
    {
        $this->authorize('update', $customer);

        return view('admin.customers.edit', [
            'customer' => $customer,
            'managers' => $this->managerOptions(),
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(CustomerUpdateRequest $request, Customer $customer): RedirectResponse
    {
        $data = $request->validated();
        $data['is_tax_exempt'] = $request->boolean('is_tax_exempt');
        $data['country_code'] = $data['country_code'] ?? 'SA';

        $customer->update($data);

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('success', 'تم تحديث بيانات العميل');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->authorize('delete', $customer);

        $customer->delete();

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'تم حذف العميل');
    }

    /**
     * Users that can be assigned as account managers.
     */
    protected function managerOptions()
    {
        return User::role('Account Manager')->orderBy('name')->get(['id', 'name']);
    }
}
