<?php

namespace App\Http\Controllers\Web\Admin;

use App\Central\Enums\SubscriptionStatus;
use App\Central\Models\Plan;
use App\Central\Models\Subscription;
use App\Central\Models\Tenant;
use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $totalTenants = Tenant::on('central')->count();
        $activeUsers = User::count();
        $mrrCents = (int) Subscription::on('central')
            ->whereIn('status', [SubscriptionStatus::Active->value, SubscriptionStatus::Trialing->value])
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->sum('plans.price_monthly');

        $recentTenants = Tenant::on('central')
            ->with('currentSubscription.plan')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get()
            ->map(fn (Tenant $t) => [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
                'plan' => $t->currentSubscription?->plan?->name ?? 'None',
                'status' => $t->status->value,
                'joined' => $t->created_at?->toFormattedDateString(),
            ]);

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_tenants' => $totalTenants,
                'active_users' => $activeUsers,
                'mrr_cents' => $mrrCents,
                'active_plans' => Plan::on('central')->where('is_active', true)->count(),
                'tenants_change' => '+'.Tenant::on('central')->whereMonth('created_at', now()->month)->count().' this month',
                'users_change' => '+'.User::whereMonth('created_at', now()->month)->count().' this month',
                'mrr_change' => '+0%',
                'plans_change' => '0',
            ],
            'recentTenants' => $recentTenants,
            'recentActivity' => [],
        ]);
    }
}
