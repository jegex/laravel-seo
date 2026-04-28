<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\Facades;

use Illuminate\Support\Facades\Facade;
use Jegex\LaravelSeo\Services\SeoService;

/**
 * @method static \Jegex\LaravelSeo\Services\SeoService for(?\Jegex\LaravelSeo\Contracts\Seoable $model)
 * @method static \Jegex\LaravelSeo\Services\SeoService setTitle(?string $title, bool $parseTemplate = true)
 * @method static \Jegex\LaravelSeo\Services\SeoService setDescription(?string $description, bool $parseTemplate = true)
 * @method static \Jegex\LaravelSeo\Services\SeoService setCanonical(?string $canonical)
 * @method static \Jegex\LaravelSeo\Services\SeoService setRobots(array $robots)
 * @method static \Jegex\LaravelSeo\Services\SeoService setOgTitle(?string $title, bool $parseTemplate = true)
 * @method static \Jegex\LaravelSeo\Services\SeoService setOgDescription(?string $description, bool $parseTemplate = true)
 * @method static \Jegex\LaravelSeo\Services\SeoService setOgImage(?string $image)
 * @method static \Jegex\LaravelSeo\Services\SeoService setOgType(?string $type)
 * @method static \Jegex\LaravelSeo\Services\SeoService setTwitterTitle(?string $title, bool $parseTemplate = true)
 * @method static \Jegex\LaravelSeo\Services\SeoService setTwitterDescription(?string $description, bool $parseTemplate = true)
 * @method static \Jegex\LaravelSeo\Services\SeoService setTwitterImage(?string $image)
 * @method static \Jegex\LaravelSeo\Services\SeoService setTwitterCard(?string $card)
 * @method static \Jegex\LaravelSeo\Services\SeoService applyTemplate(string $type)
 * @method static string render()
 * @method static \Jegex\LaravelSeo\Services\MetaTagService meta()
 * @method static \Jegex\LaravelSeo\Services\TemplateParserService parser()
 * @method static \Jegex\LaravelSeo\Services\SeoService reset()
 *
 * @see \Jegex\LaravelSeo\Services\SeoService
 */
class LaravelSeo extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SeoService::class;
    }
}
