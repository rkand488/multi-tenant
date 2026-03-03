<?php

namespace App\Http\Controllers\Web\Tenant;

use App\Central\Models\Tenant;
use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        /** @var User $user */
        $user = auth()->user();

        $tenant = $user->tenant_id
            ? Tenant::on('central')->with('currentSubscription.plan')->find($user->tenant_id)
            : null;

        $subscription = $tenant?->currentSubscription;
        $plan = $subscription?->plan;
        $planFeatures = $plan?->features ?? [];
        $maxUsers = $planFeatures['max_users'] ?? 5;
        $storageLimit = ($planFeatures['storage_gb'] ?? 5) * 1024;

        $userCount = $user->tenant_id
            ? User::where('tenant_id', $user->tenant_id)->count()
            : 0;

        return Inertia::render('Tenant/Dashboard', [
            'stats' => [
                'user_count' => $userCount,
                'user_limit' => $maxUsers,
                'storage_used_mb' => 0,
                'storage_limit_mb' => $storageLimit,
                'active_sessions' => 0,
                'subscription_status' => $subscription?->status?->value ?? 'none',
            ],
            'plan' => $plan ? ['name' => $plan->name, 'interval' => $subscription->billing_interval->value] : null,
            'recentActivity' => [],
            'invitationsPending' => 0,
        ]);
    }
}
