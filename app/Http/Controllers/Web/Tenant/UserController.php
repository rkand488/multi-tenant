<?php

namespace App\Http\Controllers\Web\Tenant;

use App\Central\Models\Tenant;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        /** @var User $user */
        $user = auth()->user();

        $roles = $user->tenant_id
            ? Role::where('tenant_id', $user->tenant_id)->orderBy('name')->get(['id', 'name', 'description'])
            : collect();

        $query = $user->tenant_id
            ? User::where('tenant_id', $user->tenant_id)->with('customRole')
            : User::whereNull('tenant_id');

        $users = $query->orderBy('name')->paginate(20);

        return Inertia::render('Tenant/Users/Index', [
            'users' => $users->through(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role_id' => $u->role_id,
                'role_name' => $u->customRole?->name ?? '—',
                'status' => $u->email_verified_at ? 'active' : 'invited',
                'joined_at' => $u->created_at?->toFormattedDateString(),
                'last_seen_at' => null,
            ]),
            'roles' => $roles,
            'filters' => [],
            'canInvite' => true,
        ]);
    }

    public function create(): Response
    {
        /** @var User $user */
        $user = auth()->user();

        $tenant = $user->tenant_id
            ? Tenant::on('central')->with('currentSubscription.plan')->find($user->tenant_id)
            : null;

        $planFeatures = $tenant?->currentSubscription?->plan?->features ?? [];
        $maxUsers = $planFeatures['max_users'] ?? 5;
        $current = $user->tenant_id ? User::where('tenant_id', $user->tenant_id)->count() : 0;

        $roles = $user->tenant_id
            ? Role::where('tenant_id', $user->tenant_id)->orderBy('name')->get(['id', 'name', 'description'])
            : collect();

        return Inertia::render('Tenant/Users/Create', [
            'roles' => $roles,
            'defaultRoleId' => $roles->first()?->id,
            'canInvite' => $maxUsers < 0 || $current < $maxUsers,
            'slotsRemaining' => $maxUsers < 0 ? null : max(0, $maxUsers - $current),
        ]);
    }

    public function store(): RedirectResponse
    {
        return back();
    }

    public function update(Request $request, string $user): RedirectResponse
    {
        /** @var User $authUser */
        $authUser = auth()->user();

        $tenantUser = User::where('tenant_id', $authUser->tenant_id)
            ->where('id', $user)
            ->firstOrFail();

        $validated = $request->validate([
            'role_id' => ['nullable', 'integer', 'exists:central.roles,id'],
        ]);

        // Ensure the role belongs to this tenant
        if ($validated['role_id']) {
            $roleExists = Role::where('id', $validated['role_id'])
                ->where('tenant_id', $authUser->tenant_id)
                ->exists();

            if (! $roleExists) {
                abort(403, 'Role does not belong to this tenant.');
            }
        }

        $tenantUser->update(['role_id' => $validated['role_id']]);

        return back()->with('success', 'Role updated successfully.');
    }

    public function destroy(string $user): RedirectResponse
    {
        return back();
    }

    public function resendInvite(string $user): RedirectResponse
    {
        return back();
    }
}
