<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Sent to a tenant user after registration to verify their email address.
 * Extends Laravel's base VerifyEmail so the signed URL logic is reused.
 */
class VerifyEmailNotification extends BaseVerifyEmail
{
    use Queueable;

    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject('Verify Your Email Address')
            ->greeting('Hello!')
            ->line('Please click the button below to verify your email address.')
            ->action('Verify Email', $url)
            ->line('This verification link will expire in '.config('auth.verification.expire', 60).' minutes.')
            ->line('If you did not create an account, no further action is required.');
    }
}
