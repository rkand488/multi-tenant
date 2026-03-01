<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;

/**
 * Extends Laravel's RefreshDatabase to also migrate and restore the central
 * in-memory SQLite connection used for billing and multi-tenant schema.
 *
 * PHP's trait precedence rule means any method defined here (or in the traits
 * we alias) takes priority over methods inherited from the parent TestCase.
 * By defining migrateDatabases() directly in this trait, the Pest-generated
 * test class will use our version instead of RefreshDatabase's version.
 */
trait RefreshEveryDatabase
{
    use RefreshDatabase {
        migrateDatabases as parentMigrateDatabases;
    }

    /**
     * Migrate both the default (sqlite) and central in-memory databases.
     *
     * Called once per process by RefreshDatabase when $migrated is false.
     * Subsequent tests restore table state via PDO caching/restoration.
     */
    protected function migrateDatabases(): void
    {
        // Run the default migrations (users, cache, jobs tables etc.)
        $this->parentMigrateDatabases();

        // Run the central schema migrations (plans, subscriptions, etc.)
        $this->artisan('migrate', [
            '--database' => 'central',
            '--path' => 'database/migrations/central',
        ]);

        // Manually cache the central PDO so RefreshDatabase's
        // updateLocalCacheOfInMemoryDatabases() can pick it up (or we fall
        // back to this cached value if the connection name check differs).
        RefreshDatabaseState::$inMemoryConnections['central'] =
            $this->app->make('db')->connection('central')->getPdo();
    }
}
