<?php

namespace App\Http\Controllers\Web\Tenant;

use App\Billing\Services\SubscriptionService;
use App\Central\Enums\BillingInterval;
use App\Central\Models\Invoice;
use App\Central\Models\Plan;
use App\Central\Models\Tenant;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
    ) {}

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
        /** @var User $user */
        $user = auth()->user();

        if (! $user->isTenantOwner()) {
            abort(403, 'Only the workspace owner can manage billing.');
        }

        $tenant = Tenant::on('central')->findOrFail($user->tenant_id);
        $subscription = $this->subscriptionService->getActiveSubscription($tenant);

        if (! $subscription) {
            return back()->withErrors(['billing' => 'No active subscription found.']);
        }

        $this->subscriptionService->cancel($subscription, atPeriodEnd: true);

        return back()->with('success', 'Your subscription has been cancelled and will end at the current billing period.');
    }

    public function upgrade(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $user->isTenantOwner()) {
            abort(403, 'Only the workspace owner can manage billing.');
        }

        $validated = $request->validate([
            'plan_id' => ['required', 'exists:central.plans,id'],
            'billing_interval' => ['required', 'in:monthly,yearly'],
        ]);

        $tenant = Tenant::on('central')->findOrFail($user->tenant_id);
        $newPlan = Plan::on('central')->findOrFail($validated['plan_id']);
        $interval = BillingInterval::from($validated['billing_interval']);

        $subscription = $this->subscriptionService->getActiveSubscription($tenant);

        if ($subscription) {
            $this->subscriptionService->changePlan($subscription, $newPlan, $interval);
        } else {
            $this->subscriptionService->subscribe($tenant, $newPlan, ['billing_interval' => $interval->value]);
        }

        return back()->with('success', "Subscription updated to {$newPlan->name}.");
    }
}
