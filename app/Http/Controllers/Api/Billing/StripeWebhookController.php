<?php

namespace App\Http\Controllers\Api\Billing;

use App\Billing\Services\StripeWebhookHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Receives and processes Stripe webhook events.
 *
 * This endpoint must be excluded from CSRF protection (see bootstrap/app.php)
 * and from Sanctum token authentication since Stripe cannot obtain a token.
 *
 * Signature verification uses the raw request body and the STRIPE_WEBHOOK_SECRET
 * environment variable.  Verification is skipped when no secret is configured
 * (e.g. local / testing environments).
 *
 * @tags Stripe Webhooks
 */
class StripeWebhookController extends Controller
{
    public function __construct(
        private readonly StripeWebhookHandler $webhookHandler,
    ) {}

    /**
     * Handle an incoming Stripe webhook.
     */
    public function __invoke(Request $request): Response
    {
        if (! $this->verifySignature($request)) {
            Log::warning('Stripe webhook: invalid signature');

            return response('Invalid signature', 401);
        }

        /** @var array<string, mixed> $payload */
        $payload = $request->json()->all();

        try {
            $this->webhookHandler->handle($payload);
        } catch (\Throwable $e) {
            Log::error('Stripe webhook handler error', [
                'event' => $payload['type'] ?? 'unknown',
                'message' => $e->getMessage(),
            ]);

            return response('Webhook handler error', 500);
        }

        return response('Webhook received', 200);
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    /**
     * Verify the Stripe-Signature header against the raw request body.
     *
     * Stripe signs each webhook with HMAC-SHA256 using the webhook secret.  The
     * header format is: t=<unix_timestamp>,v1=<hex_signature>.
     *
     * When STRIPE_WEBHOOK_SECRET is not configured the signature check is
     * bypassed (useful for local and test environments).
     */
    private function verifySignature(Request $request): bool
    {
        $secret = config('services.stripe.webhook_secret');

        if (! $secret) {
            return true;
        }

        $sigHeader = $request->header('Stripe-Signature', '');
        $rawBody = $request->getContent();

        // Parse timestamp and signatures from the header.
        $parts = [];
        foreach (explode(',', $sigHeader) as $part) {
            [$key, $value] = array_pad(explode('=', $part, 2), 2, '');
            $parts[$key][] = $value;
        }

        $timestamp = $parts['t'][0] ?? null;
        $signatures = $parts['v1'] ?? [];

        if (! $timestamp || empty($signatures)) {
            return false;
        }

        // Compute the expected signature.
        $signedPayload = $timestamp.'.'.$rawBody;
        $expected = hash_hmac('sha256', $signedPayload, $secret);

        // Verify that at least one provided signature matches.
        foreach ($signatures as $signature) {
            if (hash_equals($expected, $signature)) {
                return true;
            }
        }

        return false;
    }
}
