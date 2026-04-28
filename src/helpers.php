<?php

declare(strict_types=1);

use Jegex\LaravelSeo\Services\SeoService;

if (! function_exists('seo')) {
    /**
     * Get the SEO service instance or set SEO data.
     *
     * @param string|null $key
     * @param mixed $value
     * @return SeoService
     */
    function seo(?string $key = null, mixed $value = null): SeoService
    {
        $seo = app(SeoService::class);

        if ($key !== null && $value !== null) {
            match ($key) {
                'title' => $seo->setTitle($value),
                'description' => $seo->setDescription($value),
                'canonical' => $seo->setCanonical($value),
                'og:title' => $seo->setOgTitle($value),
                'og:description' => $seo->setOgDescription($value),
                'og:image' => $seo->setOgImage($value),
                'twitter:title' => $seo->setTwitterTitle($value),
                'twitter:description' => $seo->setTwitterDescription($value),
                'twitter:image' => $seo->setTwitterImage($value),
                default => null,
            };
        }

        return $seo;
    }
}

if (! function_exists('seo_meta')) {
    /**
     * Render SEO meta tags.
     *
     * @return string
     */
    function seo_meta(): string
    {
        return app(SeoService::class)->render();
    }
}

if (! function_exists('seo_title')) {
    /**
     * Set or get SEO title.
     *
     * @param string|null $title
     * @return string|SeoService
     */
    function seo_title(?string $title = null): string|SeoService
    {
        $seo = app(SeoService::class);

        if ($title !== null) {
            return $seo->setTitle($title);
        }

        return $seo->meta()->getTitle() ?? config('app.name');
    }
}

if (! function_exists('seo_description')) {
    /**
     * Set or get SEO description.
     *
     * @param string|null $description
     * @return string|SeoService
     */
    function seo_description(?string $description = null): string|SeoService
    {
        $seo = app(SeoService::class);

        if ($description !== null) {
            return $seo->setDescription($description);
        }

        return $seo->meta()->getDescription() ?? config('seo.site_description', '');
    }
}

if (! function_exists('seo_canonical')) {
    /**
     * Set or get canonical URL.
     *
     * @param string|null $url
     * @return string|SeoService
     */
    function seo_canonical(?string $url = null): string|SeoService
    {
        $seo = app(SeoService::class);

        if ($url !== null) {
            return $seo->setCanonical($url);
        }

        return $seo->meta()->getCanonical() ?? url()->current();
    }
}

if (! function_exists('seo_og')) {
    /**
     * Set Open Graph data.
     *
     * @param string $key
     * @param mixed $value
     * @return SeoService
     */
    function seo_og(string $key, mixed $value): SeoService
    {
        $seo = app(SeoService::class);

        match ($key) {
            'title' => $seo->setOgTitle($value),
            'description' => $seo->setOgDescription($value),
            'image' => $seo->setOgImage($value),
            'type' => $seo->setOgType($value),
            default => null,
        };

        return $seo;
    }
}

if (! function_exists('seo_schema')) {
    /**
     * Get the SchemaService instance or add a schema.
     *
     * @param string|null $type
     * @return \Jegex\LaravelSeo\Services\SchemaService|\Jegex\LaravelSeo\Schemas\BaseSchema
     */
    function seo_schema(?string $type = null): \Jegex\LaravelSeo\Services\SchemaService|\Jegex\LaravelSeo\Schemas\BaseSchema
    {
        $service = app(\Jegex\LaravelSeo\Services\SchemaService::class);

        if ($type) {
            return match ($type) {
                'article' => $service->article(),
                'website' => $service->website(),
                'organization' => $service->organization(),
                'breadcrumbs' => $service->breadcrumbs(),
                default => throw new \InvalidArgumentException("Unknown schema type: {$type}"),
            };
        }

        return $service;
    }
}
if (! function_exists('parse_seo_template')) {
    /**
     * Parse an SEO template with variables.
     *
     * @param string $template
     * @param array<string, mixed> $data
     * @return string
     */
    function parse_seo_template(string $template, array $data = []): string
    {
        return app(\Jegex\LaravelSeo\Services\TemplateParserService::class)->parse($template, $data);
    }
}
