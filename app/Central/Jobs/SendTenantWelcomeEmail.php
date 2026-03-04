<?php

namespace App\Central\Jobs;

use App\Central\Models\Tenant;
use App\Models\User;
use App\Notifications\TenantWelcomeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Sends the welcome email to a tenant owner after their workspace is
 * fully provisioned.
 *
 * Dispatched from ProvisionTenantDatabase once DB creation succeeds.
 * Queue: notifications
 */
class SendTenantWelcomeEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        private readonly Tenant $tenant,
        private readonly string $ownerEmail,
    ) {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        $owner = User::on('central')
            ->where('email', $this->ownerEmail)
            ->where('tenant_id', $this->tenant->id)
            ->first();

        if ($owner === null) {
            return;
        }

        $owner->notify(new TenantWelcomeNotification($this->tenant));
    }
}
