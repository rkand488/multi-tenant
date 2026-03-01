<?php

namespace Database\Factories;

use App\Central\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invitation>
 */
class InvitationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Str::uuid()->toString(),
            'email' => fake()->unique()->safeEmail(),
            'role' => UserRole::TenantUser,
            'token' => Str::random(64),
            'invited_by' => null,
            'accepted_at' => null,
            'expires_at' => now()->addDays(7),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subDay()]);
    }

    public function accepted(): static
    {
        return $this->state(fn () => ['accepted_at' => now()->subHour()]);
    }
}
