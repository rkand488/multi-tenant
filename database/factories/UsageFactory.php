<?php

namespace Database\Factories;

use App\Central\Models\Usage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Usage>
 */
class UsageFactory extends Factory
{
    /** @var class-string<Usage> */
    protected $model = Usage::class;

    public function definition(): array
    {
        $start = now()->startOfMonth();

        return [
            'tenant_id' => Str::uuid()->toString(),
            'subscription_id' => null,
            'feature_key' => fake()->randomElement(['api_calls', 'storage_gb', 'team_members']),
            'quantity' => fake()->numberBetween(0, 100),
            'period_start' => $start,
            'period_end' => $start->copy()->addMonth()->subSecond(),
        ];
    }
}
