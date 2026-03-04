<?php

namespace App\Notifications;

use App\Central\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the tenant owner (database + mail) when their subscription is
 * cancelled.
 */
class SubscriptionCancelledNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Subscription $subscription,
    ) {}

    /** @return list<string> */
    public function via(mixed $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $endsAt = $this->subscription->ends_at?->toFormattedDayDateString() ?? 'immediately';

        return (new MailMessage)
            ->subject('Your subscription has been cancelled')
            ->greeting('Subscription cancelled')
            ->line("Your subscription to **{$this->subscription->plan->name}** has been cancelled.")
            ->line("Your workspace will remain accessible until **{$endsAt}**.")
            ->action('Resubscribe', url('/dashboard/billing'))
            ->line('We hope to see you again. If this was a mistake, you can resubscribe at any time.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(mixed $notifiable): array
    {
        return [
            'type' => 'subscription_cancelled',
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->plan->name,
            'ends_at' => $this->subscription->ends_at?->toIso8601String(),
            'message' => "Your {$this->subscription->plan->name} subscription has been cancelled.",
        ];
    }
}
