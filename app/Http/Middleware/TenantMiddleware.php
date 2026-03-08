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

        // If it's the exact central domain (no subdomain)
        if ($host === $centralDomain) {
            // Central domain logic (landing page, tenant registration, etc.)
            // For now, we just let it pass, but you can add a session/flag if needed
            return $next($request);
        }

        // Check if it's a subdomain of the central domain
        if (str_ends_with($host, '.' . $centralDomain)) {
            $subdomain = str_replace('.' . $centralDomain, '', $host);
            
            $tenant = \App\Models\Tenant::where('slug', $subdomain)->first();

            if (!$tenant) {
                // If subdomain exists but tenant not found, 404
                abort(404, 'Business not found.');
            }

            app()->instance('currentTenant', $tenant);
            return $next($request);
        }

        // Check for custom domains
        $tenant = \App\Models\Tenant::where('domain', $host)->first();
        
        if ($tenant) {
            app()->instance('currentTenant', $tenant);
            return $next($request);
        }

        // Fallback for local development or other cases
        if (config('app.env') === 'local' && !str_contains($host, '.')) {
             // In local development without subdomains, you might want to auto-select a tenant or show central
             return $next($request);
        }

        abort(404, 'Page not found.');
    }
}
