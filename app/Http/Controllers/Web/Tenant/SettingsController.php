<?php

namespace App\Http\Controllers\Web\Tenant;

use App\Central\Models\Tenant;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
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

        $tenant = $user->tenant_id
            ? Tenant::on('central')->find($user->tenant_id)
            : null;

        return Inertia::render('Tenant/Settings/Team', [
            'tenant' => $tenant ? [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'created_at' => $tenant->created_at?->toDateString(),
            ] : null,
            'timezones' => \DateTimeZone::listIdentifiers(),
        ]);
    }

    public function updateProfile(): RedirectResponse
    {
        return back();
    }

    public function updatePassword(): RedirectResponse
    {
        return back();
    }

    public function updateTeam(): RedirectResponse
    {
        return back();
    }

    public function destroyTeam(): RedirectResponse
    {
        return redirect('/');
    }
}
