<?php

namespace Database\Factories;

use App\Central\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    /** @var class-string<Plan> */
    protected $model = Plan::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Starter', 'Growth', 'Pro', 'Enterprise', 'Basic', 'Advanced']);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'price_monthly' => fake()->randomElement([0, 2900, 4900, 9900]),
            'price_yearly' => fake()->randomElement([0, 29000, 49000, 99000]),
            'trial_days' => 14,
            'features' => [
                'max_users' => 5,
                'max_projects' => 10,
                'api_access' => false,
                'storage_gb' => 5,
            ],
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function free(): static
    {
        return $this->state(fn () => [
            'price_monthly' => 0,
            'price_yearly' => 0,
            'trial_days' => 0,
        ]);
    }

    public function withFeature(string $key, mixed $value): static
    {
        return $this->state(fn (array $attrs) => [
            'features' => array_merge($attrs['features'] ?? [], [$key => $value]),
        ]);
    }
}
