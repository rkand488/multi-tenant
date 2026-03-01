<?php

namespace Database\Factories;

use App\Central\Models\TenantFile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TenantFile>
 */
class TenantFileFactory extends Factory
{
    /** @var class-string<TenantFile> */
    protected $model = TenantFile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => (string) Str::uuid(),
            'uploaded_by' => null,
            'disk' => 'local',
            'path' => 'tenants/'.Str::uuid().'/files/'.Str::uuid().'.txt',
            'original_name' => fake()->word().'.txt',
            'mime_type' => 'text/plain',
            'size' => fake()->numberBetween(128, 10240),
            'visibility' => 'private',
            'meta' => null,
        ];
    }
}
