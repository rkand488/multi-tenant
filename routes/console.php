<?php

use App\Central\Jobs\GenerateMonthlyInvoices;
use App\Central\Jobs\SuspendPastDueTenants;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// -------------------------------------------------------------------------
// Scheduled tasks
// -------------------------------------------------------------------------

// Prune activity logs daily (default: 90 days retention).
Schedule::command('activity-logs:prune')->daily();

// Prune audit logs weekly (default: 365 days retention).
Schedule::command('audit-logs:prune')->weekly();

// Generate monthly invoices for active subscriptions whose billing period has ended.
Schedule::job(new GenerateMonthlyInvoices)->monthly();

// Suspend tenants whose subscription is past_due beyond the grace period.
Schedule::job(new SuspendPastDueTenants)->daily();
