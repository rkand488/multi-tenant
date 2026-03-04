<?php

namespace App\Notifications;

use App\Central\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the tenant owner once their workspace is fully provisioned and ready.
 */
class TenantWelcomeNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Tenant $tenant,
    ) {}

    /** @return list<string> */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $dashboardUrl = 'https://'.$this->tenant->slug.'.'.config('tenancy.domain').'/dashboard';

        return (new MailMessage)
            ->subject("Your {$this->tenant->name} workspace is ready!")
            ->greeting("Welcome to {$this->tenant->name}!")
            ->line('Your workspace has been created and is ready to use.')
            ->line("Your workspace URL is: **{$this->tenant->slug}.".config('tenancy.domain').'**')
            ->action('Go to Dashboard', $dashboardUrl)
            ->line('Here are a few things to get started:')
            ->line('• Invite your team members from the Users section')
            ->line('• Set up your team settings and preferences')
            ->line('• Choose a subscription plan that fits your needs');
    }
}
