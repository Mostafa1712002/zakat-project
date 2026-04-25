<?php

namespace App\Providers;

use App\Domain\Catalog\Models\Service;
use App\Domain\Catalog\Models\ServiceType;
use App\Domain\Catalog\Models\Unit;
use App\Domain\Catalog\Policies\ServicePolicy;
use App\Domain\Catalog\Policies\ServiceTypePolicy;
use App\Domain\Catalog\Policies\UnitPolicy;
use Illuminate\Pagination\Paginator;
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
    }
}
