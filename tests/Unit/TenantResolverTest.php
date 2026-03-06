<?php

use App\Central\Models\Domain;
use App\Central\Models\Tenant;
use App\Tenancy\TenantResolver;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\RefreshEveryDatabase;

uses(RefreshEveryDatabase::class);

// ---------------------------------------------------------------------------
// Exact domain match (custom branded domains via domains table)
// ---------------------------------------------------------------------------

it('resolves tenant by exact domain match', function (): void {
    $tenant = Tenant::factory()->create();

    Domain::create([
        'tenant_id' => $tenant->id,
        'domain' => 'crm.acmecorp.com',
        'is_primary' => true,
        'is_verified' => true,
    ]);

    $request = Request::create('https://crm.acmecorp.com/dashboard');
    $request->headers->set('HOST', 'crm.acmecorp.com');

    $resolver = app(TenantResolver::class);
    $resolved = $resolver->fromRequest($request);

    expect($resolved->id)->toBe($tenant->id);
});

// ---------------------------------------------------------------------------
// Subdomain slug extraction
// ---------------------------------------------------------------------------

it('resolves tenant from slug subdomain', function (): void {
    $tenant = Tenant::factory()->create(['slug' => 'acme-saas']);

    $baseDomain = config('tenancy.domain');
    $host = "acme-saas.{$baseDomain}";

    $request = Request::create("https://{$host}/dashboard");
    $request->headers->set('HOST', $host);

    $resolver = app(TenantResolver::class);
    $resolved = $resolver->fromRequest($request);

    expect($resolved->id)->toBe($tenant->id);
});

// ---------------------------------------------------------------------------
// Domain takes priority over slug when both could match
// ---------------------------------------------------------------------------

it('prefers exact domain match over slug extraction', function (): void {
    $tenantA = Tenant::factory()->create(['slug' => 'acme']);
    $tenantB = Tenant::factory()->create(['slug' => 'other-tenant']);

    // acme.{domain} would match tenantA by slug — but the domain record
    // explicitly maps it to tenantB (custom domain override).
    $baseDomain = config('tenancy.domain');
    Domain::create([
        'tenant_id' => $tenantB->id,
        'domain' => "acme.{$baseDomain}",
        'is_primary' => true,
        'is_verified' => true,
    ]);

    $host = "acme.{$baseDomain}";
    $request = Request::create("https://{$host}/");
    $request->headers->set('HOST', $host);

    $resolver = app(TenantResolver::class);
    $resolved = $resolver->fromRequest($request);

    expect($resolved->id)->toBe($tenantB->id);
});

// ---------------------------------------------------------------------------
// Unknown host → NotFoundHttpException
// ---------------------------------------------------------------------------

it('throws NotFoundHttpException for an unknown host', function (): void {
    $request = Request::create('https://unknown-xyz-tenant.example.com/');
    $request->headers->set('HOST', 'unknown-xyz-tenant.example.com');

    $resolver = app(TenantResolver::class);

    expect(fn () => $resolver->fromRequest($request))
        ->toThrow(NotFoundHttpException::class);
});

it('throws NotFoundHttpException for an unknown subdomain on the app domain', function (): void {
    $baseDomain = config('tenancy.domain');
    $host = "nonexistent-tenant-xyz.{$baseDomain}";

    $request = Request::create("https://{$host}/");
    $request->headers->set('HOST', $host);

    $resolver = app(TenantResolver::class);

    expect(fn () => $resolver->fromRequest($request))
        ->toThrow(NotFoundHttpException::class);
});
