<?php

namespace App\Http\Controllers\Web\Invitation;

use App\Auth\Services\InvitationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\WebAcceptInvitationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Web (session-based) invitation accept flow.
 *
 * The invited user clicks the link in their email, sees a form for their
 * name and password, then is logged in and redirected to the dashboard.
 */
class AcceptInvitationController extends Controller
{
    public function __construct(
        private readonly InvitationService $invitationService,
    ) {}

    /**
     * GET /invitations/{token}/accept
     */
    public function show(string $token): Response|RedirectResponse
    {
        try {
            $invitation = $this->invitationService->findPending($token);
        } catch (\Illuminate\Validation\ValidationException) {
            return redirect()->route('login')
                ->with('error', 'This invitation link is invalid or has expired.');
        }

        return Inertia::render('Auth/AcceptInvitation', [
            'token' => $token,
            'email' => $invitation->email,
            'role' => $invitation->role->label(),
            'workspace_name' => \App\Central\Models\Tenant::on('central')
                ->find($invitation->tenant_id)
                ?->name,
            'expires_at' => $invitation->expires_at->toFormattedDayDateString(),
        ]);
    }

    /**
     * POST /invitations/{token}/accept
     */
    public function accept(string $token, WebAcceptInvitationRequest $request): RedirectResponse
    {
        $result = $this->invitationService->accept($token, $request->only('name', 'password'));

        Auth::login($result['user']);

        $request->session()->regenerate();

        return redirect()->route('tenant.dashboard')
            ->with('success', 'Welcome! Your account has been created.');
    }
}
