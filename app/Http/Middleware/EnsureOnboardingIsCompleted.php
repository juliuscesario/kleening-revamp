<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingIsCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Check if user is logged in
        if (!$user) {
            return $next($request);
        }

        // Only enforce onboarding for tenant owners
        if ($user->role !== 'owner') {
            return $next($request);
        }

        // Fetch tenant. Assuming tenancy is handled by another middleware and tenant is available in app('currentTenant') 
        // Or if it's stored in the user model.
        $tenant = $user->tenant;

        if (!$tenant) {
            return $next($request);
        }

        // Check if onboarding is completed
        if (!$tenant->onboarding_completed_at) {
            // Allow onboarding, logout, and superadmin panel to pass through
            if ($request->routeIs('onboarding.*') || $request->is('logout') || $request->is('*/logout') || $request->is('superadminpanel*')) {
                return $next($request);
            }

            return redirect()->route('onboarding.index', ['tenant_slug' => $tenant->slug]);
        }

        return $next($request);
    }
}
