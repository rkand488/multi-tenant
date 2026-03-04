<?php

namespace App\Notifications;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to an invited email address so they can accept the invitation and
 * create their account within the tenant's workspace.
 */
class TenantInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Invitation $invitation,
        private readonly string $workspaceName,
    ) {}

    /** @return list<string> */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $acceptUrl = url('/invitations/'.$this->invitation->token.'/accept');

        return (new MailMessage)
            ->subject("You've been invited to {$this->workspaceName}")
            ->greeting('Hello!')
            ->line("You have been invited to join **{$this->workspaceName}** as a **{$this->invitation->role->label()}**.")
            ->action('Accept Invitation', $acceptUrl)
            ->line("This invitation expires on {$this->invitation->expires_at->toFormattedDayDateString()}.")
            ->line('If you did not expect this invitation, you can safely ignore this email.');
    }
}
