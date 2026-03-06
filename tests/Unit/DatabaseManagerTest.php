<?php

use App\Central\Models\Tenant;
use App\Tenancy\DatabaseManager;
use Illuminate\Support\Facades\DB;
use Tests\RefreshEveryDatabase;

uses(RefreshEveryDatabase::class);

beforeEach(function (): void {
    // Remember the original default so we can assert it's restored.
    $this->originalDefault = config('tenancy.central_connection', 'central');
});

afterEach(function (): void {
    // Always restore the central connection after each test.
    app(DatabaseManager::class)->connectCentral();
});

// ---------------------------------------------------------------------------
// connectTenant()
// ---------------------------------------------------------------------------

it('sets the tenant connection as the default after connectTenant', function (): void {
    $tenant = Tenant::factory()->create(['slug' => 'test-co']);

    $manager = app(DatabaseManager::class);
    $manager->connectTenant($tenant);

    $tenantConnection = config('tenancy.tenant_connection', 'tenant');

    expect(DB::getDefaultConnection())->toBe($tenantConnection);
});

it('writes the tenant database name into the connection config', function (): void {
    $tenant = Tenant::factory()->create(['slug' => 'acme-db-test']);

    $manager = app(DatabaseManager::class);
    $manager->connectTenant($tenant);

    $tenantConnection = config('tenancy.tenant_connection', 'tenant');
    $connectionConfig = config("database.connections.{$tenantConnection}");

    expect($connectionConfig)->toBeArray()
        ->and($connectionConfig['database'])->toBe($tenant->databaseName());
});

// ---------------------------------------------------------------------------
// connectCentral()
// ---------------------------------------------------------------------------

it('restores the central connection as default after connectCentral', function (): void {
    $tenant = Tenant::factory()->create();

    $manager = app(DatabaseManager::class);
    $manager->connectTenant($tenant);

    // Sanity: default is now tenant connection.
    expect(DB::getDefaultConnection())->toBe(config('tenancy.tenant_connection', 'tenant'));

    $manager->connectCentral();

    expect(DB::getDefaultConnection())->toBe($this->originalDefault);
});

it('can be called multiple times without error', function (): void {
    $manager = app(DatabaseManager::class);

    $manager->connectCentral();
    $manager->connectCentral();

    expect(DB::getDefaultConnection())->toBe($this->originalDefault);
});
