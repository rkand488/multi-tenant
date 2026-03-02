<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Order matters: plans → tenants (with subscriptions & invoices) → users
        $this->call([
            PlanSeeder::class,
            TenantSeeder::class,
            UserSeeder::class,
        ]);
    }
}
