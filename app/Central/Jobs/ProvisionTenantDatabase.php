<?php

namespace App\Central\Jobs;

use App\Central\Enums\TenantStatus;
use App\Central\Models\Tenant;
use App\Models\User;
use App\Notifications\TenantWelcomeNotification;
use App\Tenancy\DatabaseManager;
use Database\Seeders\Tenant\PermissionSeeder;
use Database\Seeders\Tenant\RoleSeeder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * Provisions a new tenant's isolated database, runs tenant-specific
 * migrations, seeds base roles and permissions, and sends the owner a
 * welcome notification.
 *
 * Dispatched from TenantRegistrationService after the Tenant record
 * is created in the central database.
 *
 * Queue:  central (runs on the default worker pool)
 * Retry:  3 attempts with 60-second backoff
 */
class ProvisionTenantDatabase implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        private readonly Tenant $tenant,
        private readonly string $ownerEmail,
    ) {}

    public function handle(DatabaseManager $databaseManager): void
    {
        $this->createDatabase($databaseManager);

        // Mark the tenant as Active now that setup is complete.
        $this->tenant->update(['status' => TenantStatus::Active]);

        // Send the welcome email to the workspace owner.
        $owner = User::on('central')
            ->where('email', $this->ownerEmail)
            ->where('tenant_id', $this->tenant->id)
            ->first();

        if ($owner) {
            $owner->notify(new TenantWelcomeNotification($this->tenant));
        }
    }

    /**
     * Create the isolated tenant MySQL database, run tenant-specific
     * migrations, and seed base roles and permissions.
     */
    private function createDatabase(DatabaseManager $databaseManager): void
    {
        $dbName = $this->tenant->databaseName();

        // Tenant database provisioning is MySQL-only. In SQLite (test) environments
        // the central in-memory database handles all data via tenant_id scoping.
        if (DB::connection('central')->getDriverName() !== 'mysql') {
            return;
        }

        // Create the tenant database.
        DB::connection('central')
            ->statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // Point the tenant connection at the newly created database.
        $databaseManager->connectTenant($this->tenant);

        // Run all migrations under database/migrations/tenant/.
        Artisan::call('migrate', [
            '--database' => config('tenancy.tenant_connection', 'tenant'),
            '--path' => 'database/migrations/tenant',
            '--force' => true,
            '--no-interaction' => true,
        ]);

        // Seed system roles and permissions into the tenant database.
        app(RoleSeeder::class)->run();
        app(PermissionSeeder::class)->run();

        // Restore the central connection.
        $databaseManager->connectCentral();
    }
}
