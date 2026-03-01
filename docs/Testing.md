# Testing Strategy

> **Framework:** Pest 4 (wrapping PHPUnit 12)  
> **Coverage target:** ≥ 80% line coverage  
> **Environment:** SQLite in-memory for unit tests; MySQL for feature tests (via Docker service)

---

## 1. Test Structure

```
tests/
├── Pest.php                     # Global uses, custom expectations, test helpers
├── TestCase.php                 # Base test case
├── Unit/
│   ├── TenantResolverTest.php
│   ├── PlanFeaturesTest.php
│   ├── PermissionCheckTest.php
│   └── StorageQuotaTest.php
├── Feature/
│   ├── Central/
│   │   ├── Auth/
│   │   │   ├── SuperAdminLoginTest.php
│   │   │   └── TenantRegistrationTest.php
│   │   ├── Billing/
│   │   │   ├── SubscribeTest.php
│   │   │   ├── ChangePlanTest.php
│   │   │   └── WebhookTest.php
│   │   ├── Admin/
│   │   │   ├── TenantManagementTest.php
│   │   │   └── PlanManagementTest.php
│   │   └── Onboarding/
│   │       └── TenantProvisioningTest.php
│   └── Tenant/
│       ├── Auth/
│       │   ├── LoginTest.php
│       │   ├── PasswordResetTest.php
│       │   └── EmailVerificationTest.php
│       ├── Users/
│       │   ├── InviteUserTest.php
│       │   ├── UpdateUserTest.php
│       │   └── RemoveUserTest.php
│       ├── Roles/
│       │   ├── CreateRoleTest.php
│       │   └── AssignPermissionsTest.php
│       ├── Files/
│       │   ├── UploadFileTest.php
│       │   └── StorageQuotaEnforcementTest.php
│       ├── API/
│       │   ├── ApiTokenTest.php
│       │   └── RateLimitingTest.php
│       ├── ActivityLogTest.php
│       └── AuditLogTest.php
├── Architecture/
│   └── ArchTest.php             # Pest architecture tests
└── Browser/                     # Laravel Dusk (optional E2E)
    └── TenantOnboardingTest.php
```

---

## 2. Test Configuration

### `tests/Pest.php`

```php
<?php

use App\Central\Models\Tenant;
use App\Central\Models\SuperAdmin;
use App\Tenant\Models\User;
use Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');

// ──────────────────────────────────────────────────────
// Custom helpers
// ──────────────────────────────────────────────────────

/**
 * Create and initialise a tenant with a provisioned database.
 */
function createTenant(array $attributes = []): Tenant
{
    $tenant = Tenant::factory()->create($attributes);

    // Run tenant migrations on a fresh in-memory SQLite connection
    \Artisan::call('migrate', [
        '--database' => 'tenant',
        '--path'     => 'database/migrations/tenant',
        '--realpath' => true,
    ]);

    return $tenant;
}

/**
 * Set up tenant context for a request-less test.
 */
function actingAsTenantUser(?User $user = null, ?Tenant $tenant = null): User
{
    $tenant ??= createTenant();
    initializeTenancy($tenant);

    $user ??= User::factory()->create();

    test()->actingAs($user, 'tenant');

    return $user;
}

function initializeTenancy(Tenant $tenant): void
{
    app(\App\Support\Tenancy\TenantContext::class)->set($tenant);
    app(\App\Support\Tenancy\DatabaseManager::class)->connectTenant($tenant);
}
```

### `.env.testing`

```dotenv
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
CENTRAL_DB_CONNECTION=sqlite
CENTRAL_DB_DATABASE=:memory:
QUEUE_CONNECTION=sync
CACHE_STORE=array
SESSION_DRIVER=array
MAIL_MAILER=array
FILESYSTEM_DISK=fake
```

---

## 3. Unit Tests

Unit tests are **pure PHP** — no database, no HTTP stack.

### Example: Permission check

