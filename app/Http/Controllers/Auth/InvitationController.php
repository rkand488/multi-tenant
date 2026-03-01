<?php

namespace App\Http\Controllers\Auth;

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
 * Manages tenant user invitations.
 *
 * The invite and destroy endpoints require the `tenant` + `tenant.active`
 * middleware stack and are authorised via InvitationPolicy.
 *
 * The show and accept endpoints are public (token-signed).
 */
class InvitationController extends Controller
{
    public function __construct(
        private readonly InvitationService $invitationService,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * POST /api/v1/invitations
     *
     * Tenant owner sends an invitation to a new user.
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
     * GET /api/v1/invitations/{token}
     *
     * Fetch a pending invitation (for the accept UI page).
     */
    public function show(string $token): JsonResponse
    {
        $invitation = $this->invitationService->findPending($token);

        return response()->json(['invitation' => $invitation->only('email', 'role', 'expires_at')]);
    }

    /**
     * POST /api/v1/invitations/accept
     *
     * Invited user registers an account using their invitation token.
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
     * DELETE /api/v1/invitations/{invitation}
     *
     * Revoke a pending invitation.
     */
    public function destroy(Request $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('delete', $invitation);

        $invitation->delete();

        return response()->json(['message' => 'Invitation revoked.']);
    }
}
