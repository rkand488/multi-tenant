<?php

namespace App\Admin\Services;

use App\Central\Enums\InvoiceStatus;
use App\Central\Enums\SubscriptionStatus;
use App\Central\Enums\TenantStatus;
use App\Central\Models\Invoice;
use App\Central\Models\Plan;
use App\Central\Models\Subscription;
use App\Central\Models\Tenant;

class AdminAnalyticsService
{
    /**
     * @return array<string, mixed>
     */
    public function overview(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        return [
            'tenants' => [
                'total' => Tenant::query()->count(),
                'active' => Tenant::query()->where('status', TenantStatus::Active)->count(),
                'suspended' => Tenant::query()->where('status', TenantStatus::Suspended)->count(),
                'cancelled' => Tenant::query()->where('status', TenantStatus::Cancelled)->count(),
                'new_period' => Tenant::query()->where('created_at', '>=', $startDate)->count(),
            ],
            'plans' => [
                'total' => Plan::query()->count(),
                'active' => Plan::query()->where('is_active', true)->count(),
            ],
            'subscriptions' => [
                'total' => Subscription::query()->count(),
                'active' => Subscription::query()->where('status', SubscriptionStatus::Active)->count(),
                'trialing' => Subscription::query()->where('status', SubscriptionStatus::Trialing)->count(),
                'past_due' => Subscription::query()->where('status', SubscriptionStatus::PastDue)->count(),
                'cancelled' => Subscription::query()->where('status', SubscriptionStatus::Cancelled)->count(),
                'suspended' => Subscription::query()->where('status', SubscriptionStatus::Suspended)->count(),
                'new_period' => Subscription::query()->where('created_at', '>=', $startDate)->count(),
            ],
            'invoices' => [
                'total' => Invoice::query()->count(),
                'open' => Invoice::query()->where('status', InvoiceStatus::Open)->count(),
                'paid_total_cents' => (int) Invoice::query()->where('status', InvoiceStatus::Paid)->sum('amount_paid'),
                'paid_period_cents' => (int) Invoice::query()->where('status', InvoiceStatus::Paid)->where('paid_at', '>=', $startDate)->sum('amount_paid'),
                'issued_period_count' => Invoice::query()->where('created_at', '>=', $startDate)->count(),
                'paid_period_count' => Invoice::query()->where('status', InvoiceStatus::Paid)->where('paid_at', '>=', $startDate)->count(),
                'overdue_open_count' => Invoice::query()->where('status', InvoiceStatus::Open)->whereDate('due_date', '<', now())->count(),
            ],
            'period_days' => $days,
        ];
    }
}
