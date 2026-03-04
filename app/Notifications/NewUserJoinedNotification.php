<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Stored in the database notifications table when an invited user accepts
 * their invitation and joins the workspace.
 *
 * Notifiable: the tenant owner.
 */
class NewUserJoinedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly User $newUser,
        private readonly string $workspaceName,
    ) {}

    /** @return list<string> */
    public function via(mixed $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(mixed $notifiable): array
    {
        return [
            'type' => 'new_user_joined',
            'user_id' => $this->newUser->id,
            'user_name' => $this->newUser->name,
            'user_email' => $this->newUser->email,
            'workspace_name' => $this->workspaceName,
            'message' => "{$this->newUser->name} has joined {$this->workspaceName}.",
        ];
    }
}
