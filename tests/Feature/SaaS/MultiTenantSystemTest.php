<?php

use App\Billing\Services\UsageService;
use App\Central\Enums\BillingInterval;
use App\Central\Enums\UserRole;
use App\Central\Models\Plan;
use App\Central\Models\Subscription;
use App\Central\Models\Tenant;
use App\Http\Middleware\EnforceUsageLimit;
use App\Http\Middleware\EnsureTenantIsActive;
use App\Http\Middleware\IdentifyTenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\Route;

function setTenant(Tenant $tenant): void
{
    app(TenantContext::class)->set($tenant);
}

function createTenantOwner(Tenant $tenant): User
{
    return User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => UserRole::TenantOwner,
    ]);
}

it('enforces tenant isolation', function (): void {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $ownerA = createTenantOwner($tenantA);
    $userB = User::factory()->create([
        'tenant_id' => $tenantB->id,
        'role' => UserRole::TenantUser,
    ]);

    setTenant($tenantA);

    $this->actingAs($ownerA, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->getJson("/api/v1/tenant/users/{$userB->id}")
        ->assertNotFound();
});

it('handles authentication flow', function (): void {
    $this->getJson('/api/v1/auth/me')->assertUnauthorized();

    $user = User::factory()->create([
        'email' => 'auth-flow@example.com',
        'password' => bcrypt('Password1!'),
    ]);

    $login = $this->postJson('/api/v1/auth/login', [
        'email' => 'auth-flow@example.com',
        'password' => 'Password1!',
    ])->assertOk();

    $this->withToken($login->json('token'))
        ->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('user.id', $user->id);
});

it('completes tenant onboarding', function (): void {
    $response = $this->postJson('/api/v1/auth/register', [
        'workspace_name' => 'Acme SaaS',
        'slug' => 'acme-saas-onboarding',
        'owner_name' => 'Owner One',
        'email' => 'owner-onboarding@example.com',
        'password' => 'Password1!',
        'password_confirmation' => 'Password1!',
    ])->assertCreated();

    $tenantId = $response->json('tenant.id');
    $userId = $response->json('user.id');

    expect(Tenant::query()->whereKey($tenantId)->exists())->toBeTrue()
        ->and(User::query()->whereKey($userId)->where('role', UserRole::TenantOwner)->exists())->toBeTrue();
});

it('enforces subscription usage limits', function (): void {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => UserRole::TenantOwner,
    ]);

    $plan = Plan::factory()->create([
        'is_active' => true,
        'features' => ['max_users' => 1],
    ]);

    Subscription::factory()->forTenant($tenant)->create([
        'plan_id' => $plan->id,
        'billing_interval' => BillingInterval::Monthly,
    ]);

    setTenant($tenant);

    app(UsageService::class)->increment($tenant, 'max_users', 1);

    Route::get('/__test/usage-limit-check', fn () => response()->json(['ok' => true]))
        ->middleware(EnforceUsageLimit::class.':max_users');

    $this->actingAs($user, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->getJson('/__test/usage-limit-check')
        ->assertTooManyRequests();
});

it('enforces api authorization rules', function (): void {
    $tenant = Tenant::factory()->create();
    $owner = createTenantOwner($tenant);
    $member = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => UserRole::TenantUser,
    ]);

    setTenant($tenant);

    $this->actingAs($member, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->putJson('/api/v1/tenant/team-settings/primary', [
            'settings' => ['timezone' => 'UTC'],
        ])
        ->assertForbidden();

    $this->actingAs($owner, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->putJson('/api/v1/tenant/team-settings/primary', [
            'settings' => ['timezone' => 'UTC'],
        ])
        ->assertOk();
});
