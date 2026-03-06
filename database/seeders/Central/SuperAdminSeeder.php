<?php

namespace Database\Seeders\Central;

use App\Central\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds the initial super-admin user from environment variables.
 * Safe to run multiple times (uses updateOrCreate).
 */
class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('app.admin_email', 'admin@saas.io');
        $password = config('app.admin_password', 'password');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Admin',
                'password' => Hash::make($password),
                'role' => UserRole::SuperAdmin,
                'tenant_id' => null,
                'email_verified_at' => now(),
            ],
        );

        $this->command?->info("Super admin seeded: {$email}");
    }
}
