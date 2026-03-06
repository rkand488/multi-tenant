<?php

use App\Central\Enums\UserRole;
use App\Models\User;
use App\Tenancy\TenantContext;

beforeEach(function (): void {
    $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    app(TenantContext::class)->forget();
});

it('allows a super admin to log in and redirects to the admin dashboard', function (): void {
    $admin = User::factory()->superAdmin()->create([
        'email' => 'admin@tenantrix.test',
    ]);

    $this->call('POST', '/login', [
        'email' => $admin->email,
        'password' => 'password',
    ], [], [], ['HTTP_HOST' => config('tenancy.central_domain')])
        ->assertRedirect('/admin');

    $this->assertAuthenticatedAs($admin);
});

it('rejects login with wrong password', function (): void {
    User::factory()->superAdmin()->create([
        'email' => 'admin@tenantrix.test',
    ]);

    $this->call('POST', '/login', [
        'email' => 'admin@tenantrix.test',
        'password' => 'wrong-password',
    ], [], [], ['HTTP_HOST' => config('tenancy.central_domain')])
        ->assertSessionHasErrors(['email']);

    $this->assertGuest();
});

it('rejects login with a non-existent email', function (): void {
    $this->call('POST', '/login', [
        'email' => 'nobody@tenantrix.test',
        'password' => 'password',
    ], [], [], ['HTTP_HOST' => config('tenancy.central_domain')])
        ->assertSessionHasErrors(['email']);

    $this->assertGuest();
});

it('blocks a tenant user from logging in on the central domain', function (): void {
    $tenant = \App\Central\Models\Tenant::factory()->create();

    $tenantUser = User::factory()->create([
        'role' => UserRole::TenantOwner,
        'tenant_id' => $tenant->id,
        'email' => 'owner@acme.test',
    ]);

    $this->call('POST', '/login', [
        'email' => $tenantUser->email,
        'password' => 'password',
    ], [], [], ['HTTP_HOST' => config('tenancy.central_domain')])
        ->assertSessionHasErrors(['email']);

    $this->assertGuest();
});

it('shows the login page', function (): void {
    $this->get('/login')->assertOk();
});

it('logs out an authenticated super admin', function (): void {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin);

    $this->post('/logout')->assertRedirect('/login');

    $this->assertGuest();
});
