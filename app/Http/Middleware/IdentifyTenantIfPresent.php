<?php

namespace App\Http\Middleware;

use App\Tenancy\DatabaseManager;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Optionally identifies the current tenant from the request host.
 *
 * Unlike IdentifyTenant, this middleware does NOT throw when no tenant can
 * be resolved for the current host (e.g. the central domain).  It is safe
 * to apply to routes that are reachable from both the central domain and
 * tenant subdomains / custom domains.
 *
 * When a tenant IS resolved the database connection and TenantContext are
 * configured exactly as IdentifyTenant does.
 *
 * Registration (bootstrap/app.php):
 *   $middleware->alias(['tenant.optional' => IdentifyTenantIfPresent::class]);
 *
 * Typical usage: login & registration routes where the host determines
 * which tenant the credentials must belong to.
 */
class IdentifyTenantIfPresent
{
    public function __construct(
        private readonly TenantResolver $resolver,
        private readonly TenantContext $context,
        private readonly DatabaseManager $databaseManager,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $tenant = $this->resolver->fromRequest($request);
            $this->context->set($tenant);
            $this->databaseManager->connectTenant($tenant);
        } catch (NotFoundHttpException) {
            // No tenant for this host (e.g. central domain) – continue without tenant context.
        }

        return $next($request);
    }

    /**
     * Called after the response has been sent to the client.
     * Tears down any tenant context that was set during this request.
     */
    public function terminate(Request $request, Response $response): void
    {
        if ($this->context->check()) {
            $this->context->forget();
            $this->databaseManager->connectCentral();
        }
    }
}
