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
            // If on central domain, allow null tenant
            if ($host === $centralDomain) {
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
