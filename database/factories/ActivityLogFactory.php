<?php

namespace Database\Factories;

use App\Central\Models\ActivityLog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    /** @var class-string<ActivityLog> */
    protected $model = ActivityLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => (string) Str::uuid(),
            'user_id' => null,
            'action' => fake()->randomElement([
                'tenant.users.created',
                'tenant.users.updated',
                'tenant.users.deleted',
                'tenant.files.uploaded',
            ]),
            'subject_type' => fake()->randomElement(['user', 'file', 'team_settings']),
            'subject_id' => (string) Str::uuid(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'meta' => ['source' => 'factory'],
        ];
    }
}
