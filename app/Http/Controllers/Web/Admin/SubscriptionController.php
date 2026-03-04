<?php

namespace App\Http\Controllers\Web\Admin;

use App\Billing\Services\SubscriptionService;
use App\Central\Enums\BillingInterval;
use App\Central\Enums\SubscriptionStatus;
use App\Central\Models\Subscription;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class SubscriptionController extends Controller
{
    public function index(Request $request): Response
    {
        $search = (string) $request->input('search', '');
        $status = (string) $request->input('status', '');
        $plan = (string) $request->input('plan', '');

        $subscriptions = Subscription::on('central')
            ->with([
                'tenant' => fn ($q) => $q->withTrashed()->with('primaryDomain'),
                'plan',
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('tenant', function ($q) use ($search) {
                    $q->withTrashed()
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('owner_email', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($plan !== '', function ($query) use ($plan) {
                $query->whereHas('plan', fn ($q) => $q->where('slug', $plan)->orWhere('name', 'like', "%{$plan}%"));
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Subscription $sub) => [
                'id' => $sub->id,
                'tenant_id' => $sub->tenant_id,
                'tenant_name' => $sub->tenant?->name,
                'tenant_domain' => $sub->tenant?->primaryDomain?->domain,
                'plan_name' => $sub->plan?->name,
                'billing_interval' => $sub->billing_interval?->value,
                'amount_cents' => $sub->billing_interval === BillingInterval::Yearly
                    ? $sub->plan?->price_yearly
                    : $sub->plan?->price_monthly,
                'status' => $sub->status?->value,
                'trial_ends_at' => $sub->trial_ends_at?->toDateString(),
                'renews_at' => $sub->current_period_end?->toDateString(),
            ]);

        $summary = [
            'active' => Subscription::on('central')->where('status', SubscriptionStatus::Active)->count(),
            'trialing' => Subscription::on('central')->where('status', SubscriptionStatus::Trialing)->count(),
            'past_due' => Subscription::on('central')->where('status', SubscriptionStatus::PastDue)->count(),
            'canceled_30d' => Subscription::on('central')
                ->where('status', SubscriptionStatus::Cancelled)
                ->where('cancelled_at', '>=', now()->subDays(30))
                ->count(),
        ];

        return Inertia::render('Admin/Subscriptions/Index', [
            'subscriptions' => $subscriptions,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'plan' => $plan,
            ],
            'summary' => $summary,
        ]);
    }

    public function cancel(string $subscription, SubscriptionService $subscriptionService): RedirectResponse
    {
        $sub = Subscription::on('central')->findOrFail($subscription);

        if (! in_array($sub->status, [SubscriptionStatus::Active, SubscriptionStatus::Trialing], true)) {
            return back()->with('error', 'Only active or trialing subscriptions can be cancelled.');
        }

        try {
            $subscriptionService->cancel($sub, atPeriodEnd: true);
        } catch (Throwable $e) {
            return back()->with('error', 'Failed to cancel subscription: '.$e->getMessage());
        }

        return back()->with('success', 'Subscription cancelled. Access continues until the end of the billing period.');
    }
}
