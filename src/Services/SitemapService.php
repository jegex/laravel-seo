<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class SitemapService
{
    /** @var array<int, array<string, mixed>> */
    protected array $urls = [];

    /**
     * Generate XML sitemap content.
     */
    public function generate(): string
    {
        $this->collectUrls();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;

        foreach ($this->urls as $url) {
            $xml .= '  <url>'.PHP_EOL;
            $xml .= '    <loc>'.htmlspecialchars($url['loc']).'</loc>'.PHP_EOL;

            if (isset($url['lastmod'])) {
                $xml .= '    <lastmod>'.$url['lastmod'].'</lastmod>'.PHP_EOL;
            }

            if (isset($url['changefreq'])) {
                $xml .= '    <changefreq>'.$url['changefreq'].'</changefreq>'.PHP_EOL;
            }

            if (isset($url['priority'])) {
                $xml .= '    <priority>'.$url['priority'].'</priority>'.PHP_EOL;
            }

            $xml .= '  </url>'.PHP_EOL;
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Collect all URLs for the sitemap.
     */
    protected function collectUrls(): void
    {
        $this->urls = [];

        // Add home page
        $this->addUrl(
            URL::to('/'),
            null,
            'daily',
            '1.0'
        );

        // Collect from configured models
        $modelConfigs = Config::get('seo.sitemap.models', []);

        foreach ($modelConfigs as $modelClass => $config) {
            $this->collectFromModel($modelClass, $config);
        }
    }

    /**
     * Add a URL to the sitemap.
     */
    public function addUrl(string $url, ?string $lastmod = null, string $changefreq = 'weekly', string $priority = '0.5'): self
    {
        $this->urls[] = [
            'loc' => $url,
            'lastmod' => $lastmod,
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];

        return $this;
    }

    /**
     * Collect URLs from a model.
     *
     * @param class-string $modelClass
     * @param array<string, mixed> $config
     */
    protected function collectFromModel(string $modelClass, array $config): void
    {
        if (! class_exists($modelClass)) {
            return;
        }

        try {
            $query = $modelClass::query();

            // Only get published/active items if the model has those scopes
            if (method_exists($modelClass, 'published')) {
                $query->published();
            }

            if (method_exists($modelClass, 'active')) {
                $query->active();
            }

            $items = $query->get();

            foreach ($items as $item) {
                $url = $this->getItemUrl($item);

                if (! $url) {
                    continue;
                }

                $this->addUrl(
                    $url,
                    $this->getLastModified($item),
                    $config['changefreq'] ?? 'weekly',
                    $config['priority'] ?? '0.5'
                );
            }
        } catch (\Exception $e) {
            // Silently fail - sitemap should not break the site
        }
    }

    /**
     * Get URL for a model instance.
     */
    protected function getItemUrl($item): ?string
    {
        // Try to get URL from the item
        if (method_exists($item, 'getUrl')) {
            return $item->getUrl();
        }

        if (method_exists($item, 'getRouteKey')) {
            return URL::to($item->getRouteKey());
        }

        // Try to construct from slug
        if (isset($item->slug)) {
            return URL::to($item->slug);
        }

        return null;
    }

    /**
     * Get last modified date for a model instance.
     */
    protected function getLastModified($item): ?string
    {
        if (isset($item->updated_at)) {
            return $item->updated_at->toAtomString();
        }

        return null;
    }

    /**
     * Get all collected URLs.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getUrls(): array
    {
        return $this->urls;
    }

    /**
     * Clear all URLs.
     */
    public function clear(): self
    {
        $this->urls = [];

        return $this;
    }
}
