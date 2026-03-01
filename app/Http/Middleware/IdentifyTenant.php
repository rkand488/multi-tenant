<?php

namespace App\Http\Middleware;

use App\Tenancy\DatabaseManager;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Identifies the current tenant from the request host, switches the database
 * connection to the tenant's isolated database, and stores the resolved Tenant
 * in the TenantContext singleton so it is accessible anywhere during the
 * request lifecycle via the tenant() helper.
 *
 * On terminate() — after the response has been sent — the context is cleared
 * and the default connection is restored to the central database so that
 * subsequent code (e.g. terminable middleware, long-running FPM processes)
 * is never left pointing at a tenant database.
 *
 * Registration (bootstrap/app.php):
 *   $middleware->alias(['tenant' => IdentifyTenant::class]);
 *
 * Usage in routes:
 *   Route::middleware(['tenant', 'tenant.active'])->group(...);
 */
class IdentifyTenant
{
    public function __construct(
        private readonly TenantResolver $resolver,
        private readonly TenantContext $context,
        private readonly DatabaseManager $databaseManager,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->resolver->fromRequest($request);

        $this->context->set($tenant);
        $this->databaseManager->connectTenant($tenant);

        return $next($request);
    }

    /**
     * Called after the response has been sent to the client.
     * Tears down the tenant context to prevent state leaking into the next
     * request when using long-lived PHP processes (e.g. Octane, Swoole).
     */
    public function terminate(Request $request, Response $response): void
    {
        $this->context->forget();
        $this->databaseManager->connectCentral();
    }
}
