<?php

namespace Database\Seeders;

use App\Central\Enums\BillingInterval;
use App\Central\Enums\InvoiceStatus;
use App\Central\Enums\SubscriptionStatus;
use App\Central\Enums\TenantStatus;
use App\Central\Models\Invoice;
use App\Central\Models\Plan;
use App\Central\Models\Subscription;
use App\Central\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $starter    = Plan::on('central')->where('slug', 'starter')->firstOrFail();
        $pro        = Plan::on('central')->where('slug', 'pro')->firstOrFail();
        $enterprise = Plan::on('central')->where('slug', 'enterprise')->firstOrFail();

        $tenants = [
            [
                'name'        => 'Acme Corp',
                'slug'        => 'acme-corp',
                'status'      => TenantStatus::Active,
                'owner_email' => 'owner@acme-corp.example.com',
                'plan'        => $enterprise,
                'interval'    => BillingInterval::Yearly,
                'months_back' => 8,
            ],
            [
                'name'        => 'Globex LLC',
                'slug'        => 'globex-llc',
                'status'      => TenantStatus::Active,
                'owner_email' => 'owner@globex.example.com',
                'plan'        => $pro,
                'interval'    => BillingInterval::Monthly,
                'months_back' => 5,
            ],
            [
                'name'        => 'Initech Inc.',
                'slug'        => 'initech-inc',
                'status'      => TenantStatus::Active,
                'owner_email' => 'owner@initech.example.com',
                'plan'        => $pro,
                'interval'    => BillingInterval::Monthly,
                'months_back' => 3,
            ],
            [
                'name'        => 'Massive Dynamic',
                'slug'        => 'massive-dynamic',
                'status'      => TenantStatus::Active,
                'owner_email' => 'owner@massive.example.com',
                'plan'        => $starter,
                'interval'    => BillingInterval::Monthly,
                'months_back' => 6,
            ],
            [
                'name'        => 'Soylent Corp',
                'slug'        => 'soylent-corp',
                'status'      => TenantStatus::Suspended,
                'owner_email' => 'owner@soylent.example.com',
                'plan'        => $pro,
                'interval'    => BillingInterval::Monthly,
                'months_back' => 2,
            ],
        ];

        $invoiceNumber = 1;

        foreach ($tenants as $data) {
            $tenant = Tenant::on('central')->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name'        => $data['name'],
                    'slug'        => $data['slug'],
                    'status'      => $data['status'],
                    'owner_email' => $data['owner_email'],
                ],
            );

            // Subscription
            $subStatus = $data['status'] === TenantStatus::Suspended
                ? SubscriptionStatus::Suspended
                : SubscriptionStatus::Active;

            $periodStart = now()->startOfMonth();
            $periodEnd   = now()->startOfMonth()->addMonth();

            $subscription = Subscription::on('central')->updateOrCreate(
                ['tenant_id' => $tenant->id],
                [
                    'plan_id'              => $data['plan']->id,
                    'status'               => $subStatus,
                    'billing_interval'     => $data['interval'],
                    'current_period_start' => $periodStart,
                    'current_period_end'   => $periodEnd,
                ],
            );

            // Past invoices
            $amount = $data['interval'] === BillingInterval::Yearly
                ? $data['plan']->price_yearly
                : $data['plan']->price_monthly;

            for ($i = $data['months_back']; $i >= 1; $i--) {
                $start = now()->startOfMonth()->subMonths($i);
                $end   = $start->copy()->addMonth();

                Invoice::on('central')->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'period_start' => $start,
                    ],
                    [
                        'subscription_id' => $subscription->id,
                        'number'          => 'INV-'.str_pad((string) $invoiceNumber++, 4, '0', STR_PAD_LEFT),
                        'status'          => InvoiceStatus::Paid,
                        'amount_due'      => $amount,
                        'amount_paid'     => $amount,
                        'currency'        => 'usd',
                        'billing_interval'=> $data['interval'],
                        'period_start'    => $start,
                        'period_end'      => $end,
                        'due_date'        => $start->toDateString(),
                        'paid_at'         => $start->copy()->addDays(1),
                    ],
                );
            }

            $this->command->info("Tenant seeded: {$tenant->name}");
        }
    }
}
