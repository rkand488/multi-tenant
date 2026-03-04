<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Manages personal access tokens (Sanctum) for the authenticated user.
 */
class TokenController extends Controller
{
    /**
     * List all tokens for the authenticated user.
     *
     * @response array{data: array}
     */
    public function index(Request $request): JsonResponse
    {
        $tokens = $request->user()
            ->tokens()
            ->select(['id', 'name', 'abilities', 'last_used_at', 'expires_at', 'created_at'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($token) => [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => $token->abilities,
                'last_used_at' => $token->last_used_at?->toIso8601String(),
                'expires_at' => $token->expires_at?->toIso8601String(),
                'created_at' => $token->created_at->toIso8601String(),
            ]);

        return response()->json(['data' => $tokens]);
    }

    /**
     * Create a new personal access token.
     *
     * @response array{token: string, data: array}
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'abilities' => ['nullable', 'array'],
            'abilities.*' => ['string'],
            'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        $abilities = $validated['abilities'] ?? ['*'];
        $expiresAt = isset($validated['expires_in_days'])
            ? now()->addDays($validated['expires_in_days'])
            : null;

        $token = $request->user()->createToken(
            $validated['name'],
            $abilities,
            $expiresAt,
        );

        return response()->json([
            'token' => $token->plainTextToken,
            'data' => [
                'id' => $token->accessToken->id,
                'name' => $token->accessToken->name,
                'abilities' => $token->accessToken->abilities,
                'expires_at' => $token->accessToken->expires_at?->toIso8601String(),
                'created_at' => $token->accessToken->created_at->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Revoke a specific token.
     *
     * @response array{message: string}
     */
    public function destroy(Request $request, int $tokenId): JsonResponse
    {
        $deleted = $request->user()->tokens()->where('id', $tokenId)->delete();

        if (! $deleted) {
            return response()->json(['message' => 'Token not found.'], 404);
        }

        return response()->json(['message' => 'Token revoked successfully.']);
    }
}
