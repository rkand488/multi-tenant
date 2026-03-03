<?php

namespace App\Http\Controllers\Api\Billing;

use App\Billing\Services\SubscriptionService;
use App\Central\Models\Plan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\ChangePlanRequest;
use App\Http\Requests\Billing\SubscribeRequest;
use App\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @tags Subscriptions
 */
class SubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * Get current subscription.
     *
     * Returns the current tenant's active subscription details.
     *
     * @response array{data: object|null, message?: string}
     */
    public function show(): JsonResponse
    {
        $tenant = $this->tenantContext->get();
        $subscription = $this->subscriptionService->getActiveSubscription($tenant);

        if (! $subscription) {
            return response()->json(['data' => null, 'message' => 'No active subscription.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $subscription->load('plan')]);
    }

    /**
     * Subscribe to a plan.
     *
     * Creates a new subscription for the tenant to a specified plan.
     *
     * @response array{data: object}
     */
    public function store(SubscribeRequest $request): JsonResponse
    {
        $tenant = $this->tenantContext->get();
        $plan = Plan::findOrFail($request->validated('plan_id'));
        $subscription = $this->subscriptionService->subscribe($tenant, $plan, $request->validated());

        return response()->json(['data' => $subscription->load('plan')], Response::HTTP_CREATED);
    }

    /**
     * Change subscription plan.
     *
     * Upgrades or downgrades the existing subscription to a different plan.
     *
     * @response array{data: object}
     */
    public function update(ChangePlanRequest $request): JsonResponse
    {
        $tenant = $this->tenantContext->get();
        $subscription = $this->subscriptionService->getActiveSubscription($tenant);

        if (! $subscription) {
            return response()->json(['message' => 'No active subscription to change.'], Response::HTTP_NOT_FOUND);
        }

        $plan = Plan::findOrFail($request->validated('plan_id'));
        $interval = \App\Central\Enums\BillingInterval::from($request->validated('billing_interval'));
        $subscription = $this->subscriptionService->changePlan($subscription, $plan, $interval);

        return response()->json(['data' => $subscription->load('plan')]);
    }

    /**
     * Cancel subscription.
     *
     * Cancels the tenant's active subscription. By default cancels at period end.
     *
     * @response array{data: object, message: string}
     */
    public function destroy(): JsonResponse
    {
        $tenant = $this->tenantContext->get();
        $subscription = $this->subscriptionService->getActiveSubscription($tenant);

        if (! $subscription) {
            return response()->json(['message' => 'No active subscription to cancel.'], Response::HTTP_NOT_FOUND);
        }

        $atPeriodEnd = ! request()->boolean('immediately');
        $subscription = $this->subscriptionService->cancel($subscription, $atPeriodEnd);

        return response()->json(['data' => $subscription, 'message' => 'Subscription cancelled.']);
    }

    /**
     * Resume cancelled subscription.
     *
     * Resumes a cancelled subscription that is still within its grace period.
     *
     * @response array{data: object, message: string}
     */
    public function resume(): JsonResponse
    {
        $tenant = $this->tenantContext->get();
        $subscription = $tenant->subscriptions()
            ->where('status', \App\Central\Enums\SubscriptionStatus::Cancelled)
            ->latest()
            ->first();

        if (! $subscription) {
            return response()->json(['message' => 'No cancelled subscription found.'], Response::HTTP_NOT_FOUND);
        }

        $subscription = $this->subscriptionService->resume($subscription);

        return response()->json(['data' => $subscription->load('plan'), 'message' => 'Subscription resumed.']);
    }
}
