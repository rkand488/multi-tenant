<?php

use App\Central\Enums\TenantStatus;
use App\Central\Models\Invoice;
use App\Central\Models\Plan;
use App\Central\Models\Tenant;
use App\Central\Models\Usage;
use App\Models\User;

function makeSuperAdmin(): User
{
    return User::factory()->superAdmin()->create();
}

it('allows super admin to view tenants', function (): void {
    $admin = makeSuperAdmin();
    Tenant::factory()->count(2)->create();

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/admin/tenants')
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');
});

it('allows super admin to suspend a tenant', function (): void {
    $admin = makeSuperAdmin();
    $tenant = Tenant::factory()->create(['status' => TenantStatus::Active]);

    $this->actingAs($admin, 'sanctum')
        ->patchJson("/api/v1/admin/tenants/{$tenant->id}", [
            'status' => TenantStatus::Suspended->value,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.status', TenantStatus::Suspended->value);

    expect($tenant->fresh()?->status)->toBe(TenantStatus::Suspended);
});

it('allows super admin to manage plans', function (): void {
    $admin = makeSuperAdmin();

    $createResponse = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/admin/plans', [
            'name' => 'Business',
            'slug' => 'business',
            'description' => 'Business plan',
            'price_monthly' => 7900,
            'price_yearly' => 79000,
            'trial_days' => 14,
            'features' => ['max_users' => 25, 'api_access' => true],
            'is_active' => true,
            'sort_order' => 10,
        ])
        ->assertCreated();

    $planId = $createResponse->json('data.id');

    $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/admin/plans/{$planId}", [
            'name' => 'Business Plus',
            'price_monthly' => 8900,
            'price_yearly' => 89000,
            'trial_days' => 21,
            'features' => ['max_users' => 40, 'api_access' => true],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'Business Plus');

    $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/v1/admin/plans/{$planId}")
        ->assertSuccessful();

    expect(Plan::query()->whereKey($planId)->exists())->toBeFalse();
});

it('allows super admin to view invoices', function (): void {
    $admin = makeSuperAdmin();
    $tenant = Tenant::factory()->create();
    $invoice = Invoice::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/admin/invoices')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');

    $this->actingAs($admin, 'sanctum')
        ->getJson("/api/v1/admin/invoices/{$invoice->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.id', $invoice->id);
});

it('allows super admin to view system analytics', function (): void {
    $admin = makeSuperAdmin();
    Tenant::factory()->count(2)->create();

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/admin/system-analytics')
        ->assertSuccessful()
        ->assertJsonPath('data.tenants.total', 2);
});

it('allows super admin to view usage statistics', function (): void {
    $admin = makeSuperAdmin();
    $tenant = Tenant::factory()->create();

    Usage::factory()->create([
        'tenant_id' => $tenant->id,
        'feature_key' => 'api_calls',
        'quantity' => 120,
        'period_start' => now()->startOfMonth(),
        'period_end' => now()->endOfMonth(),
    ]);

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/admin/usage-statistics')
        ->assertSuccessful()
        ->assertJsonPath('summary.data.0.feature_key', 'api_calls');
});

it('forbids non super admins from admin api', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/admin/tenants')
        ->assertForbidden();
});
