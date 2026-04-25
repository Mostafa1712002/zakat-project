<?php

namespace App\Providers;

use App\Domain\Catalog\Models\Service;
use App\Domain\Catalog\Models\ServiceType;
use App\Domain\Catalog\Models\Unit;
use App\Domain\Catalog\Policies\ServicePolicy;
use App\Domain\Catalog\Policies\ServiceTypePolicy;
use App\Domain\Catalog\Policies\UnitPolicy;
use App\Domain\Customer\Models\Customer;
use App\Domain\Customer\Policies\CustomerPolicy;
use App\Domain\Sales\Events\InvoiceIssued;
use App\Domain\Sales\Events\QuoteApproved;
use App\Domain\Sales\Events\QuoteRejected;
use App\Domain\Sales\Models\Invoice;
use App\Domain\Sales\Models\Quote;
use App\Domain\Sales\Policies\InvoicePolicy;
use App\Domain\Sales\Policies\QuotePolicy;
use App\Listeners\AuditLogListener;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.bootstrap-4');
        Paginator::defaultSimpleView('vendor.pagination.simple-bootstrap-4');

        // Phase 3: Catalog policies
        Gate::policy(ServiceType::class, ServiceTypePolicy::class);
        Gate::policy(Service::class, ServicePolicy::class);
        Gate::policy(Unit::class, UnitPolicy::class);

        // Phase 4: Customer policy
        Gate::policy(Customer::class, CustomerPolicy::class);

        // Phase 5a: Sales / Quote policy
        Gate::policy(Quote::class, QuotePolicy::class);

        // Phase 5b: Sales / Invoice policy
        Gate::policy(Invoice::class, InvoicePolicy::class);

        // Phase 8.1: Audit log event listeners
        Event::listen(InvoiceIssued::class, AuditLogListener::class);
        Event::listen(QuoteApproved::class, AuditLogListener::class);
        Event::listen(QuoteRejected::class, AuditLogListener::class);
    }
}
