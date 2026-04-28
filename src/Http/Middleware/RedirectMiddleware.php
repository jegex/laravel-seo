<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;
use Jegex\LaravelSeo\Models\Redirect;

class RedirectMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Config::get('seo.redirects.enabled', true)) {
            return $next($request);
        }

        $url = $request->getRequestUri();

        // Find matching redirect
        $redirect = $this->findRedirect($url);

        if ($redirect) {
            $redirect->recordHit();

            // Handle 410 Gone
            if ($redirect->getStatusCode() === 410) {
                abort(410);
            }

            // Perform redirect
            $destination = $redirect->getDestination($url);

            return redirect($destination, $redirect->getStatusCode());
        }

        return $next($request);
    }

    /**
     * Find a redirect that matches the given URL.
     */
    protected function findRedirect(string $url): ?Redirect
    {
        // First check for exact match
        $exactMatch = Redirect::query()
            ->active()
            ->where('is_regex', false)
            ->where('from_url', $url)
            ->first();

        if ($exactMatch) {
            return $exactMatch;
        }

        // Then check for regex patterns
        $regexRedirects = Redirect::query()
            ->active()
            ->where('is_regex', true)
            ->get();

        foreach ($regexRedirects as $redirect) {
            if ($redirect->matches($url)) {
                return $redirect;
            }
        }

        return null;
    }
}
