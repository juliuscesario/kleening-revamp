<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// --- ADD THIS LINE ---
use Illuminate\Http\RedirectResponse;
// --------------------

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): \Illuminate\View\View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Buat API Token setelah user login
        $user = $request->user();
        $user->tokens()->delete(); // Hapus token lama
        $token = $user->createToken('spa_token')->plainTextToken; // Buat token baru

        // Kirim token ke session agar bisa diambil di Blade
        $request->session()->put('api_token', $token);

        // Path-based tenancy: Redirect to the tenant-specific dashboard
        if ($user->tenant_id) {
            $tenant = \App\Models\Tenant::find($user->tenant_id);
            if ($tenant) {
                 return redirect()->route('dashboard', ['tenant_slug' => $tenant->slug]);
            }
        }

        return redirect()->intended('/dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $tenantSlug = $request->route('tenant_slug');

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if ($tenantSlug) {
            return redirect()->route('login', ['tenant_slug' => $tenantSlug]);
        }

        return redirect('/');
    }
}