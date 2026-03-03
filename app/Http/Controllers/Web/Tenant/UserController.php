<?php

namespace App\Http\Controllers\Web\Tenant;

use App\Central\Models\Tenant;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        /** @var User $user */
        $user = auth()->user();

        $query = $user->tenant_id
            ? User::where('tenant_id', $user->tenant_id)
            : User::whereNull('tenant_id');

        $users = $query->orderBy('name')->paginate(20);

        return Inertia::render('Tenant/Users/Index', [
            'users' => $users->through(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role_id' => $u->role?->value ?? null,
                'role_name' => $u->role?->label() ?? 'Member',
                'status' => $u->email_verified_at ? 'active' : 'invited',
                'joined_at' => $u->created_at?->toFormattedDateString(),
                'last_seen_at' => null,
            ]),
            'roles' => [],
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

        return Inertia::render('Tenant/Users/Create', [
            'roles' => [],
            'defaultRoleId' => null,
            'canInvite' => $maxUsers < 0 || $current < $maxUsers,
            'slotsRemaining' => $maxUsers < 0 ? null : max(0, $maxUsers - $current),
        ]);
    }

    public function store(): RedirectResponse
    {
        return back();
    }

    public function update(string $user): RedirectResponse
    {
        return back();
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
