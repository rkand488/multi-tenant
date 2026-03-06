<?php

namespace Database\Seeders\Central;

use App\Central\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds the default billing plans (Starter, Pro, Enterprise) into the central
 * database. Safe to run multiple times — uses updateOrCreate keyed on slug.
 */
class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Perfect for individuals and small side projects.',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'trial_days' => 0,
                'features' => [
                    'max_users' => 5,
                    'storage_gb' => 5,
                    'api_access' => false,
                    'custom_domain' => false,
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'For growing teams that need more power and integrations.',
                'price_monthly' => 4900,
                'price_yearly' => 49000,
                'trial_days' => 14,
                'features' => [
                    'max_users' => 50,
                    'storage_gb' => 50,
                    'api_access' => true,
                    'custom_domain' => true,
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Dedicated infrastructure, SLA, and white-glove support.',
                'price_monthly' => 24900,
                'price_yearly' => 249000,
                'trial_days' => 14,
                'features' => [
                    'max_users' => -1,
                    'storage_gb' => 500,
                    'api_access' => true,
                    'custom_domain' => true,
                    'sla' => true,
                    'sso' => true,
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::on('central')->updateOrCreate(
                ['slug' => $plan['slug']],
                $plan,
            );
        }

        $this->command?->info('Plans seeded: Starter, Pro, Enterprise');
    }
}
