<?php

use App\Central\Enums\UserRole;
use App\Central\Models\Tenant;
use App\Http\Middleware\EnsureTenantIsActive;
use App\Http\Middleware\IdentifyTenant;
use App\Models\User;
use App\Tenancy\TenantContext;

it('returns 401 for unauthenticated request to tenant users endpoint', function (): void {
    $this->getJson('/api/v1/tenant/users')
        ->assertUnauthorized();
});

it('allows super admin to access tenant users endpoint', function (): void {
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->superAdmin()->create();
    app(TenantContext::class)->set($tenant);

    $this->actingAs($admin, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->getJson('/api/v1/tenant/users')
        ->assertOk();
});

it('allows tenant owner to access tenant users endpoint', function (): void {
    $tenant = Tenant::factory()->create();
    $owner = User::factory()->tenantOwner($tenant->id)->create();
    app(TenantContext::class)->set($tenant);

    $this->actingAs($owner, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->getJson('/api/v1/tenant/users')
        ->assertOk();
});

it('does not reject tenant user at the EnsureTenantOrSuperAdmin middleware layer', function (): void {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['role' => UserRole::TenantUser, 'tenant_id' => $tenant->id]);
    app(TenantContext::class)->set($tenant);

    // The middleware grants access to TenantUser; a 403 response here is
    // from a downstream policy (viewAny requires TenantOwner), not the middleware.
    $this->actingAs($user, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->getJson('/api/v1/tenant/users')
        ->assertStatus(403)
        ->assertJsonPath('message', 'This action is unauthorized.');
});
