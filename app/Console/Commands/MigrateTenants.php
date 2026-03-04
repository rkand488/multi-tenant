<?php

namespace App\Console\Commands;

use App\Central\Enums\TenantStatus;
use App\Central\Models\Tenant;
use App\Tenancy\DatabaseManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Iterate over active tenants and run migrations on each tenant database.
 *
 * Usage:
 *   php artisan tenants:migrate
 *   php artisan tenants:migrate --tenant=acme-corp
 *   php artisan tenants:migrate --fresh
 *   php artisan tenants:migrate --seed
 */
class MigrateTenants extends Command
{
    /** @var string */
    protected $signature = 'tenants:migrate
                            {--tenant= : Slug of a single tenant to migrate}
                            {--fresh   : Drop all tables and re-run all migrations}
                            {--seed    : Run seeders after migrations}
                            {--force   : Force the operation to run in production}';

    /** @var string */
    protected $description = 'Run database migrations for all (or one) tenant database(s)';

    public function __construct(private readonly DatabaseManager $databaseManager)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $query = Tenant::on('central')
            ->whereIn('status', [TenantStatus::Active, TenantStatus::Suspended]);

        if ($slug = $this->option('tenant')) {
            $query->where('slug', $slug);
        }

        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->warn('No tenants found matching the given criteria.');

            return self::SUCCESS;
        }

        $command = $this->option('fresh') ? 'migrate:fresh' : 'migrate';

        $options = ['--no-interaction' => true];

        if ($this->option('force') || $this->option('fresh')) {
            $options['--force'] = true;
        }

        if ($this->option('seed')) {
            $options['--seed'] = true;
        }

        $passed = 0;
        $failed = 0;

        foreach ($tenants as $tenant) {
            $dbName = $tenant->databaseName();

            $this->info("Migrating tenant: <comment>{$tenant->name}</comment> → <comment>{$dbName}</comment>");

            try {
                $this->ensureDatabaseExists($dbName);
                $this->databaseManager->connectTenant($tenant);

                Artisan::call($command, array_merge($options, [
                    '--database' => config('tenancy.tenant_connection', 'tenant'),
                    '--path' => 'database/migrations/tenant',
                ]), $this->output);

                $passed++;
            } catch (Throwable $e) {
                $this->error("  Failed: {$e->getMessage()}");
                $failed++;
            } finally {
                $this->databaseManager->connectCentral();
            }
        }

        $this->newLine();
        $this->info("Done. Passed: {$passed} | Failed: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Create the tenant database if it does not already exist.
     */
    private function ensureDatabaseExists(string $dbName): void
    {
        DB::connection('central')
            ->statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }
}
