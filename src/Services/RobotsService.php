<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\Services;

use Illuminate\Support\Facades\Config;

class RobotsService
{
    /**
     * Generate robots.txt content.
     *
     * @return string
     */
    public function generate(): string
    {
        $lines = [];
        $lines[] = 'User-agent: ' . Config::get('seo.robots.user_agent', '*');

        // Disallow paths
        foreach (Config::get('seo.robots.disallow', []) as $path) {
            $lines[] = 'Disallow: ' . $path;
        }

        // Allow paths
        foreach (Config::get('seo.robots.allow', []) as $path) {
            $lines[] = 'Allow: ' . $path;
        }

        // Crawl delay
        $crawlDelay = Config::get('seo.robots.crawl_delay');
        if ($crawlDelay !== null) {
            $lines[] = 'Crawl-delay: ' . $crawlDelay;
        }

        // Host
        $host = Config::get('seo.robots.host');
        if ($host) {
            $lines[] = '';
            $lines[] = 'Host: ' . $host;
        }

        // Sitemap reference
        if (Config::get('seo.sitemap.enabled', true)) {
            $lines[] = '';
            $lines[] = 'Sitemap: ' . url(Config::get('seo.sitemap.path', 'sitemap.xml'));
        }

        // Custom rules
        foreach (Config::get('seo.robots.custom_rules', []) as $rule) {
            $lines[] = $rule;
        }

        return implode(PHP_EOL, $lines);
    }
}
