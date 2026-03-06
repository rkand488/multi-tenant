<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the four system roles into a freshly provisioned tenant database.
 * System roles cannot be renamed or deleted by tenant users.
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $roles = [
            [
                'name' => 'owner',
                'display_name' => 'Owner',
                'description' => 'Full access to all features and workspace management.',
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Can manage users, roles, and most settings.',
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'member',
                'display_name' => 'Member',
                'description' => 'Standard access to workspace features.',
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'viewer',
                'display_name' => 'Viewer',
                'description' => 'Read-only access to workspace content.',
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($roles as $role) {
            DB::connection(config('tenancy.tenant_connection'))
                ->table('roles')
                ->insertOrIgnore($role);
        }
    }
}
