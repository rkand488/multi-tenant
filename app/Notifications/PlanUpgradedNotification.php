<?php

namespace App\Notifications;

use App\Central\Models\Plan;
use App\Central\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the tenant owner (database + mail) when their subscription plan
 * is upgraded or downgraded.
 */
class PlanUpgradedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Subscription $subscription,
        private readonly Plan $previousPlan,
        private readonly Plan $newPlan,
    ) {}

    /** @return list<string> */
    public function via(mixed $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $isUpgrade = $this->newPlan->price_monthly > $this->previousPlan->price_monthly;
        $action = $isUpgrade ? 'upgraded' : 'changed';

        return (new MailMessage)
            ->subject("Plan {$action}: now on {$this->newPlan->name}")
            ->greeting('Plan updated!')
            ->line("Your subscription has been {$action} from **{$this->previousPlan->name}** to **{$this->newPlan->name}**.")
            ->action('View Billing', url('/dashboard/billing'))
            ->line('Contact support if you have any questions about your new plan.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(mixed $notifiable): array
    {
        return [
            'type' => 'plan_upgraded',
            'subscription_id' => $this->subscription->id,
            'previous_plan' => $this->previousPlan->name,
            'new_plan' => $this->newPlan->name,
            'message' => "Your plan changed from {$this->previousPlan->name} to {$this->newPlan->name}.",
        ];
    }
}
