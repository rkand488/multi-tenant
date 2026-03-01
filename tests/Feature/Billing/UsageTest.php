<?php

use App\Billing\Services\UsageService;
use App\Central\Enums\SubscriptionStatus;
use App\Central\Models\Plan;
use App\Central\Models\Subscription;
use App\Central\Models\Tenant;
use App\Http\Middleware\EnsureTenantIsActive;
use App\Http\Middleware\IdentifyTenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\Route;

/**
 * @return array{0: User, 1: Tenant, 2: Subscription}
 */
function makeTenantWithSubscription(array $features = []): array
{
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $plan = Plan::factory()->create([
        'is_active' => true,
        'trial_days' => 0,
        'features' => array_merge(['max_users' => 5, 'api_access' => true], $features),
    ]);
    $subscription = Subscription::factory()
        ->forTenant($tenant)
        ->create(['plan_id' => $plan->id, 'status' => SubscriptionStatus::Active]);

    app(TenantContext::class)->set($tenant);

    return [$user, $tenant, $subscription];
}

// ---------------------------------------------------------------------------
// Usage summary
// ---------------------------------------------------------------------------

it('returns usage summary for the current billing period', function (): void {
    [$user] = makeTenantWithSubscription();

    $this->actingAs($user, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->getJson('/api/v1/billing/usage')
        ->assertOk()
        ->assertJsonStructure(['data']);
});

// ---------------------------------------------------------------------------
// Single feature usage
// ---------------------------------------------------------------------------

it('returns usage detail for a specific feature', function (): void {
    [$user] = makeTenantWithSubscription();

    $this->actingAs($user, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->getJson('/api/v1/billing/usage/max_users')
        ->assertOk()
        ->assertJsonPath('data.feature', 'max_users')
        ->assertJsonPath('data.used', 0)
        ->assertJsonPath('data.limit', 5);
});

// ---------------------------------------------------------------------------
// UsageService
// ---------------------------------------------------------------------------

it('increments feature usage correctly', function (): void {
    [, $tenant, $subscription] = makeTenantWithSubscription();

    $service = app(UsageService::class);
    $service->increment($tenant, 'max_users', 2);
    $service->increment($tenant, 'max_users', 1);

    expect($service->currentUsage($tenant, 'max_users', $subscription))->toBe(3);
});

it('confirms the tenant can still use a feature within limit', function (): void {
    [, $tenant, $subscription] = makeTenantWithSubscription(['max_users' => 5]);
    $service = app(UsageService::class);

    $service->increment($tenant, 'max_users', 4);

    expect($service->canUse($tenant, 'max_users', 1))->toBeTrue()
        ->and($service->canUse($tenant, 'max_users', 2))->toBeFalse();
});

it('blocks incrementing usage beyond the plan limit', function (): void {
    [, $tenant] = makeTenantWithSubscription(['max_users' => 3]);
    $service = app(UsageService::class);

    $service->increment($tenant, 'max_users', 3);

    expect(fn () => $service->increment($tenant, 'max_users', 1))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('returns usage as unlimited for a boolean true feature flag', function (): void {
    [, $tenant] = makeTenantWithSubscription(['api_access' => true]);
    $service = app(UsageService::class);

    // Boolean true features are gated, not metered — canUse should still return true.
    expect($service->canUse($tenant, 'api_access'))->toBeTrue();
});

it('blocks a disabled boolean feature', function (): void {
    [, $tenant] = makeTenantWithSubscription(['api_access' => false]);
    $service = app(UsageService::class);

    expect($service->canUse($tenant, 'api_access'))->toBeFalse();
});

// ---------------------------------------------------------------------------
// EnforceUsageLimit middleware
// ---------------------------------------------------------------------------

it('returns 429 when usage limit is exceeded via middleware', function (): void {
    [$user, $tenant] = makeTenantWithSubscription(['max_users' => 1]);
    $service = app(UsageService::class);

    // Saturate the limit.
    $service->increment($tenant, 'max_users', 1);

    // Register a temporary route protected by the middleware.
    Route::get('/__test/usage_limit', fn () => response()->json(['ok' => true]))
        ->middleware(\App\Http\Middleware\EnforceUsageLimit::class.':max_users');

    $this->actingAs($user, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->getJson('/__test/usage_limit')
        ->assertTooManyRequests();
});
