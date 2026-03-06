<?php

namespace App\Console\Commands;

use App\Central\Enums\TenantStatus;
use App\Central\Models\Tenant;
use App\Tenancy\DatabaseManager;
use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Throwable;

/**
 * Run a seeder against a single tenant's database.
 *
 * Usage:
 *   php artisan tenants:seed
 *   php artisan tenants:seed --tenant=acme-corp
 *   php artisan tenants:seed --class=Database\\Seeders\\Tenant\\RoleSeeder
 *   php artisan tenants:seed --tenant=acme-corp --class=Database\\Seeders\\Tenant\\PermissionSeeder
 */
class SeedTenant extends Command
{
    /** @var string */
    protected $signature = 'tenants:seed
                            {--tenant=  : Slug of a single tenant to seed (defaults to all active tenants)}
                            {--class=   : Fully-qualified seeder class to run (defaults to DatabaseSeeder)}';

    /** @var string */
    protected $description = 'Run a database seeder against one or all tenant databases';

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

        /** @var class-string<Seeder> $seederClass */
        $seederClass = $this->option('class') ?? \Database\Seeders\DatabaseSeeder::class;

        if (! class_exists($seederClass)) {
            $this->error("Seeder class [{$seederClass}] does not exist.");

            return self::FAILURE;
        }

        $passed = 0;
        $failed = 0;

        foreach ($tenants as $tenant) {
            $this->info("Seeding tenant: <comment>{$tenant->name}</comment>");

            try {
                $this->databaseManager->connectTenant($tenant);

                $seeder = app($seederClass);
                $seeder->setCommand($this);
                $seeder->run();

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
}