```php
// tests/Unit/PermissionCheckTest.php

use App\Tenant\Models\User;
use App\Tenant\Models\Role;
use App\Tenant\Models\Permission;

it('returns true when user has the required permission', function () {
    $permission = Permission::factory()->make(['name' => 'users.create']);
    $role = Role::factory()
        ->has(Permission::factory()->state(['name' => 'users.create']), 'permissions')
        ->make();

    $user = User::factory()
        ->has(Role::factory()->state(['name' => 'admin']), 'roles')
        ->make();

    expect($user->hasPermission('users.create'))->toBeTrue();
});

it('returns false when user has no matching permission', function () {
    $user = User::factory()->has(Role::factory()->state(['name' => 'viewer']), 'roles')->make();

    expect($user->hasPermission('billing.manage'))->toBeFalse();
});
```

---

## 4. Feature Tests

Feature tests hit the full HTTP stack including middleware. Tenant feature tests use the `createTenant()` helper and simulate subdomain routing.

### Example: Tenant user invitation

```php
// tests/Feature/Tenant/Users/InviteUserTest.php

use App\Tenant\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Tenant\Notifications\UserInvitedNotification;

beforeEach(function () {
    $this->tenant = createTenant(['slug' => 'acme']);
    $this->admin  = actingAsTenantUser(attributes: ['is_owner' => true], tenant: $this->tenant);
});

it('allows an admin to invite a new user', function () {
    Notification::fake();

    $response = $this
        ->withServerVariables(['HTTP_HOST' => 'acme.app.com'])
        ->postJson('/api/v1/users/invite', [
            'email' => 'newmember@acme.com',
            'name'  => 'New Member',
            'roles' => [1],
        ]);

    $response->assertStatus(201)
             ->assertJsonPath('user.status', 'invited');

    $this->assertDatabaseHas('users', ['email' => 'newmember@acme.com']);

    Notification::assertSentTo(
        User::where('email', 'newmember@acme.com')->first(),
        UserInvitedNotification::class
    );
});

it('returns 403 when a viewer tries to invite a user', function () {
    $viewer = User::factory()->create();
    $viewer->roles()->attach(Role::where('name', 'viewer')->first());

    $this->actingAs($viewer, 'tenant')
         ->postJson('/api/v1/users/invite', ['email' => 'x@x.com', 'name' => 'X', 'roles' => []])
         ->assertForbidden();
});

it('rejects invitation when user limit is reached', function () {
    User::factory()->count(5)->create(); // Starter plan: max 5 users

    $this->postJson('/api/v1/users/invite', ['email' => 'extra@acme.com', 'name' => 'Extra', 'roles' => []])
         ->assertStatus(422)
         ->assertJsonPath('message', 'User limit reached for your plan.');
});
```

### Example: Stripe webhook

```php
// tests/Feature/Central/Billing/WebhookTest.php

use Illuminate\Support\Facades\Event;
use App\Central\Events\SubscriptionRenewed;

it('marks invoice as paid on invoice.paid webhook', function () {
    $tenant = Tenant::factory()->hasSubscription()->create();

    $payload = [
        'type' => 'invoice.paid',
        'data' => [
            'object' => [
                'id'                     => 'in_test123',
                'customer'               => $tenant->stripe_customer_id,
                'status'                 => 'paid',
                'amount_paid'            => 4900,
                'currency'               => 'usd',
                'subscription'           => $tenant->subscription->stripe_subscription_id,
            ],
        ],
    ];

    $signature = generateTestStripeSignature($payload);

    Event::fake([SubscriptionRenewed::class]);

    $this->withHeaders(['Stripe-Signature' => $signature])
         ->postJson('/api/v1/webhooks/stripe', $payload)
         ->assertOk();

    Event::assertDispatched(SubscriptionRenewed::class);
    $this->assertDatabaseHas('invoices', ['stripe_invoice_id' => 'in_test123', 'status' => 'paid']);
});
```

---

## 5. Tenant Isolation Tests

