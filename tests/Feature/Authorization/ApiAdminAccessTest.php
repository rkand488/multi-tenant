<?php

use App\Central\Enums\UserRole;
use App\Models\User;

it('returns 401 for unauthenticated request to admin tenants endpoint', function (): void {
    $this->getJson('/api/v1/admin/tenants')
        ->assertUnauthorized();
});

it('returns 403 for tenant user accessing admin tenants endpoint', function (): void {
    $user = User::factory()->create(['role' => UserRole::TenantUser]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/admin/tenants')
        ->assertForbidden();
});

it('allows super admin to access admin tenants endpoint', function (): void {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/admin/tenants')
        ->assertOk();
});
