<?php

namespace App\Auth\Services;

use App\Models\User;
use Illuminate\Http\Request;

class WebLoginDomainGuard
{
    public function resolveRedirectPath(Request $request, User $user): ?string
    {
        $tenant = tenantOrNull();

        if ($tenant === null) {
            if (! $user->isSuperAdmin()) {
                return null;
            }

            return '/admin';
        }

        if ($user->isSuperAdmin()) {
            return null;
        }

        if ($user->tenant_id !== $tenant->id) {
            return null;
        }

        return '/dashboard';
    }
}
