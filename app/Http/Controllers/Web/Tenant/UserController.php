<?php

namespace App\Http\Controllers\Web\Tenant;

use App\Auth\Services\InvitationService;
use App\Central\Models\Tenant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreUserInviteRequest;
use App\Models\Invitation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(
        private readonly InvitationService $invitationService,
    ) {}

    public function index(): Response
    {
        $roles = Role::orderBy('name')->get(['id', 'name', 'description']);

        $users = User::with('customRole')->orderBy('name')->paginate(20);

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

    public function show(string $userId): Response
    {
        /** @var User $authUser */
        $authUser = auth()->user();

        $target = User::findOrFail($userId);

        $roles = Role::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Tenant/Users/Show', [
            'user' => [
                'id' => $target->id,
                'name' => $target->name,
                'email' => $target->email,
                'role' => $target->role?->value ?? null,
                'role_id' => $target->role_id,
                'email_verified_at' => $target->email_verified_at?->toISOString(),
                'created_at' => $target->created_at?->toISOString(),
            ],
            'roles' => $roles,
            'canEdit' => $authUser->id !== $target->id,
            'canDelete' => $authUser->id !== $target->id && ! $target->isTenantOwner(),
        ]);
    }

    public function create(): Response
    {
        /** @var User $user */
        $user = auth()->user();

        $tenant = Tenant::on('central')->with('currentSubscription.plan')->find($user->tenant_id);

        $planFeatures = $tenant?->currentSubscription?->plan?->features ?? [];
        $maxUsers = $planFeatures['max_users'] ?? 5;
        $current = User::count();

        $roles = Role::orderBy('name')->get(['id', 'name', 'description']);

        return Inertia::render('Tenant/Users/Create', [
            'roles' => $roles,
            'defaultRoleId' => $roles->first()?->id,
            'canInvite' => $maxUsers < 0 || $current < $maxUsers,
            'slotsRemaining' => $maxUsers < 0 ? null : max(0, $maxUsers - $current),
        ]);
    }

    public function store(StoreUserInviteRequest $request): RedirectResponse
    {
        /** @var User $authUser */
        $authUser = $request->user();

        $invitation = $this->invitationService->invite(
            tenantId: $authUser->tenant_id,
            inviter: $authUser,
            data: $request->validated(),
        );

        return redirect()->route('tenant.users.index')
            ->with('success', "Invitation sent to {$invitation->email}.");
    }

    public function update(Request $request, string $user): RedirectResponse
    {
        /** @var User $authUser */
        $authUser = auth()->user();

        $tenantUser = User::findOrFail($user);

        $validated = $request->validate([
            'role_id' => ['nullable', 'integer', 'exists:central.roles,id'],
        ]);

        // Ensure the role belongs to this tenant (ScopedByTenant filters by current tenant)
        if ($validated['role_id']) {
            $roleExists = Role::where('id', $validated['role_id'])->exists();

            if (! $roleExists) {
                abort(403, 'Role does not belong to this tenant.');
            }
        }

        $tenantUser->update(['role_id' => $validated['role_id']]);

        return back()->with('success', 'Role updated successfully.');
    }

    public function destroy(string $user): RedirectResponse
    {
        /** @var User $authUser */
        $authUser = auth()->user();

        $tenantUser = User::findOrFail($user);

        if ($tenantUser->id === $authUser->id) {
            return back()->withErrors(['user' => 'You cannot remove yourself.']);
        }

        if ($tenantUser->isTenantOwner()) {
            return back()->withErrors(['user' => 'Cannot remove the workspace owner.']);
        }

        $tenantUser->delete();

        return redirect()->route('tenant.users.index')
            ->with('success', "{$tenantUser->name} has been removed from the workspace.");
    }

    public function resendInvite(string $user): RedirectResponse
    {
        /** @var User $authUser */
        $authUser = auth()->user();

        $tenantUser = User::findOrFail($user);

        // Find the most recent pending invitation for this user's email address.
        $invitation = Invitation::withoutGlobalScope(\App\Tenancy\Scopes\TenantScope::class)
            ->where('tenant_id', $authUser->tenant_id)
            ->where('email', $tenantUser->email)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $invitation) {
            return back()->withErrors(['user' => 'No pending invitation found for this user.']);
        }

        // Extend expiry and resend.
        $invitation->update(['expires_at' => now()->addDays(7)]);

        $invitation->refresh();

        $workspaceName = \App\Central\Models\Tenant::on('central')->find($authUser->tenant_id)?->name ?? 'your workspace';

        \Illuminate\Support\Facades\Notification::route('mail', $invitation->email)
            ->notify(new \App\Notifications\TenantInvitationNotification($invitation, $workspaceName));

        return back()->with('success', 'Invitation resent successfully.');
    }
}
