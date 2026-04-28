<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo;

use Jegex\LaravelSeo\Services\SeoService;

/**
 * Laravel SEO Package
 *
 * Main entry point for the Laravel SEO package.
 * Use the `seo()` helper function or the `LaravelSeo` facade instead.
 *
 * @deprecated Use SeoService or the seo() helper function instead
 */
class LaravelSeo
{
    /**
     * Get the SEO service instance.
     */
    public static function getService(): SeoService
    {
        return app(SeoService::class);
    }
}
