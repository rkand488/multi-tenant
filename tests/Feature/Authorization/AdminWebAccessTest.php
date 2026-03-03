<?php

use App\Central\Enums\UserRole;
use App\Models\User;

it('redirects guest to login when accessing admin', function (): void {
    $this->get('/admin')
        ->assertRedirectToRoute('login');
});

it('returns 403 for tenant user accessing admin', function (): void {
    $user = User::factory()->create(['role' => UserRole::TenantUser]);

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

it('allows super admin to access admin', function (): void {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk();
});
