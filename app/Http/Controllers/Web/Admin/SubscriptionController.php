<?php

namespace App\Http\Controllers\Web\Admin;

use App\Central\Enums\SubscriptionStatus;
use App\Central\Models\Subscription;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function index(): Response
    {
        $subscriptions = Subscription::on('central')
            ->with(['tenant', 'plan'])
            ->orderByDesc('created_at')
            ->paginate(15);

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
            'filters' => [],
            'summary' => $summary,
        ]);
    }

    public function cancel(string $subscription): RedirectResponse
    {
        return back();
    }
}
