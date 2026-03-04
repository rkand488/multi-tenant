<?php

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    public function index(): Response
    {
        /** @var User $user */
        $user = auth()->user();

        $roles = $user->tenant_id
            ? Role::where('tenant_id', $user->tenant_id)
                ->orderBy('name')
                ->get()
                ->map(fn ($role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'description' => $role->description,
                    'permissions' => $role->permissions ?? [],
                    'users_count' => 0,
                ])
            : collect();

        $availablePermissions = [
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            'settings.view',
            'settings.update',
            'billing.view',
            'billing.manage',
            'files.view',
            'files.upload',
            'files.delete',
        ];

        return Inertia::render('Tenant/Roles/Index', [
            'roles' => $roles,
            'availablePermissions' => $availablePermissions,
        ]);
    }

    public function show(Role $role): Response
    {
        /** @var User $user */
        $user = auth()->user();

        if ($role->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $availablePermissions = [
            'users.view', 'users.create', 'users.update', 'users.delete',
            'roles.view', 'roles.create', 'roles.update', 'roles.delete',
            'settings.view', 'settings.update',
            'billing.view', 'billing.manage',
            'files.view', 'files.upload', 'files.delete',
        ];

        return Inertia::render('Tenant/Roles/Show', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'description' => $role->description,
                'permissions' => $role->permissions ?? [],
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
        /** @var User $user */
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        Role::create([
            'tenant_id' => $user->tenant_id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'permissions' => $validated['permissions'] ?? [],
        ]);

        return back()->with('success', 'Role created successfully.');
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        if ($role->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'permissions' => $validated['permissions'] ?? [],
        ]);

        return back()->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        if ($role->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $role->delete();

        return back()->with('success', 'Role deleted successfully.');
    }
}
