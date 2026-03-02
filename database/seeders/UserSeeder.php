<?php

namespace Database\Seeders;

use App\Central\Enums\UserRole;
use App\Central\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Super Admin ───────────────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'admin@saas.io'],
            [
                'name'              => 'Super Admin',
                'password'          => Hash::make('password'),
                'role'              => UserRole::SuperAdmin,
                'tenant_id'         => null,
                'email_verified_at' => now(),
            ],
        );

        // ── Per-tenant owners + members ───────────────────────────────────────
        $tenantUsers = [
            'acme-corp' => [
                ['name' => 'Jordan Lee',   'email' => 'jordan@acme-corp.example.com',  'role' => UserRole::TenantOwner],
                ['name' => 'Maria Torres', 'email' => 'maria@acme-corp.example.com',   'role' => UserRole::TenantUser],
                ['name' => 'Sam Patel',    'email' => 'sam@acme-corp.example.com',     'role' => UserRole::TenantUser],
                ['name' => 'Chris Kim',    'email' => 'chris@acme-corp.example.com',   'role' => UserRole::TenantUser],
                ['name' => 'Alex Morgan',  'email' => 'alex@acme-corp.example.com',    'role' => UserRole::TenantUser],
            ],
            'globex-llc' => [
                ['name' => 'Dana White',   'email' => 'dana@globex.example.com',       'role' => UserRole::TenantOwner],
                ['name' => 'Riley Chen',   'email' => 'riley@globex.example.com',      'role' => UserRole::TenantUser],
                ['name' => 'Morgan Blake', 'email' => 'morgan@globex.example.com',     'role' => UserRole::TenantUser],
            ],
            'initech-inc' => [
                ['name' => 'Taylor Swift', 'email' => 'taylor@initech.example.com',    'role' => UserRole::TenantOwner],
                ['name' => 'Jamie Park',   'email' => 'jamie@initech.example.com',     'role' => UserRole::TenantUser],
            ],
            'massive-dynamic' => [
                ['name' => 'Quinn Adams',  'email' => 'quinn@massive.example.com',     'role' => UserRole::TenantOwner],
                ['name' => 'Avery Brown', 'email' => 'avery@massive.example.com',      'role' => UserRole::TenantUser],
                ['name' => 'Phoenix Nash','email' => 'phoenix@massive.example.com',    'role' => UserRole::TenantUser],
            ],
            'soylent-corp' => [
                ['name' => 'Blake Stone', 'email' => 'blake@soylent.example.com',      'role' => UserRole::TenantOwner],
            ],
        ];

        foreach ($tenantUsers as $slug => $members) {
            $tenant = Tenant::on('central')->where('slug', $slug)->first();

            if (! $tenant) {
                $this->command->warn("Tenant not found: {$slug} — skipping users");
                continue;
            }

            foreach ($members as $member) {
                User::updateOrCreate(
                    ['email' => $member['email']],
                    [
                        'name'              => $member['name'],
                        'password'          => Hash::make('password'),
                        'role'              => $member['role'],
                        'tenant_id'         => $tenant->id,
                        'email_verified_at' => now(),
                    ],
                );
            }

            $this->command->info("Users seeded for: {$tenant->name}");
        }

        $this->command->info('Total users: '.User::count());
    }
}
