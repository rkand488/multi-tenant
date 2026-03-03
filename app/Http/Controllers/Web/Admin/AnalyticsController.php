<?php

namespace App\Http\Controllers\Web\Admin;

use App\Central\Enums\SubscriptionStatus;
use App\Central\Models\Invoice;
use App\Central\Models\Plan;
use App\Central\Models\Subscription;
use App\Central\Models\Tenant;
use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function dashboard(): Response
    {
        $totalTenants = Tenant::on('central')->count();
        $totalUsers = User::count();
        $mrrCents = (int) Subscription::on('central')
            ->whereIn('status', [SubscriptionStatus::Active->value, SubscriptionStatus::Trialing->value])
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->sum('plans.price_monthly');

        $months = collect(range(5, 0))->map(fn ($i) => now()->startOfMonth()->subMonths($i));
        $revenueLabels = $months->map(fn ($m) => $m->format('M Y'))->all();

        $revenueData = $months->map(fn ($m) => (int) Invoice::on('central')
            ->whereYear('period_start', $m->year)
            ->whereMonth('period_start', $m->month)
            ->sum('amount_paid')
        )->all();

        $tenantData = $months->map(fn ($m) => Tenant::on('central')
            ->whereYear('created_at', $m->year)
            ->whereMonth('created_at', $m->month)
            ->count()
        )->all();

        $planDist = Plan::on('central')
            ->withCount(['subscriptions' => fn ($q) => $q->whereIn('status', [SubscriptionStatus::Active->value, SubscriptionStatus::Trialing->value])])
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Admin/Analytics/Dashboard', [
            'kpis' => [
                'total_tenants' => $totalTenants,
                'total_users' => $totalUsers,
                'mrr_cents' => $mrrCents,
                'churn_rate' => 0.0,
                'tenants_growth' => '+'.Tenant::on('central')->whereMonth('created_at', now()->month)->count().' this month',
                'users_growth' => '+'.User::whereMonth('created_at', now()->month)->count().' this month',
                'mrr_growth' => '+0%',
                'churn_change' => '0%',
            ],
            'revenueByMonth' => ['labels' => $revenueLabels, 'data' => $revenueData],
            'tenantsByMonth' => ['labels' => $revenueLabels, 'data' => $tenantData],
            'planDistribution' => [
                'labels' => $planDist->pluck('name')->all(),
                'data' => $planDist->pluck('subscriptions_count')->all(),
            ],
            'churnByMonth' => ['labels' => $revenueLabels, 'data' => array_fill(0, 6, 0.0)],
            'topTenants' => Tenant::on('central')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get()
                ->map(fn (Tenant $t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'storage_mb' => 0,
                    'storage_pct' => 0,
                ])->all(),
        ]);
    }

    public function subscriptions(): Response
    {
        return Inertia::render('Admin/Analytics/SubscriptionMetrics');
    }

    public function userGrowth(): Response
    {
        return Inertia::render('Admin/Analytics/UserGrowth');
    }
}
