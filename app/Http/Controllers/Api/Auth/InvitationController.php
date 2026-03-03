<?php

namespace App\Http\Controllers\Api\Auth;

use App\Auth\Services\InvitationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AcceptInvitationRequest;
use App\Http\Requests\Auth\InviteUserRequest;
use App\Models\Invitation;
use App\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @tags Tenant Management
 */
class InvitationController extends Controller
{
    public function __construct(
        private readonly InvitationService $invitationService,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * Send an invitation.
     *
     * Tenant owner sends an invitation to a new user.
     *
     * @response array{message: string, invitation: object}
     */
    public function store(InviteUserRequest $request): JsonResponse
    {
        $this->authorize('create', Invitation::class);

        $tenant = $this->tenantContext->get();

        $invitation = $this->invitationService->invite(
            tenantId: $tenant->id,
            inviter: $request->user(),
            data: $request->validated(),
        );

        return response()->json([
            'message' => 'Invitation sent successfully.',
            'invitation' => $invitation,
        ], Response::HTTP_CREATED);
    }

    /**
     * Get invitation details.
     *
     * Fetch a pending invitation by token (for the accept UI page).
     *
     * @unauthenticated
     *
     * @response array{invitation: object}
     */
    public function show(string $token): JsonResponse
    {
        $invitation = $this->invitationService->findPending($token);

        return response()->json(['invitation' => $invitation->only('email', 'role', 'expires_at')]);
    }

    /**
     * Accept an invitation.
     *
     * Invited user registers an account using their invitation token.
     *
     * @unauthenticated
     *
     * @response array{message: string, user: object, token: string}
     */
    public function accept(AcceptInvitationRequest $request): JsonResponse
    {
        $result = $this->invitationService->accept(
            token: $request->string('token')->toString(),
            data: $request->only('name', 'password'),
        );

        return response()->json([
            'message' => 'Invitation accepted. Welcome!',
            'user' => $result['user'],
            'token' => $result['token'],
        ], Response::HTTP_CREATED);
    }

    /**
     * Revoke an invitation.
     *
     * Delete a pending invitation.
     *
     * @response array{message: string}
     */
    public function destroy(Request $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('delete', $invitation);

        $invitation->delete();

        return response()->json(['message' => 'Invitation revoked.']);
    }
}
