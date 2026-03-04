<?php

namespace App\Notifications;

use App\Central\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the super admin when a ProvisionTenantDatabase job fails
 * so the issue can be investigated and resolved manually.
 */
class TenantProvisionFailedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Tenant $tenant,
        private readonly string $errorMessage,
    ) {}

    /** @return list<string> */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Tenant Provisioning Failed: {$this->tenant->name}")
            ->error()
            ->greeting('Heads up!')
            ->line("Provisioning for tenant **{$this->tenant->name}** (ID: `{$this->tenant->id}`) has failed.")
            ->line('**Error:** '.$this->errorMessage)
            ->line("Owner email: {$this->tenant->owner_email}")
            ->action('View Tenant in Admin', url('/admin/tenants/'.$this->tenant->id))
            ->line('Please investigate and manually provision or re-run the job.');
    }
}
