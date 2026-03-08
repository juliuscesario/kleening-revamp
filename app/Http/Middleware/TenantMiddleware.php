<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $centralDomain = config('app.central_domain', 'pakeberes.id');

        $tenant = null;

        // 1. Check if this is the super admin panel (bypasses tenant identification)
        if ($request->segment(1) === 'superadminpanel') {
            return $next($request);
        }

        // 2. Check if the first path segment is a tenant slug (Path-based tenancy)
        if ($host === $centralDomain || str_ends_with($host, '.' . $centralDomain)) {
            $slug = $request->segment(1);
            if ($slug && $slug !== 'login' && $slug !== 'register' && $slug !== 'up') {
                $tenant = \App\Models\Tenant::where('slug', $slug)->first();
            }
        }

        // 2. Subdomain-based tenancy (if not path-based)
        if (!$tenant && str_ends_with($host, '.' . $centralDomain)) {
            $subdomain = str_replace('.' . $centralDomain, '', $host);
            $tenant = \App\Models\Tenant::where('slug', $subdomain)->first();
        }

        // 3. Custom domain support
        if (!$tenant) {
            $tenant = \App\Models\Tenant::where('domain', $host)->first();
        }

        if ($tenant) {
            app()->instance('currentTenant', $tenant);
            
            // Set global route defaults for the identified tenant
            \Illuminate\Support\Facades\URL::defaults([
                'tenant_slug' => $tenant->slug,
            ]);
        } else {
            // If on central domain, check if user should be redirected to a tenant
            if ($host === $centralDomain) {
                // If user is authenticated and not superadmin, redirect to their tenant
                if (auth()->check()) {
                    $user = auth()->user();
                    if ($user->role !== 'superadmin' && $user->tenant_id) {
                        $userTenant = \App\Models\Tenant::find($user->tenant_id);
                        if ($userTenant) {
                            // Redirect to current path but with tenant slug
                            $currentPath = ltrim($request->path(), '/');
                            if ($currentPath === '') $currentPath = 'dashboard';
                            
                            // Check if currentPath already starts with slug or is the slug itself
                            $isPathCorrect = ($currentPath === $userTenant->slug) || 
                                             str_starts_with($currentPath, $userTenant->slug . '/');
                                             
                            if (!$isPathCorrect) {
                                return redirect('/' . $userTenant->slug . '/' . $currentPath);
                            }
                        }
                    }
                }
                
                // If no tenant was matched, but a slug is present, abort unless it's a global route
                $slug = $request->segment(1);
                $allowedGlobalSegments = [
                    'login', 'register', 'up', 'forgot-password', 'reset-password',
                    'verify-email', 'email', 'confirm-password', 'password', 'logout',
                    'auth', 'superadminpanel', 'api', 'sanctum', 'broadcasting',
                    '_debugbar', 'livewire', 'storage', 'images', 'css', 'js', 'fonts', 'build'
                ];

                if ($slug && !in_array($slug, $allowedGlobalSegments)) {
                    abort(404, "Business page not found. Please check your URL.");
                }

                return $next($request);
            }
            // Fallback for local development
            if (config('app.env') === 'local' && !str_contains($host, '.')) {
                 return $next($request);
            }

            abort(404, 'Page not found.');
        }

        return $next($request);
    }
}