These tests explicitly assert that one tenant **cannot** see another tenant's data.

```php
// tests/Feature/Tenant/TenantIsolationTest.php

it('cannot access another tenants users through the API', function () {
    $tenantA = createTenant(['slug' => 'alpha']);
    $tenantB = createTenant(['slug' => 'bravo']);

    initializeTenancy($tenantA);
    $userA = User::factory()->create(['email' => 'alpha@alpha.com']);

    initializeTenancy($tenantB);
    $userB = User::factory()->create(['email' => 'bravo@bravo.com']);
    $this->actingAs($userB, 'tenant');

    $this->withServerVariables(['HTTP_HOST' => 'bravo.app.com'])
         ->getJson('/api/v1/users')
         ->assertJsonMissing(['email' => 'alpha@alpha.com']);
});

it('resolves a different tenant context per subdomain', function () {
    $tenantA = createTenant(['slug' => 'alpha']);
    $tenantB = createTenant(['slug' => 'bravo']);

    $resolver = app(\App\Support\Tenancy\TenantResolver::class);

    $requestA = Request::create('https://alpha.app.com/');
    $requestB = Request::create('https://bravo.app.com/');

    expect($resolver->fromRequest($requestA)->id)->toBe($tenantA->id);
    expect($resolver->fromRequest($requestB)->id)->toBe($tenantB->id);
});
```

---

## 6. Architecture Tests

Pest's architecture testing keeps structural rules enforced automatically.

```php
// tests/Architecture/ArchTest.php

arch('Central models live in the Central namespace')
    ->expect('App\Central\Models')
    ->toBeClasses()
    ->toExtend('Illuminate\Database\Eloquent\Model');

arch('Tenant models live in the Tenant namespace')
    ->expect('App\Tenant\Models')
    ->toBeClasses()
    ->toExtend('Illuminate\Database\Eloquent\Model');

arch('Controllers do not call env() directly')
    ->expect('App\Central\Controllers')
    ->not->toUse('env');

arch('Service classes are not directly instantiated in controllers')
    ->expect('App\Central\Controllers')
    ->not->toInstantiate('App\Central\Services');

arch('Jobs implement TenantAwareJob when in Tenant namespace')
    ->expect('App\Tenant\Jobs')
    ->toUse('App\Support\Traits\TenantAwareJob');
```

---

## 7. Seeding Strategy

### Central Seeders (run once)

```bash
php artisan db:seed --class=Central\\PlanSeeder
php artisan db:seed --class=Central\\SuperAdminSeeder
```

`PlanSeeder` creates Starter, Pro, and Enterprise plans with realistic feature sets.

### Tenant Seeders (run per tenant at provision time)

```bash
php artisan tenants:seed --tenant={id}
# Runs: RoleSeeder, PermissionSeeder (default data for new tenant DB)
```

### Development Database Seeder

```bash
php artisan db:seed   # calls DatabaseSeeder
```

`DatabaseSeeder` calls central seeders, then creates 3 demo tenants with realistic data for local development.

```php
// database/seeders/DatabaseSeeder.php

public function run(): void
{
    $this->call([
        PlanSeeder::class,
        SuperAdminSeeder::class,
    ]);

    foreach (['demo-alpha', 'demo-bravo', 'demo-gamma'] as $slug) {
        $tenant = Tenant::factory()->active()->create(['slug' => $slug]);
        Artisan::call('tenants:migrate', ['--tenant' => $tenant->id]);
        Artisan::call('tenants:seed', ['--tenant' => $tenant->id, '--class' => 'DemoDataSeeder']);
    }
}
```

---

## 8. Running Tests

```bash
# Run all tests
php artisan test --compact

# Run with coverage report
php artisan test --compact --coverage --min=80

# Filter by test name
php artisan test --compact --filter=InviteUser

# Run only unit tests
php artisan test --compact tests/Unit

# Run in parallel (faster)
php artisan test --compact --parallel
```

---

*Last updated: 2026-03-01*
