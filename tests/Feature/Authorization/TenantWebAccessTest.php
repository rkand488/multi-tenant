<?php

use App\Central\Enums\UserRole;
use App\Central\Models\Tenant;
use App\Http\Middleware\IdentifyTenant;
use App\Models\User;

it('redirects guest to login when accessing dashboard', function (): void {
    $this->get('/dashboard')
        ->assertRedirectToRoute('login');
});

it('allows tenant owner to access dashboard users', function (): void {
    $tenant = Tenant::factory()->create();
    $owner = User::factory()->tenantOwner($tenant->id)->create();

    $this->actingAs($owner)
        ->withoutMiddleware([IdentifyTenant::class])
        ->get('/dashboard/users')
        ->assertOk();
});

it('allows tenant user to access dashboard users', function (): void {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['role' => UserRole::TenantUser, 'tenant_id' => $tenant->id]);

    $this->actingAs($user)
        ->withoutMiddleware([IdentifyTenant::class])
        ->get('/dashboard/users')
        ->assertOk();
});

it('allows super admin to access dashboard users', function (): void {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->withoutMiddleware([IdentifyTenant::class])
        ->get('/dashboard/users')
        ->assertOk();
});
