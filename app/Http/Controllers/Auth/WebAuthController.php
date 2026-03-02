<?php

namespace App\Http\Controllers\Auth;

use App\Auth\Services\TenantRegistrationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterTenantRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Handles web (session-based) authentication for login, registration, and logout.
 */
class WebAuthController extends Controller
{
    public function __construct(
        private readonly TenantRegistrationService $registrationService,
    ) {}

    /**
     * GET /login
     */
    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * POST /login
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = (bool) $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()->withErrors([
                'email' => 'The provided credentials are incorrect.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('tenant.dashboard'));
    }

    /**
     * GET /register
     */
    public function showRegister(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * POST /register
     */
    public function register(RegisterTenantRequest $request): RedirectResponse
    {
        $result = $this->registrationService->register([
            'name' => $request->string('workspace_name')->toString(),
            'slug' => $request->string('slug')->toString(),
            'owner_name' => $request->string('owner_name')->toString(),
            'email' => $request->string('email')->toString(),
            'password' => $request->string('password')->toString(),
        ]);

        Auth::login($result['user']);

        $request->session()->regenerate();

        return redirect()->route('tenant.dashboard');
    }

    /**
     * POST /logout
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Inertia::clearHistory();

        return redirect()->route('login');
    }
}
