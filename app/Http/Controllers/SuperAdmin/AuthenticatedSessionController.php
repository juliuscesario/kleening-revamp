<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('superadmin.auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        \Illuminate\Support\Facades\Log::info('SuperAdmin login attempt:', ['phone_number' => $request->phone_number]);
        
        try {
            $request->authenticate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('SuperAdmin authentication failed for:', ['phone_number' => $request->phone_number, 'errors' => $e->errors()]);
            throw $e;
        }

        $user = Auth::user();
        \Illuminate\Support\Facades\Log::info('SuperAdmin authenticated:', ['user_id' => $user->id, 'role' => $user->role]);

        // Extra check for superadmin role
        if ($user->role !== 'superadmin') {
            \Illuminate\Support\Facades\Log::warning('Non-superadmin tried to access superadmin panel:', ['user_id' => $user->id, 'role' => $user->role]);
            Auth::logout();
            return redirect()->route('superadmin.login')->withErrors(['phone_number' => 'You are not authorized to access this panel.']);
        }

        $request->session()->regenerate();

        // Superadmin doesn't need API token for basic panel
        return redirect()->route('superadmin.tenants.index');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('superadmin.login');
    }
}
