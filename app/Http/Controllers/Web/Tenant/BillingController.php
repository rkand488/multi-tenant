<?php

namespace App\Http\Controllers\Web\Tenant;

use App\Central\Models\Invoice;
use App\Central\Models\Plan;
use App\Central\Models\Tenant;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
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
        $plans = Plan::on('central')->where('is_active', true)->orderBy('sort_order')->get();
        $invoices = $user->tenant_id
            ? Invoice::on('central')->where('tenant_id', $user->tenant_id)->orderByDesc('period_start')->limit(12)->get()
            : collect();

        $planFeatures = $plan?->features ?? [];
        $userCount = $user->tenant_id ? User::where('tenant_id', $user->tenant_id)->count() : 0;

        return Inertia::render('Tenant/Billing/Subscription', [
            'subscription' => $subscription,
            'plan' => $plan,
            'plans' => $plans,
            'invoices' => $invoices,
            'usage' => [
                'users' => ['used' => $userCount, 'limit' => $planFeatures['max_users'] ?? 5],
                'storage' => ['used' => 0, 'limit' => ($planFeatures['storage_gb'] ?? 5) * 1024],
            ],
        ]);
    }

    public function cancel(): RedirectResponse
    {
        return back();
    }

    public function upgrade(): RedirectResponse
    {
        return back();
    }
}
