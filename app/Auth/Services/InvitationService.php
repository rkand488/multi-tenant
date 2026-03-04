<?php

namespace App\Auth\Services;

use App\Central\Enums\UserRole;
use App\Central\Models\Tenant;
use App\Models\Invitation;
use App\Models\User;
use App\Notifications\TenantInvitationNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Manages the full invitation workflow:
 *   1. Tenant owner creates an invitation for an email address.
 *   2. The invited person accepts via a signed token and creates their account.
 */
class InvitationService
{
    /**
     * Create a new pending invitation.
     *
     * @param  array{email: string, role?: string}  $data
     *
     * @throws ValidationException
     */
    public function invite(string $tenantId, User $inviter, array $data): Invitation
    {
        $role = UserRole::from($data['role'] ?? UserRole::TenantUser->value);

        // Prevent inviting the same email twice (pending invitation already exists).
        $existing = Invitation::query()
            ->where('tenant_id', $tenantId)
            ->where('email', $data['email'])
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'email' => ['A pending invitation already exists for this email address.'],
            ]);
        }

        // Also prevent inviting someone who is already a member.
        if (User::query()->where('email', $data['email'])->where('tenant_id', $tenantId)->exists()) {
            throw ValidationException::withMessages([
                'email' => ['This user is already a member of your workspace.'],
            ]);
        }

        $invitation = Invitation::create([
            'tenant_id' => $tenantId,
            'email' => $data['email'],
            'role' => $role,
            'token' => Str::random(64),
            'invited_by' => $inviter->id,
            'expires_at' => now()->addDays(7),
        ]);

        // Resolve the workspace name for the notification subject line.
        $workspaceName = Tenant::on('central')->find($tenantId)?->name ?? 'your workspace';

        // Send the invitation email to the invitee.
        Notification::route('mail', $invitation->email)
            ->notify(new TenantInvitationNotification($invitation, $workspaceName));

        return $invitation;
    }

    /**
     * Accept an invitation and create the user account.
     *
     * @param  array{name: string, password: string}  $data
     *
     * @throws ValidationException
     */
    public function accept(string $token, array $data): array
    {
        $invitation = Invitation::query()->where('token', $token)->first();

        if (! $invitation) {
            throw ValidationException::withMessages([
                'token' => ['This invitation link is invalid.'],
            ]);
        }

        if ($invitation->isExpired()) {
            throw ValidationException::withMessages([
                'token' => ['This invitation link has expired.'],
            ]);
        }

        if ($invitation->isAccepted()) {
            throw ValidationException::withMessages([
                'token' => ['This invitation has already been accepted.'],
            ]);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $invitation->email,
            'password' => Hash::make($data['password']),
            'role' => $invitation->role,
            'tenant_id' => $invitation->tenant_id,
            // The invited user clicked through the invitation link, so their
            // email address is already verified — mark it as such immediately.
            'email_verified_at' => now(),
        ]);

        $invitation->update(['accepted_at' => now()]);

        $sanctumToken = $user->createToken('api')->plainTextToken;

        return [
            'user' => $user,
            'token' => $sanctumToken,
        ];
    }

    /**
     * Find a pending invitation by token for display (the accept page).
     *
     * @throws ValidationException
     */
    public function findPending(string $token): Invitation
    {
        $invitation = Invitation::query()->where('token', $token)->first();

        if (! $invitation || ! $invitation->isPending()) {
            throw ValidationException::withMessages([
                'token' => ['This invitation link is invalid or has expired.'],
            ]);
        }

        return $invitation;
    }
}
