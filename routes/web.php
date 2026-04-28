<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Laravel SEO Routes
|--------------------------------------------------------------------------
|
| These routes are automatically registered by the LaravelSeoServiceProvider.
| You can customize them in the config/seo.php file.
|
*/

// Sitemap route
if (Config::get('seo.sitemap.enabled', true)) {
    Route::get(Config::get('seo.sitemap.path', 'sitemap.xml'), function () {
        // Placeholder - full implementation will come later
        $content = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;
        $content .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;
        $content .= '  <!-- Sitemap generation will be implemented in a future update -->'.PHP_EOL;
        $content .= '</urlset>';

        return Response::make($content, 200, [
            'Content-Type' => 'application/xml',
        ]);
    })->name('seo.sitemap');
}

// robots.txt route
if (Config::get('seo.robots.enabled', true)) {
    Route::get('robots.txt', function () {
        $lines = [];
        $lines[] = 'User-agent: '.Config::get('seo.robots.user_agent', '*');

        // Disallow paths
        foreach (Config::get('seo.robots.disallow', []) as $path) {
            $lines[] = 'Disallow: '.$path;
        }

        // Allow paths
        foreach (Config::get('seo.robots.allow', []) as $path) {
            $lines[] = 'Allow: '.$path;
        }

        // Crawl delay
        $crawlDelay = Config::get('seo.robots.crawl_delay');
        if ($crawlDelay !== null) {
            $lines[] = 'Crawl-delay: '.$crawlDelay;
        }

        // Host
        $host = Config::get('seo.robots.host');
        if ($host) {
            $lines[] = '';
            $lines[] = 'Host: '.$host;
        }

        // Sitemap reference
        if (Config::get('seo.sitemap.enabled', true)) {
            $lines[] = '';
            $lines[] = 'Sitemap: '.url(Config::get('seo.sitemap.path', 'sitemap.xml'));
        }

        // Custom rules
        foreach (Config::get('seo.robots.custom_rules', []) as $rule) {
            $lines[] = $rule;
        }

        $content = implode(PHP_EOL, $lines);

        return Response::make($content, 200, [
            'Content-Type' => 'text/plain',
        ]);
    })->name('seo.robots');
}
