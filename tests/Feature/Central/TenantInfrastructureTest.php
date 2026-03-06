<?php

use App\Central\Models\Domain;
use App\Central\Models\Tenant;
use App\Http\Middleware\IdentifyTenant;
use App\Tenancy\DatabaseManager;
use App\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

// ---------------------------------------------------------------------------
// Helpers shared across tests in this file.
// ---------------------------------------------------------------------------

/**
 * Invoke IdentifyTenant::handle() in isolation with a no-op DatabaseManager,
 * returning the tenant ID placed into TenantContext by the middleware.
 */
function runIdentifyTenant(string $host): ?string
{
    // We only need the context-setting side-effect; DB switching is a no-op.
    $databaseManager = Mockery::mock(DatabaseManager::class);
    $databaseManager->allows('connectTenant');

    /** @var TenantContext $context */
    $context = app(TenantContext::class);
    $context->forget();

    $middleware = new IdentifyTenant(
        app(\App\Tenancy\TenantResolver::class),
        $context,
        $databaseManager,
    );

    $request = Request::create("https://{$host}/");
    $request->headers->set('HOST', $host);

    $resolvedId = null;
    $middleware->handle($request, function () use ($context, &$resolvedId) {
        $resolvedId = $context->getOrNull()?->id;

        return response('ok');
    });

    $context->forget();

    return $resolvedId;
}

// ---------------------------------------------------------------------------
// Subdomain resolution via the IdentifyTenant middleware
// ---------------------------------------------------------------------------

it('sets the tenant context for a valid tenant subdomain', function (): void {
    $tenant = Tenant::factory()->create(['slug' => 'infra-test-tenant']);

    $baseDomain = config('tenancy.domain');
    $host = "infra-test-tenant.{$baseDomain}";

    $resolvedId = runIdentifyTenant($host);

    expect($resolvedId)->toBe($tenant->id);
});

it('sets the tenant context for a custom domain', function (): void {
    $tenant = Tenant::factory()->create();

    Domain::create([
        'tenant_id' => $tenant->id,
        'domain' => 'custom.mycompany.com',
        'is_primary' => true,
        'is_verified' => true,
    ]);

    $resolvedId = runIdentifyTenant('custom.mycompany.com');

    expect($resolvedId)->toBe($tenant->id);
});

// ---------------------------------------------------------------------------
// Unknown host → NotFoundHttpException (HTTP 404)
// ---------------------------------------------------------------------------

it('throws NotFoundHttpException for an unknown subdomain', function (): void {
    $baseDomain = config('tenancy.domain');
    $host = "no-such-tenant-xyz.{$baseDomain}";

    expect(fn () => runIdentifyTenant($host))
        ->toThrow(NotFoundHttpException::class);
});

it('throws NotFoundHttpException for a completely unrecognised host', function (): void {
    expect(fn () => runIdentifyTenant('totally.unknown.domain'))
        ->toThrow(NotFoundHttpException::class);
});
