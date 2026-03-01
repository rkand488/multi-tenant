<?php

use App\Central\Enums\UserRole;
use App\Central\Models\ActivityLog;
use App\Central\Models\Tenant;
use App\Central\Models\TenantFile;
use App\Http\Middleware\EnsureTenantIsActive;
use App\Http\Middleware\IdentifyTenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function setTenantContext(Tenant $tenant): void
{
    app(TenantContext::class)->set($tenant);
}

function actingAsTenantOwner(Tenant $tenant): User
{
    return User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => UserRole::TenantOwner,
    ]);
}

it('manages tenant users with tenant isolation', function (): void {
    $tenant = Tenant::factory()->create();
    setTenantContext($tenant);

    $owner = actingAsTenantOwner($tenant);

    $createResponse = $this->actingAs($owner, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->postJson('/api/v1/tenant/users', [
            'name' => 'Team Member',
            'email' => 'member@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'role' => UserRole::TenantUser->value,
        ])
        ->assertCreated();

    $userId = $createResponse->json('data.id');

    $this->actingAs($owner, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->getJson('/api/v1/tenant/users')
        ->assertSuccessful()
        ->assertJsonFragment(['email' => 'member@example.com']);

    $this->actingAs($owner, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->patchJson("/api/v1/tenant/users/{$userId}", [
            'name' => 'Updated Member',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'Updated Member');

    $this->actingAs($owner, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->deleteJson("/api/v1/tenant/users/{$userId}")
        ->assertSuccessful();

    expect(User::query()->whereKey($userId)->exists())->toBeFalse();
});

it('does not allow reading another tenant user', function (): void {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();
    setTenantContext($tenantA);

    $ownerA = actingAsTenantOwner($tenantA);
    $userB = User::factory()->create([
        'tenant_id' => $tenantB->id,
        'role' => UserRole::TenantUser,
    ]);

    $this->actingAs($ownerA, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->getJson("/api/v1/tenant/users/{$userB->id}")
        ->assertNotFound();
});

it('manages tenant roles and permissions', function (): void {
    $tenant = Tenant::factory()->create();
    setTenantContext($tenant);

    $owner = actingAsTenantOwner($tenant);
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => UserRole::TenantUser,
    ]);

    $this->actingAs($owner, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->getJson('/api/v1/tenant/roles-permissions')
        ->assertSuccessful()
        ->assertJsonPath('data.roles.tenant_owner.label', 'Tenant Owner');

    $this->actingAs($owner, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->putJson("/api/v1/tenant/roles-permissions/{$user->id}", [
            'role' => UserRole::TenantOwner->value,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.role', UserRole::TenantOwner->value);
});

it('updates and reads team settings', function (): void {
    $tenant = Tenant::factory()->create();
    setTenantContext($tenant);
    $owner = actingAsTenantOwner($tenant);

    $payload = [
        'timezone' => 'UTC',
        'work_week' => ['mon', 'tue', 'wed', 'thu', 'fri'],
    ];

    $this->actingAs($owner, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->putJson('/api/v1/tenant/team-settings/primary', [
            'settings' => $payload,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.timezone', 'UTC');

    $this->actingAs($owner, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->getJson('/api/v1/tenant/team-settings')
        ->assertSuccessful()
        ->assertJsonPath('data.timezone', 'UTC');
});

it('manages tenant file storage with tenant isolation', function (): void {
    Storage::fake('local');

    $tenant = Tenant::factory()->create();
    $otherTenant = Tenant::factory()->create();
    setTenantContext($tenant);
    $owner = actingAsTenantOwner($tenant);

    $uploadResponse = $this->actingAs($owner, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->postJson('/api/v1/tenant/files', [
            'file' => UploadedFile::fake()->create('contract.pdf', 120, 'application/pdf'),
            'visibility' => 'private',
        ])
        ->assertCreated();

    $fileId = $uploadResponse->json('data.id');

    $this->actingAs($owner, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->getJson('/api/v1/tenant/files')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');

    $this->actingAs($owner, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->getJson("/api/v1/tenant/files/{$fileId}")
        ->assertSuccessful()
        ->assertJsonPath('data.id', $fileId);

    $otherFile = TenantFile::factory()->create([
        'tenant_id' => $otherTenant->id,
    ]);

    $this->actingAs($owner, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->getJson("/api/v1/tenant/files/{$otherFile->id}")
        ->assertNotFound();
});

it('views tenant activity logs with owner-only access', function (): void {
    $tenant = Tenant::factory()->create();
    setTenantContext($tenant);

    $owner = actingAsTenantOwner($tenant);
    $member = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => UserRole::TenantUser,
    ]);

    ActivityLog::factory()->count(2)->create([
        'tenant_id' => $tenant->id,
        'user_id' => $owner->id,
        'action' => 'tenant.users.updated',
    ]);

    $this->actingAs($owner, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->getJson('/api/v1/tenant/activity-logs?action=tenant.users.updated')
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');

    $this->actingAs($member, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->getJson('/api/v1/tenant/activity-logs')
        ->assertForbidden();
});
