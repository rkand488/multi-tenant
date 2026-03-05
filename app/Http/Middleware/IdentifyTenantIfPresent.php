<?php

namespace App\Http\Middleware;

use App\Central\Models\Tenant;
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
    private const SUPER_ADMIN_TENANT_SESSION_KEY = 'selected_tenant_id';

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
            $fallbackTenant = $this->resolveFallbackTenant($request);

            if ($fallbackTenant !== null) {
                $this->context->set($fallbackTenant);
                $this->databaseManager->connectTenant($fallbackTenant);
            }
        }

        return $next($request);
    }

    private function resolveFallbackTenant(Request $request): ?Tenant
    {
        $user = $request->user();

        if ($user === null) {
            return null;
        }

        if ($user->isSuperAdmin()) {
            $requestedTenantId = trim((string) ($request->input('tenant_id') ?? $request->query('tenant_id') ?? ''));
            $refererTenantId = $this->extractTenantIdFromReferer($request);
            $sessionTenantId = (string) $request->session()->get(self::SUPER_ADMIN_TENANT_SESSION_KEY, '');
            $selectedTenantId = $requestedTenantId !== ''
                ? $requestedTenantId
                : ($refererTenantId !== '' ? $refererTenantId : $sessionTenantId);

            if ($selectedTenantId === '') {
                $request->session()->forget(self::SUPER_ADMIN_TENANT_SESSION_KEY);

                return null;
            }

            $tenant = Tenant::on('central')->find($selectedTenantId);

            if ($tenant === null) {
                $request->session()->forget(self::SUPER_ADMIN_TENANT_SESSION_KEY);

                return null;
            }

            $request->session()->put(self::SUPER_ADMIN_TENANT_SESSION_KEY, $tenant->id);

            return $tenant;
        }

        if ($user->tenant_id === null) {
            return null;
        }

        return Tenant::on('central')->find($user->tenant_id);
    }

    private function extractTenantIdFromReferer(Request $request): string
    {
        $referer = (string) $request->headers->get('referer', '');

        if ($referer === '') {
            return '';
        }

        $query = parse_url($referer, PHP_URL_QUERY);

        if (! is_string($query) || $query === '') {
            return '';
        }

        parse_str($query, $params);

        return trim((string) ($params['tenant_id'] ?? ''));
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
