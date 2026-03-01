<?php

namespace Database\Factories;

use App\Central\Enums\TenantStatus;
use App\Central\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    /** @var class-string<Tenant> */
    protected $model = Tenant::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->randomNumber(3),
            'status' => TenantStatus::Active,
            'owner_email' => fake()->safeEmail(),
        ];
    }

    public function provisioning(): static
    {
        return $this->state(fn () => ['status' => TenantStatus::Provisioning]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['status' => TenantStatus::Suspended]);
    }
}
