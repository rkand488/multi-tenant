<?php

use App\Central\Enums\UserRole;
use App\Central\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;

beforeEach(function (): void {
    $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    app(TenantContext::class)->forget();
});

it('allows only super admin login on base domain and redirects to admin dashboard', function (): void {
    $superAdmin = User::factory()->superAdmin()->create([
        'email' => 'super-admin@example.com',
    ]);

    $this->call('POST', '/login', [
        'email' => $superAdmin->email,
        'password' => 'password',
    ], [], [], ['HTTP_HOST' => config('tenancy.central_domain')])
        ->assertRedirect('/admin');

    $this->assertAuthenticatedAs($superAdmin);
});

it('blocks tenant user login on base domain', function (): void {
    $tenant = Tenant::factory()->create();
    $tenantUser = User::factory()->create([
        'role' => UserRole::TenantUser,
        'tenant_id' => $tenant->id,
        'email' => 'tenant-user@example.com',
    ]);

    $this->call('POST', '/login', [
        'email' => $tenantUser->email,
        'password' => 'password',
    ], [], [], ['HTTP_HOST' => config('tenancy.central_domain')])
        ->assertSessionHasErrors(['email']);

    $this->assertGuest();
});

it('blocks super admin login on tenant subdomain', function (): void {
    $tenant = Tenant::factory()->create();
    $superAdmin = User::factory()->superAdmin()->create([
        'email' => 'central-admin@example.com',
    ]);

    app(TenantContext::class)->set($tenant);

    $this->post('/login', [
        'email' => $superAdmin->email,
        'password' => 'password',
    ])
        ->assertSessionHasErrors(['email']);

    $this->assertGuest();
});

it('blocks tenant user login on another tenant subdomain', function (): void {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $tenantUser = User::factory()->create([
        'role' => UserRole::TenantUser,
        'tenant_id' => $tenantA->id,
        'email' => 'cross-login-user@example.com',
    ]);

    app(TenantContext::class)->set($tenantB);

    $this->post('/login', [
        'email' => $tenantUser->email,
        'password' => 'password',
    ])
        ->assertSessionHasErrors(['email']);

    $this->assertGuest();
});

it('allows tenant user login only on own subdomain', function (): void {
    $tenant = Tenant::factory()->create();
    $tenantUser = User::factory()->create([
        'role' => UserRole::TenantUser,
        'tenant_id' => $tenant->id,
        'email' => 'workspace-user@example.com',
    ]);

    app(TenantContext::class)->set($tenant);

    $this->post('/login', [
        'email' => $tenantUser->email,
        'password' => 'password',
    ])
        ->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($tenantUser);
});
