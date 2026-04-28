<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Jegex\LaravelSeo\Models\NotFoundLog;
use Symfony\Component\HttpFoundation\Response;

class NotFoundTrackerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Track 404s if enabled
        if (
            $response->getStatusCode() === 404 &&
            Config::get('seo.404_tracking.enabled', true)
        ) {
            $this->track404($request);
        }

        return $response;
    }

    /**
     * Track a 404 error.
     */
    protected function track404(Request $request): void
    {
        $ip = $request->ip();

        // Check if IP is excluded
        $excludedIps = Config::get('seo.404_tracking.exclude_ips', []);
        if (in_array($ip, $excludedIps)) {
            return;
        }

        $userAgent = $request->userAgent();

        // Check if user agent is excluded
        $excludedAgents = Config::get('seo.404_tracking.exclude_user_agents', []);
        foreach ($excludedAgents as $pattern) {
            if (str_contains(strtolower($userAgent ?? ''), strtolower($pattern))) {
                return;
            }
        }

        // Log the 404
        NotFoundLog::record(
            url: $request->getRequestUri(),
            referer: $request->headers->get('referer'),
            userAgent: $userAgent,
            ipAddress: $ip,
        );
    }
}
