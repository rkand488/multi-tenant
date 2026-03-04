<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Handles email verification for newly registered tenant owners.
 *
 * Invited users are auto-verified when they accept their invitation
 * (see InvitationService::accept()), so this controller is primarily
 * used by owners who registered directly via the registration form.
 */
class EmailVerificationController extends Controller
{
    /**
     * GET /email/verify
     * Show the "check your email" notice.
     */
    public function notice(Request $request): Response|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('tenant.dashboard'));
        }

        return Inertia::render('Auth/VerifyEmail', [
            'status' => session('status'),
        ]);
    }

    /**
     * GET /email/verify/{id}/{hash}
     * Handle the signed verification link from the email.
     */
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('tenant.dashboard').'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended(route('tenant.dashboard').'?verified=1');
    }

    /**
     * POST /email/verification-notification
     * Resend the verification email.
     */
    public function resend(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('tenant.dashboard'));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
