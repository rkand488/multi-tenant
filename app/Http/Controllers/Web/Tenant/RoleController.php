<?php

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Tenancy\TenantQueryExecutor;
use App\Tenant\Support\TenantPermissionCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RoleController extends Controller
{
    public function __construct(
        private readonly TenantQueryExecutor $tenantQueryExecutor,
    ) {}

    public function index(): Response
    {
        $roles = $this->resolveRolesCollection()
            ->map(fn ($role) => [
                'id' => $role->id,
                'name' => $role->name,
                'description' => $role->description,
                'permissions' => $this->normalizePermissions($role->permissions ?? null),
                'users_count' => 0,
            ]);

        $availablePermissions = TenantPermissionCatalog::webAssignable();

        return Inertia::render('Tenant/Roles/Index', [
            'roles' => $roles,
            'availablePermissions' => $availablePermissions,
        ]);
    }

    public function show(string $roleId): Response
    {
        $role = $this->resolveRoleByIdOrFail((int) $roleId);

        $availablePermissions = TenantPermissionCatalog::webAssignable();

        return Inertia::render('Tenant/Roles/Show', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'description' => $role->description,
                'permissions' => $this->normalizePermissions($role->permissions ?? null),
                'is_system' => (bool) ($role->is_system ?? false),
                'tenant_id' => $role->tenant_id,
            ],
            'allPermissions' => $availablePermissions,
            'canEdit' => true,
            'canDelete' => ! ($role->is_system ?? false),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $availablePermissions = TenantPermissionCatalog::webAssignable();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => ['string', Rule::in($availablePermissions)],
        ]);

        $tenantInsertResult = $this->tenantQueryExecutor->runTenant(
            fn ($tenantConnection, $tenant) => $tenantConnection
                ->table('roles')
                ->insert([
                    'tenant_id' => $tenant->id,
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? null,
                    'permissions' => json_encode($validated['permissions'] ?? []),
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
        );

        if ($tenantInsertResult === true) {
            return back()->with('success', 'Role created successfully.');
        }

        Role::create([
            'tenant_id' => $user?->tenant_id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'permissions' => $validated['permissions'] ?? [],
        ]);

        return back()->with('success', 'Role created successfully.');
    }

    public function update(Request $request, string $role): RedirectResponse
    {
        $availablePermissions = TenantPermissionCatalog::webAssignable();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => ['string', Rule::in($availablePermissions)],
        ]);

        $updated = $this->updateRoleById(
            roleId: (int) $role,
            payload: [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'permissions' => $validated['permissions'] ?? [],
            ],
        );

        if (! $updated) {
            throw new NotFoundHttpException('Role not found.');
        }

        return back()->with('success', 'Role updated successfully.');
    }

    public function destroy(string $role): RedirectResponse
    {
        $deleted = $this->deleteRoleById((int) $role);

        if (! $deleted) {
            throw new NotFoundHttpException('Role not found.');
        }

        return back()->with('success', 'Role deleted successfully.');
    }

    private function resolveRolesCollection(): Collection
    {
        $tenantResult = $this->tenantQueryExecutor->runTenant(
            fn ($tenantConnection) => $tenantConnection
                ->table('roles')
                ->orderBy('name')
                ->get()
        );

        if ($tenantResult instanceof Collection) {
            return $tenantResult;
        }

        return Role::orderBy('name')->get();
    }

    private function resolveRoleByIdOrFail(int $roleId): object
    {
        $tenantResult = $this->tenantQueryExecutor->runTenant(
            fn ($tenantConnection) => $tenantConnection
                ->table('roles')
                ->where('id', $roleId)
                ->first()
        );

        if ($tenantResult !== null) {
            return $tenantResult;
        }

        return Role::findOrFail($roleId);
    }

    private function updateRoleById(int $roleId, array $payload): bool
    {
        $tenantResult = $this->tenantQueryExecutor->runTenant(
            fn ($tenantConnection) => $tenantConnection
                ->table('roles')
                ->where('id', $roleId)
                ->update([
                    'name' => $payload['name'],
                    'description' => $payload['description'],
                    'permissions' => json_encode($payload['permissions']),
                    'updated_at' => now(),
                ])
        );

        if (is_int($tenantResult) && $tenantResult > 0) {
            return true;
        }

        $role = Role::find($roleId);

        if ($role === null) {
            return false;
        }

        $role->update([
            'name' => $payload['name'],
            'description' => $payload['description'],
            'permissions' => $payload['permissions'],
        ]);

        return true;
    }

    private function deleteRoleById(int $roleId): bool
    {
        $tenantResult = $this->tenantQueryExecutor->runTenant(
            fn ($tenantConnection) => $tenantConnection
                ->table('roles')
                ->where('id', $roleId)
                ->delete()
        );

        if (is_int($tenantResult) && $tenantResult > 0) {
            return true;
        }

        $role = Role::find($roleId);

        if ($role === null) {
            return false;
        }

        $role->delete();

        return true;
    }

    private function normalizePermissions(mixed $permissions): array
    {
        if (is_array($permissions)) {
            return $permissions;
        }

        if (is_string($permissions)) {
            $decoded = json_decode($permissions, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}
