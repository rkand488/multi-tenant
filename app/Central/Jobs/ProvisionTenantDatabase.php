<?php

namespace App\Central\Jobs;

use App\Central\Enums\TenantStatus;
use App\Central\Enums\UserRole;
use App\Central\Models\Tenant;
use App\Models\User;
use App\Notifications\TenantWelcomeNotification;
use App\Tenancy\DatabaseManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * Provisions a new tenant's isolated database, runs tenant-specific
 * migrations, and sends the owner a welcome notification.
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
        if ($this->shouldProvisionDatabase()) {
            $this->createDatabase($databaseManager);
        }

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
     * Create the isolated tenant MySQL database and run tenant-specific
     * migrations against it.
     *
     * Call this instead of (or after) setting status=Active when the
     * database-per-tenant architecture is fully implemented.
     */
    private function createDatabase(DatabaseManager $databaseManager): void
    {
        $dbName = $this->tenant->databaseName();

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

        $this->seedOwnerUserInTenantDatabase();

        // Restore the central connection.
        $databaseManager->connectCentral();
    }

    private function seedOwnerUserInTenantDatabase(): void
    {
        $owner = User::on('central')
            ->where('email', $this->ownerEmail)
            ->where('tenant_id', $this->tenant->id)
            ->first();

        if ($owner === null) {
            return;
        }

        DB::connection(config('tenancy.tenant_connection', 'tenant'))
            ->table('users')
            ->updateOrInsert(
                ['email' => $owner->email],
                [
                    'name' => $owner->name,
                    'email' => $owner->email,
                    'role' => UserRole::TenantOwner->value,
                    'tenant_id' => $this->tenant->id,
                    'role_id' => $owner->role_id,
                    'email_verified_at' => $owner->email_verified_at,
                    'password' => $owner->password,
                    'remember_token' => $owner->remember_token,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
    }

    private function shouldProvisionDatabase(): bool
    {
        if (config('database.connections.central.driver') !== 'mysql') {
            return false;
        }

        $migrationFiles = glob(database_path('migrations/tenant/*.php'));

        return is_array($migrationFiles) && count($migrationFiles) > 0;
    }
}
