<?php

namespace App\Http\Controllers\Web\Tenant;

use App\Central\Models\Tenant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdatePasswordRequest;
use App\Http\Requests\Settings\UpdateProfileRequest;
use App\Http\Requests\Settings\UpdateTeamRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function apiTokens(): Response
    {
        /** @var User $user */
        $user = auth()->user();

        $tokens = $user->tokens()
            ->orderByDesc('last_used_at')
            ->get()
            ->map(fn ($token) => [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => $token->abilities,
                'last_used_at' => $token->last_used_at?->toISOString(),
                'expires_at' => $token->expires_at?->toISOString(),
                'created_at' => $token->created_at?->toISOString(),
            ]);

        return Inertia::render('Tenant/Profile/ApiTokens', [
            'tokens' => $tokens,
        ]);
    }

    public function index(): RedirectResponse
    {
        return redirect()->route('tenant.settings.profile');
    }

    public function profile(): Response
    {
        /** @var User $user */
        $user = auth()->user();

        return Inertia::render('Tenant/Settings/Profile', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function team(): Response
    {
        /** @var User $user */
        $user = auth()->user();

        $tenant = Tenant::on('central')->find($user->tenant_id);

        $transferableMembers = $user->isTenantOwner()
            ? User::where('id', '!=', $user->id)
                ->orderBy('name')
                ->get(['id', 'name', 'email'])
                ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email])
            : collect();

        return Inertia::render('Tenant/Settings/Team', [
            'tenant' => $tenant ? [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'created_at' => $tenant->created_at?->toDateString(),
            ] : null,
            'timezones' => \DateTimeZone::listIdentifiers(),
            'isOwner' => $user->isTenantOwner(),
            'transferableMembers' => $transferableMembers,
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->update($request->validated());

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->update([
            'password' => $request->string('password')->toString(),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }

    public function updateTeam(UpdateTeamRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $tenant = Tenant::on('central')->findOrFail($user->tenant_id);

        $tenant->update($request->validated());

        return back()->with('success', 'Workspace settings updated.');
    }

    public function destroyTeam(): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $user->isTenantOwner()) {
            abort(403, 'Only the workspace owner can delete the workspace.');
        }

        $tenant = Tenant::on('central')->findOrFail($user->tenant_id);

        // Mark all tenant users as deleted by removing tenant association.
        User::on('central')
            ->where('tenant_id', $tenant->id)
            ->update(['tenant_id' => null]);

        $tenant->delete();

        Auth::logout();

        return redirect('/')->with('status', 'Your workspace has been deleted.');
    }

    public function transferOwnership(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $user->isTenantOwner()) {
            abort(403, 'Only the workspace owner can transfer ownership.');
        }

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:central.users,id'],
        ]);

        $newOwner = User::findOrFail($validated['user_id']);

        if ($newOwner->tenant_id !== $user->tenant_id) {
            abort(403, 'User does not belong to this workspace.');
        }

        $user->update(['role' => \App\Central\Enums\UserRole::TenantUser]);
        $newOwner->update(['role' => \App\Central\Enums\UserRole::TenantOwner]);

        return back()->with('success', "Workspace ownership transferred to {$newOwner->name}.");
    }
}
