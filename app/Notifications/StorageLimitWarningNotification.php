<?php

namespace App\Notifications;

use App\Central\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the tenant owner (database + mail) when storage usage exceeds 80%
 * of the plan's storage_gb limit.
 */
class StorageLimitWarningNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Tenant $tenant,
        private readonly float $usedPercent,
        private readonly float $limitGb,
    ) {}

    /** @return list<string> */
    public function via(mixed $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $usedFormatted = number_format($this->usedPercent, 1);

        return (new MailMessage)
            ->subject("Storage Warning: {$usedFormatted}% used in {$this->tenant->name}")
            ->warning()
            ->greeting('Storage alert!')
            ->line("Your workspace **{$this->tenant->name}** has used {$usedFormatted}% of its {$this->limitGb} GB storage limit.")
            ->action('View Storage Usage', url('/dashboard/usage'))
            ->line('Consider upgrading your plan or removing unused files to avoid service interruptions.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(mixed $notifiable): array
    {
        return [
            'type' => 'storage_limit_warning',
            'tenant_id' => $this->tenant->id,
            'used_percent' => $this->usedPercent,
            'limit_gb' => $this->limitGb,
            'message' => "Storage is at {$this->usedPercent}% of your {$this->limitGb} GB limit.",
        ];
    }
}
