<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the base permission catalogue into a freshly provisioned tenant database.
 * Permissions follow the "resource.action" naming convention.
 */
class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $permissions = [
            // Users
            ['group' => 'users', 'name' => 'users.view',   'description' => 'View team members'],
            ['group' => 'users', 'name' => 'users.invite',  'description' => 'Invite new members'],
            ['group' => 'users', 'name' => 'users.update',  'description' => 'Update member details'],
            ['group' => 'users', 'name' => 'users.delete',  'description' => 'Remove members from workspace'],

            // Roles
            ['group' => 'roles', 'name' => 'roles.view',    'description' => 'View roles and permissions'],
            ['group' => 'roles', 'name' => 'roles.create',  'description' => 'Create custom roles'],
            ['group' => 'roles', 'name' => 'roles.update',  'description' => 'Update roles and permissions'],
            ['group' => 'roles', 'name' => 'roles.delete',  'description' => 'Delete custom roles'],
            ['group' => 'roles', 'name' => 'roles.assign',  'description' => 'Assign roles to users'],

            // Files
            ['group' => 'files', 'name' => 'files.view',    'description' => 'View uploaded files'],
            ['group' => 'files', 'name' => 'files.upload',  'description' => 'Upload new files'],
            ['group' => 'files', 'name' => 'files.delete',  'description' => 'Delete files'],

            // Settings
            ['group' => 'settings', 'name' => 'settings.view',   'description' => 'View workspace settings'],
            ['group' => 'settings', 'name' => 'settings.update', 'description' => 'Update workspace settings'],

            // Billing
            ['group' => 'billing', 'name' => 'billing.view',   'description' => 'View billing information'],
            ['group' => 'billing', 'name' => 'billing.manage', 'description' => 'Manage subscriptions and plans'],

            // Activity & Audit
            ['group' => 'logs', 'name' => 'logs.view', 'description' => 'View activity and audit logs'],
        ];

        $connection = config('tenancy.tenant_connection');

        foreach ($permissions as $permission) {
            DB::connection($connection)->table('permissions')->insertOrIgnore([
                'group' => $permission['group'],
                'name' => $permission['name'],
                'description' => $permission['description'],
            ]);
        }

        // Assign all permissions to the owner role
        $ownerRoleId = DB::connection($connection)
            ->table('roles')
            ->where('name', 'owner')
            ->value('id');

        if ($ownerRoleId) {
            $allPermissionIds = DB::connection($connection)
                ->table('permissions')
                ->pluck('id');

            $pivotRows = $allPermissionIds->map(fn ($permId) => [
                'role_id' => $ownerRoleId,
                'permission_id' => $permId,
            ])->all();

            DB::connection($connection)->table('permission_role')->insertOrIgnore($pivotRows);
        }
    }
}
