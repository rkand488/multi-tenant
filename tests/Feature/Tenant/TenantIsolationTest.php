<?php

use App\Central\Enums\UserRole;
use App\Central\Models\Tenant;
use App\Http\Middleware\EnsureTenantIsActive;
use App\Http\Middleware\IdentifyTenant;
use App\Models\User;
use App\Tenancy\TenantContext;

/**
 * These tests verify that a tenant user cannot access or modify another
 * tenant's resources when the tenant context is set to their own tenant.
 */

// ---------------------------------------------------------------------------
// API — cross-tenant user read prevention
// ---------------------------------------------------------------------------

it('cannot read a user from a different tenant via API', function (): void {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $ownerA = User::factory()->create(['tenant_id' => $tenantA->id, 'role' => UserRole::TenantOwner]);
    $userB = User::factory()->create(['tenant_id' => $tenantB->id, 'role' => UserRole::TenantUser]);

    // Set context to Tenant A
    app(TenantContext::class)->set($tenantA);

    $this->actingAs($ownerA, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->getJson("/api/v1/tenant/users/{$userB->id}")
        ->assertStatus(404);
});

it('cannot delete a user from a different tenant via API', function (): void {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $ownerA = User::factory()->create(['tenant_id' => $tenantA->id, 'role' => UserRole::TenantOwner]);
    $userB = User::factory()->create(['tenant_id' => $tenantB->id, 'role' => UserRole::TenantUser]);

    app(TenantContext::class)->set($tenantA);

    $this->actingAs($ownerA, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->deleteJson("/api/v1/tenant/users/{$userB->id}")
        ->assertStatus(404);

    // User B must still exist (bypass scope since we're checking a different tenant's record)
    expect(User::on('central')->withoutGlobalScopes()->find($userB->id))->not->toBeNull();
});

it('listing users only returns users from the authenticated tenant', function (): void {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $ownerA = User::factory()->create(['tenant_id' => $tenantA->id, 'role' => UserRole::TenantOwner]);
    User::factory()->create(['tenant_id' => $tenantA->id, 'role' => UserRole::TenantUser]);
    $userB = User::factory()->create(['tenant_id' => $tenantB->id, 'role' => UserRole::TenantUser]);

    app(TenantContext::class)->set($tenantA);

    $response = $this->actingAs($ownerA, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->getJson('/api/v1/tenant/users')
        ->assertOk();

    $emails = collect($response->json('data'))->pluck('email');
    expect($emails)->not->toContain($userB->email);
});

// ---------------------------------------------------------------------------
// Web — cross-tenant web route isolation
// ---------------------------------------------------------------------------

it('web user list only shows users from the same tenant', function (): void {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $ownerA = User::factory()->tenantOwner($tenantA->id)->create();
    $memberA = User::factory()->create(['tenant_id' => $tenantA->id, 'role' => UserRole::TenantUser]);
    $memberB = User::factory()->create(['tenant_id' => $tenantB->id, 'role' => UserRole::TenantUser]);

    $this->actingAs($ownerA)
        ->get(route('tenant.users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Tenant/Users/Index')
            ->where('users.data', fn ($data) => collect($data)->pluck('email')->contains($memberA->email) &&
                ! collect($data)->pluck('email')->contains($memberB->email)
            )
        );
});

// ---------------------------------------------------------------------------
// Suspended tenant cannot access routes
// ---------------------------------------------------------------------------

it('suspended tenant is blocked from API routes', function (): void {
    $tenant = Tenant::factory()->suspended()->create();
    $owner = User::factory()->create(['tenant_id' => $tenant->id, 'role' => UserRole::TenantOwner]);

    app(TenantContext::class)->set($tenant);

    $this->actingAs($owner, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class])
        ->getJson('/api/v1/tenant/users')
        ->assertForbidden();
});
