<?php

use App\Central\Enums\UserRole;
use App\Central\Models\Tenant;
use App\Models\User;
use App\Tenant\Services\TenantRolePermissionService;
use App\Tenant\Support\TenantPermissionCatalog;
use Inertia\Testing\AssertableInertia as Assert;

it('uses shared catalog for tenant role index permissions', function (): void {
    $tenant = Tenant::factory()->create();
    $owner = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => UserRole::TenantOwner,
    ]);

    $this->actingAs($owner)
        ->get(route('tenant.roles.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tenant/Roles/Index')
            ->where('availablePermissions', TenantPermissionCatalog::webAssignable())
        );
});

it('uses shared catalog for tenant role permission defaults', function (): void {
    $matrix = app(TenantRolePermissionService::class)->getRolePermissionMatrix();

    expect($matrix['roles'][UserRole::TenantOwner->value]['permissions'])
        ->toBe(TenantPermissionCatalog::tenantOwnerDefault())
        ->and($matrix['roles'][UserRole::TenantUser->value]['permissions'])
        ->toBe(TenantPermissionCatalog::tenantUserDefault());
});
