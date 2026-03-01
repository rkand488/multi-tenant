<?php

namespace App\Providers;

use App\Tenancy\Contracts\FindsTenant;
use App\Tenancy\DatabaseManager;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantResolver;
use Illuminate\Support\ServiceProvider;

/**
 * Registers all tenancy engine bindings into the service container.
 *
 * Registered in bootstrap/providers.php.
 */
class TenancyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);

        $this->app->singleton(DatabaseManager::class);

        $this->app->bind(FindsTenant::class, TenantResolver::class);
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(
            base_path('config/tenancy.php'),
            'tenancy',
        );
    }
}
