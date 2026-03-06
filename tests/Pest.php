<?php

use App\Central\Enums\UserRole;
use App\Central\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

pest()->extend(Tests\TestCase::class)
    ->use(Tests\RefreshEveryDatabase::class)
    ->in('Feature');

pest()->extend(Tests\TestCase::class)
    ->in('Architecture');

pest()->extend(Tests\TestCase::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Global Helpers
|--------------------------------------------------------------------------
*/

/**
 * Create a Tenant record in the central database using its factory.
 *
 * @param  array<string, mixed>  $attributes
 */
function createTenant(array $attributes = []): Tenant
{
    return Tenant::factory()->create($attributes);
}

/**
 * Set TenantContext to the given tenant (or create one) and return a
 * User that belongs to it, already authenticated via actingAs().
 *
 * The middleware is intentionally NOT bypassed here — call
 * `withoutMiddleware([IdentifyTenant::class])` in the test if needed.
 *
 * @param  array<string, mixed>  $userAttributes
 */
function actingAsTenantUser(
    ?Tenant $tenant = null,
    array $userAttributes = [],
    string $guard = 'sanctum',
): User {
    $tenant ??= createTenant();

    initializeTenancy($tenant);

    $user = User::factory()->create(array_merge([
        'tenant_id' => $tenant->id,
        'role' => UserRole::TenantUser,
    ], $userAttributes));

    test()->actingAs($user, $guard);

    return $user;
}

/**
 * Bind the given tenant into TenantContext for the current test.
 * Does NOT switch the database connection (tests run on the central
 * SQLite in-memory DB and use tenant_id scoping).
 */
function initializeTenancy(Tenant $tenant): void
{
    app(TenantContext::class)->set($tenant);
}
