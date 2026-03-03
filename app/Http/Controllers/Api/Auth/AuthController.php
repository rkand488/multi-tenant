<?php

namespace App\Http\Controllers\Api\Auth;

use App\Auth\Services\AuthenticationService;
use App\Auth\Services\TenantRegistrationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterTenantRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles unauthenticated entry points: registration and login/logout.
 */
class AuthController extends Controller
{
    public function __construct(
        private readonly AuthenticationService $authService,
        private readonly TenantRegistrationService $registrationService,
    ) {}

    /**
     * POST /api/v1/auth/register
     *
     * Register a new tenant workspace and its owner account.
     */
    public function register(RegisterTenantRequest $request): JsonResponse
    {
        $result = $this->registrationService->register([
            'name' => $request->string('workspace_name')->toString(),
            'slug' => $request->string('slug')->toString(),
            'owner_name' => $request->string('owner_name')->toString(),
            'email' => $request->string('email')->toString(),
            'password' => $request->string('password')->toString(),
        ]);

        $token = $result['user']->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'Workspace created successfully.',
            'tenant' => $result['tenant'],
            'user' => $result['user'],
            'token' => $token,
        ], Response::HTTP_CREATED);
    }

    /**
     * POST /api/v1/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->authenticate($request->validated());

        return response()->json([
            'user' => $result['user'],
            'token' => $result['token'],
        ]);
    }

    /**
     * POST /api/v1/auth/logout
     * Revokes the current bearer token.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * POST /api/v1/auth/logout-all
     * Revokes all tokens for the authenticated user.
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $this->authService->logoutAll($request->user());

        return response()->json(['message' => 'Logged out from all devices.']);
    }

    /**
     * GET /api/v1/auth/me
     * Returns the authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()]);
    }
}
